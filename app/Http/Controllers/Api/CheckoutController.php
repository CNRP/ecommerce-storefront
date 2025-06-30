<?php

// app/Http/Controllers/Api/CheckoutController.php - Updated

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
        protected PaymentService $paymentService
    ) {}

    /**
     * Initialize checkout process with enhanced validation
     */
    public function initialize(Request $request): JsonResponse
    {
        try {
            Log::info('Checkout initialize request', [
                'data' => $request->all(),
                'user' => auth()->user()?->id,
            ]);

            $validated = $request->validate([
                // Customer information
                'customer.first_name' => 'required|string|max:255',
                'customer.last_name' => 'required|string|max:255',
                'customer.email' => 'required|email|max:255',
                'customer.phone' => 'nullable|string|max:20',

                // Account creation (for guests)
                'create_account' => 'boolean',
                'password' => 'nullable|string|min:8|required_if:create_account,true',

                // Billing address
                'billing_address.first_name' => 'required|string|max:255',
                'billing_address.last_name' => 'required|string|max:255',
                'billing_address.company' => 'nullable|string|max:255',
                'billing_address.address_line_1' => 'required|string|max:255',
                'billing_address.address_line_2' => 'nullable|string|max:255',
                'billing_address.city' => 'required|string|max:255',
                'billing_address.state_county' => 'required|string|min:2|max:255', // Ensure minimum length
                'billing_address.postal_code' => 'required|string|max:20',
                'billing_address.country' => 'required|string|size:2',

                // Shipping address handling
                'same_as_billing' => 'boolean',
                'shipping_address.first_name' => 'required_if:same_as_billing,false|string|max:255',
                'shipping_address.last_name' => 'required_if:same_as_billing,false|string|max:255',
                'shipping_address.company' => 'nullable|string|max:255',
                'shipping_address.address_line_1' => 'required_if:same_as_billing,false|string|max:255',
                'shipping_address.address_line_2' => 'nullable|string|max:255',
                'shipping_address.city' => 'required_if:same_as_billing,false|string|max:255',
                'shipping_address.state_county' => 'required_if:same_as_billing,false|string|min:2|max:255',
                'shipping_address.postal_code' => 'required_if:same_as_billing,false|string|max:20',
                'shipping_address.country' => 'required_if:same_as_billing,false|string|size:2',

                // Order notes
                'customer_notes' => 'nullable|string|max:1000',
            ]);

            // Ensure shipping address is properly set
            if ($validated['same_as_billing'] ?? false) {
                $validated['shipping_address'] = $validated['billing_address'];
            }

            Log::info('Validation passed', ['validated' => $validated]);

            $result = $this->checkoutService->initializeCheckout($validated);

            Log::info('Checkout initialized successfully', [
                'order_id' => $result['order']->id,
                'customer_created' => $result['customer_created'],
                'user_created' => $result['user_created'] ?? false,
            ]);

            $response = [
                'success' => true,
                'order_id' => $result['order']->id,
                'client_secret' => $result['client_secret'],
                'customer' => [
                    'id' => $result['customer']->id,
                    'email' => $result['customer']->email,
                    'name' => $result['customer']->full_name,
                ],
            ];

            // Include account creation info if relevant
            if ($result['user_created'] ?? false) {
                $response['account_created'] = true;
                $response['message'] = 'Account created successfully! You are now logged in.';
            } elseif ($result['customer_created'] ?? false) {
                $response['customer_created'] = true;
            }

            return response()->json($response);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Checkout initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Checkout initialization failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete checkout after payment confirmation
     */
    public function complete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_intent_id' => 'required|string',
        ]);

        try {
            $order = Order::findOrFail($validated['order_id']);

            // Verify payment intent status
            $paymentIntent = $this->paymentService->retrievePaymentIntent($validated['payment_intent_id']);

            if ($paymentIntent['status'] !== 'succeeded') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not confirmed',
                ], 400);
            }

            // Use the new confirmPayment method instead of completeCheckout
            $result = $this->checkoutService->confirmPayment($order);

            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $result['order']->id,
                    'order_number' => $result['order']->order_number,
                    'total' => $result['order']->getTotalMoney()->format(),
                    'status' => $result['order']->status,
                ],
                'redirect_url' => route('checkout.confirmation', [
                    'orderNumber' => $result['order']->order_number,
                    'token' => $result['order']->guest_token,
                ]),
            ]);

        } catch (\Exception $e) {
            Log::error('Checkout completion failed', [
                'error' => $e->getMessage(),
                'order_id' => $validated['order_id'] ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Handle checkout errors
     */
    public function error(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'error_message' => 'required|string',
        ]);

        try {
            $order = Order::findOrFail($validated['order_id']);
            $this->checkoutService->handleCheckoutFailure($order, $validated['error_message']);

            return response()->json([
                'success' => true,
                'message' => 'Error recorded',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get checkout summary
     */
    public function summary(): JsonResponse
    {
        try {
            $summary = $this->checkoutService->getCheckoutSummary();

            return response()->json($summary);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
