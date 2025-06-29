<?php

// app/Http/Controllers/Frontend/OrderController.php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Order\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Show customer orders (for logged-in users)
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('message', 'Please log in to view your orders');
        }

        $user = auth()->user();
        $customer = Customer::where('user_id', $user->id)->first();

        if (! $customer) {
            return redirect()->route('home')->with('error', 'No orders found');
        }

        $filters = $request->only(['status', 'date_from', 'date_to']);
        $orders = $this->orderService->getCustomerOrders($customer, $filters);

        return view('frontend.orders.index', compact('orders', 'filters'));
    }

    /**
     * Show specific order details
     */
    public function show(Request $request, string $orderNumber): View|RedirectResponse
    {
        $token = $request->get('token');
        $order = null;

        if (auth()->check()) {
            // For logged-in users, find by customer
            $user = auth()->user();
            $customer = Customer::where('user_id', $user->id)->first();

            if ($customer) {
                $order = Order::with(['items.product', 'items.productVariant', 'statusHistories'])
                    ->where('order_number', $orderNumber)
                    ->where('customer_id', $customer->id)
                    ->first();
            }
        } elseif ($token) {
            // For guests, use token
            $order = Order::with(['items.product', 'items.productVariant', 'statusHistories'])
                ->where('order_number', $orderNumber)
                ->where('guest_token', $token)
                ->first();
        }

        if (! $order) {
            return redirect()->route('home')->with('error', 'Order not found or access denied');
        }

        return view('frontend.orders.show', compact('order'));
    }

    /**
     * Track order by order number and email (for guests)
     */
    public function track(Request $request): View
    {
        $order = null;

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'order_number' => 'required|string',
                'email' => 'required|email',
            ]);

            $order = Order::with(['items.product', 'items.productVariant', 'statusHistories'])
                ->where('order_number', $validated['order_number'])
                ->whereHas('customer', function ($query) use ($validated) {
                    $query->where('email', $validated['email']);
                })
                ->first();

            if (! $order) {
                return back()->withErrors(['order_number' => 'Order not found with the provided details']);
            }
        }

        return view('frontend.orders.track', compact('order'));
    }

    /**
     * Cancel an order (if allowed)
     */
    public function cancel(Request $request, string $orderNumber): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
            'token' => 'nullable|string', // For guest orders
        ]);

        $order = null;

        if (auth()->check()) {
            $user = auth()->user();
            $customer = Customer::where('user_id', $user->id)->first();

            if ($customer) {
                $order = Order::where('order_number', $orderNumber)
                    ->where('customer_id', $customer->id)
                    ->first();
            }
        } elseif ($validated['token']) {
            $order = Order::where('order_number', $orderNumber)
                ->where('guest_token', $validated['token'])
                ->first();
        }

        if (! $order) {
            return back()->with('error', 'Order not found or access denied');
        }

        if (! $order->canBeCancelled()) {
            return back()->with('error', 'This order cannot be cancelled');
        }

        try {
            $this->orderService->cancelOrder($order, $validated['reason'] ?? 'Cancelled by customer');

            return back()->with('success', 'Order has been cancelled successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel order: '.$e->getMessage());
        }
    }
}
