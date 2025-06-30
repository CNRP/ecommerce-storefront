{{-- resources/views/frontend/checkout/index.blade.php - Updated --}}
@extends('layouts.frontend')

@section('title', 'Checkout - ' . config('app.name'))

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="lg:grid lg:grid-cols-2 lg:gap-12">
            <!-- Checkout Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-300 p-6">
                    <h1 class="text-2xl font-bold text-gray-900 mb-6">Checkout</h1>

                    <form id="checkout-form" x-data="checkoutForm()">
                        <!-- Guest/Login Options (only for non-authenticated users) -->
                        @guest
                            <div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <h2 class="text-lg font-medium text-gray-900 mb-4">Checkout Options</h2>
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input type="radio" x-model="checkoutType" value="guest"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Continue as guest</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" x-model="checkoutType" value="create_account"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Create an account for faster future
                                            checkouts</span>
                                    </label>
                                    <div class="text-center">
                                        <span class="text-sm text-gray-500">Already have an account? </span>
                                        <a href="{{ route('login') }}"
                                            class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                            Sign in
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endguest

                        <!-- Customer Information -->
                        <div class="mb-8">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h2>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                    <input type="text" x-model="customer.first_name" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                    <input type="text" x-model="customer.last_name" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                    <input type="email" x-model="customer.email" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number
                                        (Optional)</label>
                                    <input type="tel" x-model="customer.phone"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <!-- Password field for account creation -->
                            @guest
                                <div x-show="checkoutType === 'create_account'" class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                                    <input type="password" x-model="password" :required="checkoutType === 'create_account'"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    <p class="mt-1 text-sm text-gray-500">Minimum 8 characters</p>
                                </div>
                            @endguest
                        </div>

                        <!-- Billing Address -->
                        <div class="mb-8">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Billing Address</h2>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                    <input type="text" x-model="billing_address.first_name" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                    <input type="text" x-model="billing_address.last_name" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Company (Optional)</label>
                                    <input type="text" x-model="billing_address.company"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                    <input type="text" x-model="billing_address.address_line_1" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Address Line 2
                                        (Optional)</label>
                                    <input type="text" x-model="billing_address.address_line_2"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                                    <input type="text" x-model="billing_address.city" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">County/State</label>
                                    <input type="text" x-model="billing_address.state_county" required
                                        @input="validateStateCounty()"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    <p x-show="stateCountyError" x-text="stateCountyError"
                                        class="mt-1 text-sm text-red-600"></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                                    <input type="text" x-model="billing_address.postal_code" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="mb-8">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-medium text-gray-900">Shipping Address</h2>
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="sameAsBilling" @change="copyBillingToShipping()"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-600">Same as billing address</span>
                                </label>
                            </div>

                            <div x-show="!sameAsBilling" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                    <input type="text" x-model="shipping_address.first_name"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                    <input type="text" x-model="shipping_address.last_name"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Company (Optional)</label>
                                    <input type="text" x-model="shipping_address.company"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                    <input type="text" x-model="shipping_address.address_line_1"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Address Line 2
                                        (Optional)</label>
                                    <input type="text" x-model="shipping_address.address_line_2"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                                    <input type="text" x-model="shipping_address.city"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">County/State</label>
                                    <input type="text" x-model="shipping_address.state_county"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                                    <input type="text" x-model="shipping_address.postal_code"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Country Selection (Single Field) -->
                        <div class="mb-8">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Shipping Country</h2>
                            <div class="max-w-xs">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                                <div class="relative">
                                    <input type="text" value="United Kingdom" readonly
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600 cursor-not-allowed">
                                    <input type="hidden" x-model="country" value="GB">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Currently shipping to UK only</p>
                            </div>
                        </div>

                        <!-- Order Notes -->
                        <div class="mb-8">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Order Notes (Optional)</h2>
                            <textarea x-model="customer_notes" rows="3" placeholder="Any special instructions for your order..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <!-- Payment Section -->
                        <div class="mb-8">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h2>
                            <div id="payment-element" class="p-4 border border-gray-300 rounded-md bg-gray-50">
                                <div class="text-center text-gray-500">
                                    <svg class="mx-auto h-8 w-8 mb-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                        </path>
                                    </svg>
                                    Payment form will appear here after filling in your details
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="button" @click="processPayment()" :disabled="processing"
                            :class="{
                                'bg-gray-400 cursor-not-allowed': processing,
                                'bg-blue-600 hover:bg-blue-700': !processing
                            }"
                            class="w-full py-3 px-4 text-white font-medium rounded-lg focus:ring-2 focus:ring-blue-500 transition-colors">
                            <span x-show="!processing">Complete Order</span>
                            <span x-show="processing" class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1 mt-8 lg:mt-0">
                <div class="bg-white rounded-lg shadow-sm border border-gray-300 p-6 sticky top-24">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h2>

                    <!-- Cart Items -->
                    <div class="space-y-4 mb-6">
                        @foreach ($summary['items'] as $item)
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0 w-12 h-12 bg-gray-200 rounded overflow-hidden">
                                    @if ($item['image'])
                                        <img src="{{ Storage::url($item['image']) }}" alt="{{ $item['name'] }}"
                                            class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $item['name'] }}</h4>
                                    <p class="text-sm text-gray-500">Qty: {{ $item['quantity'] }}</p>
                                </div>
                                <p class="text-sm font-medium text-gray-900">
                                    £{{ number_format($item['price'] * $item['quantity'], 2) }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <!-- Order Totals -->
                    <div class="border-t border-gray-300 pt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span>Subtotal</span>
                            <span>£{{ number_format($summary['subtotal'], 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>VAT (20%)</span>
                            <span>£{{ number_format($summary['tax_amount'], 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>Shipping</span>
                            <span>£{{ number_format($summary['shipping_amount'], 2) }}</span>
                        </div>
                        <div class="border-t border-gray-300 pt-2 flex justify-between font-medium text-lg">
                            <span>Total</span>
                            <span>£{{ number_format($summary['total'], 2) }}</span>
                        </div>
                    </div>

                    <!-- Security Notice -->
                    <div class="mt-6 text-center">
                        <div class="flex items-center justify-center text-sm text-gray-500 mb-2">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                </path>
                            </svg>
                            Secure 256-bit SSL encryption
                        </div>
                        <p class="text-xs text-gray-400">Your payment information is protected</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stripe JavaScript -->
    <script src="https://js.stripe.com/v3/"></script>

    <script>
        function checkoutForm() {
            return {
                // Checkout type for guests
                checkoutType: 'guest',
                password: '',

                // Form data - with proper null safety
                customer: {
                    first_name: '@auth{{ auth()->user()->customer?->first_name ?? '' }}@endauth',
                    last_name: '@auth{{ auth()->user()->customer?->last_name ?? '' }}@endauth',
                    email: '@auth{{ auth()->user()->email ?? '' }}@endauth',
                    phone: '@auth{{ auth()->user()->customer?->phone ?? '' }}@endauth'
                },
                billing_address: {
                    @auth
                    @php
                        $defaultBilling = auth()->user()->customer?->getDefaultBillingAddress();
                    @endphp
                    first_name: '{{ $defaultBilling?->first_name ?? '' }}',
                    last_name: '{{ $defaultBilling?->last_name ?? '' }}',
                    company: '{{ $defaultBilling?->company ?? '' }}',
                    address_line_1: '{{ $defaultBilling?->address_line_1 ?? '' }}',
                    address_line_2: '{{ $defaultBilling?->address_line_2 ?? '' }}',
                    city: '{{ $defaultBilling?->city ?? '' }}',
                    state_county: '{{ $defaultBilling?->state_county ?? '' }}',
                    postal_code: '{{ $defaultBilling?->postal_code ?? '' }}',
                @else
                    first_name: '',
                    last_name: '',
                    company: '',
                    address_line_1: '',
                    address_line_2: '',
                    city: '',
                    state_county: '',
                    postal_code: '',
                @endauth
                country: 'GB'
            },
            shipping_address: {
                    first_name: '',
                    last_name: '',
                    company: '',
                    address_line_1: '',
                    address_line_2: '',
                    city: '',
                    state_county: '',
                    postal_code: '',
                    country: 'GB'
                },
                customer_notes: '',
                sameAsBilling: true,
                processing: false,
                country: 'GB',
                stateCountyError: '',

                // Stripe
                stripe: null,
                elements: null,
                paymentElement: null,
                clientSecret: null,
                orderId: null,
                stripeInitialized: false,

                init() {
                    this.billing_address.country = this.country;
                    this.shipping_address.country = this.country;

                    // Copy billing to shipping initially
                    this.copyBillingToShipping();

                    // Watch for changes in billing address
                    this.$watch('billing_address', () => {
                        if (this.sameAsBilling) {
                            this.copyBillingToShipping();
                        }
                    }, {
                        deep: true
                    });

                    // Watch for form completion to initialize Stripe (with debounce)
                    let timeoutId;
                    const watchCallback = () => {
                        if (!this.stripeInitialized) {
                            clearTimeout(timeoutId);
                            timeoutId = setTimeout(() => {
                                this.checkFormAndInitializeStripe();
                            }, 500);
                        }
                    };

                    this.$watch('customer', watchCallback, {
                        deep: true
                    });
                    this.$watch('billing_address', watchCallback, {
                        deep: true
                    });
                },

                copyBillingToShipping() {
                    if (this.sameAsBilling) {
                        this.shipping_address = {
                            ...this.billing_address
                        };
                    }
                },

                validateStateCounty() {
                    const value = this.billing_address.state_county;
                    if (value && value.length < 2) {
                        this.stateCountyError = 'County/State must be at least 2 characters';
                    } else {
                        this.stateCountyError = '';
                    }
                },

                checkFormAndInitializeStripe() {
                    if (this.stripeInitialized) return;

                    // Enhanced validation including state_county length
                    if (this.customer.first_name &&
                        this.customer.last_name &&
                        this.customer.email &&
                        this.billing_address.first_name &&
                        this.billing_address.address_line_1 &&
                        this.billing_address.city &&
                        this.billing_address.postal_code &&
                        this.billing_address.state_county &&
                        this.billing_address.state_county.length >= 2 &&
                        this.country) {

                        this.initializeStripe();
                    }
                },

                async initializeStripe() {
                        if (this.stripeInitialized) return;
                        this.stripeInitialized = true;

                        try {
                            const requestData = {
                                customer: this.customer,
                                billing_address: this.billing_address,
                                shipping_address: this.sameAsBilling ? this.billing_address : this.shipping_address,
                                same_as_billing: this.sameAsBilling,
                                customer_notes: this.customer_notes,
                                create_account: this.checkoutType === 'create_account',
                                password: this.checkoutType === 'create_account' ? this.password : null
                            };

                            console.log('Sending request data:', requestData);

                            const response = await fetch('{{ route('api.checkout.initialize') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                        'content'),
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(requestData)
                            });

                            const responseText = await response.text();
                            console.log('Raw response:', responseText.substring(0, 500));

                            if (!response.ok) {
                                console.error('HTTP Error:', response.status, response.statusText);

                                if (response.status === 422) {
                                    try {
                                        const errorResult = JSON.parse(responseText);
                                        console.error('Validation errors:', errorResult);
                                        if (errorResult.errors) {
                                            let errorMessage = 'Validation failed:\n';
                                            Object.keys(errorResult.errors).forEach(field => {
                                                errorMessage +=
                                                    `${field}: ${errorResult.errors[field].join(', ')}\n`;
                                            });
                                            throw new Error(errorMessage);
                                        }
                                    } catch (parseError) {
                                        console.error('Could not parse validation error response');
                                    }
                                }
                                throw new Error(`HTTP ${response.status}: Server returned an error`);
                            }

                            let result;
                            try {
                                result = JSON.parse(responseText);
                            } catch (parseError) {
                                console.error('JSON parse error:', parseError);
                                throw new Error('Server returned invalid JSON response');
                            }

                            console.log('Parsed result:', result);

                            if (!result.success) {
                                throw new Error(result.message || 'Checkout initialization failed');
                            }

                            this.clientSecret = result.client_secret;
                            this.orderId = result.order_id;

                            // Show account creation message if applicable
                            if (result.account_created) {
                                showToast(result.message || 'Account created successfully!', 'success');
                            }

                            // Initialize Stripe
                            this.stripe = Stripe('{{ config('services.stripe.key') }}');
                            this.elements = this.stripe.elements({
                                clientSecret: this.clientSecret
                            });

                            // Create payment element
                            this.paymentElement = this.elements.create('payment');
                            this.paymentElement.mount('#payment-element');

                        } catch (error) {
                            console.error('Error initializing checkout:', error);
                            this.stripeInitialized = false; // Reset flag on error
                            showToast('Error initializing checkout: ' + error.message, 'error');
                        }
                    },

                    async processPayment() {
                        if (!this.stripe || !this.paymentElement) {
                            showToast('Payment system not ready. Please try again.', 'error');
                            return;
                        }

                        this.processing = true;

                        try {
                            const {
                                error
                            } = await this.stripe.confirmPayment({
                                elements: this.elements,
                                confirmParams: {
                                    return_url: window.location.origin + '/checkout/complete'
                                },
                                redirect: 'if_required'
                            });

                            if (error) {
                                throw error;
                            }

                            // Payment succeeded
                            const completeResponse = await fetch('{{ route('api.checkout.complete') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .getAttribute('content')
                                },
                                body: JSON.stringify({
                                    order_id: this.orderId,
                                    payment_intent_id: this.clientSecret.split('_secret_')[0]
                                })
                            });

                            const completeResult = await completeResponse.json();

                            if (completeResult.success) {
                                // Redirect to confirmation page
                                window.location.href = completeResult.redirect_url;
                            } else {
                                throw new Error(completeResult.message);
                            }

                        } catch (error) {
                            console.error('Payment error:', error);
                            showToast('Payment failed: ' + error.message, 'error');

                            // Record error
                            if (this.orderId) {
                                fetch('{{ route('api.checkout.error') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                            .getAttribute('content')
                                    },
                                    body: JSON.stringify({
                                        order_id: this.orderId,
                                        error_message: error.message
                                    })
                                });
                            }
                        } finally {
                            this.processing = false;
                        }
                    }
        }
        }
    </script>
@endsection
