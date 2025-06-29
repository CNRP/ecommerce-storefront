<?php

// app/Http/Controllers/Frontend/CheckoutController.php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService
    ) {}

    /**
     * Show checkout page
     */
    public function index(): View|RedirectResponse
    {
        $summary = $this->checkoutService->getCheckoutSummary();

        if ($summary['item_count'] === 0) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }

        return view('frontend.checkout.index', compact('summary'));
    }

    /**
     * Show order confirmation page
     */
    public function confirmation(Request $request, string $orderNumber): View|RedirectResponse
    {
        $token = $request->get('token');

        if (! $token) {
            return redirect()->route('home')->with('error', 'Invalid order access');
        }

        $order = Order::with(['items.product', 'items.productVariant', 'customer'])
            ->where('order_number', $orderNumber)
            ->where('guest_token', $token)
            ->first();

        if (! $order) {
            return redirect()->route('home')->with('error', 'Order not found');
        }

        return view('frontend.checkout.confirmation', compact('order'));
    }
}
