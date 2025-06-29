{{-- resources/views/frontend/checkout/index.blade.php --}}
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
                                    <label class="block text-sm font-medium text-gray-700 mb-2">County</label>
                                    <input type="text" x-model="billing_address.state_county" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                                    <input type="text" x-model="billing_address.postal_code" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                                    <select x-model="billing_address.country" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        <option value="GB">United Kingdom</option>
                                        <option value="US">United States</option>
                                        <option value="CA">Canada</option>
                                        <option value="AU">Australia</option>
                                    </select>
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
                                    <label class="block text-sm font-medium text-gray-700 mb-2">County</label>
                                    <input type="text" x-model="shipping_address.state_county"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                                    <input type="text" x-model="shipping_address.postal_code"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                                    <select x-model="shipping_address.country"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        <option value="GB">United Kingdom</option>
                                        <option value="US">United States</option>
                                        <option value="CA">Canada</option>
                                        <option value="AU">Australia</option>
                                    </select>
                                </div>
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
                                <!-- Stripe Elements will be mounted here -->
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
                <div class="bg-white rounded-lg shadow-sm border border-gray-300  p-6 sticky top-24">
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
                    <div class="border-t pt-4 space-y-2">
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
                        <div class="border-t pt-2 flex justify-between font-medium text-lg">
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
                // Form data
                customer: {
                    first_name: '',
                    last_name: '',
                    email: '',
                    phone: ''
                },
                billing_address: {
                    first_name: '',
                    last_name: '',
                    company: '',
                    address_line_1: '',
                    address_line_2: '',
                    city: '',
                    state_county: '', // Ensure it's an empty string, not null
                    postal_code: '',
                    country: 'GB'
                },
                shipping_address: {
                    first_name: '',
                    last_name: '',
                    company: '',
                    address_line_1: '',
                    address_line_2: '',
                    city: '',
                    state_county: '', // Ensure it's an empty string, not null
                    postal_code: '',
                    country: 'GB'
                },
                customer_notes: '',
                sameAsBilling: true,
                processing: false,

                // Stripe
                stripe: null,
                elements: null,
                paymentElement: null,
                clientSecret: null,
                orderId: null,

                init() {
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

                    // Watch for form completion to initialize Stripe
                    this.$watch('customer', () => this.checkFormAndInitializeStripe(), {
                        deep: true
                    });
                    this.$watch('billing_address', () => this.checkFormAndInitializeStripe(), {
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

                checkFormAndInitializeStripe() {
                    // Check if basic form fields are filled
                    if (this.customer.first_name && this.customer.last_name && this.customer.email &&
                        this.billing_address.first_name && this.billing_address.address_line_1 &&
                        this.billing_address.city && this.billing_address.postal_code) {

                        console.log('Form validation passed, checking Stripe initialization...');
                        console.log('Customer data:', this.customer);
                        console.log('Billing address:', this.billing_address);
                        console.log('Shipping address:', this.shipping_address);
                        console.log('Same as billing:', this.sameAsBilling);

                        if (!this.stripe) {
                            this.initializeStripe();
                        }
                    } else {
                        console.log('Form validation failed - missing required fields');
                        console.log('Customer:', this.customer);
                        console.log('Billing:', this.billing_address);
                    }
                },

                async initializeStripe() {
                    try {
                        const requestData = {
                            customer: this.customer,
                            billing_address: this.billing_address,
                            shipping_address: this.sameAsBilling ? this.billing_address : this.shipping_address,
                            customer_notes: this.customer_notes
                        };

                        console.log('Sending request data:', requestData);

                        // Initialize checkout
                        const response = await fetch('{{ route('api.checkout.initialize') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'Accept': 'application/json' // This is important!
                            },
                            body: JSON.stringify(requestData)
                        });

                        // Debug: Log the response
                        console.log('Response status:', response.status);
                        console.log('Response content-type:', response.headers.get('content-type'));

                        const responseText = await response.text();
                        console.log('Raw response (first 500 chars):', responseText.substring(0, 500));

                        // If response is not ok, show more details
                        if (!response.ok) {
                            console.error('HTTP Error:', response.status, response.statusText);

                            // For validation errors (422), show detailed errors
                            if (response.status === 422) {
                                try {
                                    const errorResult = JSON.parse(responseText);
                                    console.error('Validation errors:', errorResult);

                                    if (errorResult.errors) {
                                        // Show specific field errors
                                        Object.keys(errorResult.errors).forEach(field => {
                                            console.error(`${field}: ${errorResult.errors[field].join(', ')}`);
                                        });
                                    }

                                    throw new Error('Validation failed: ' + (errorResult.message ||
                                        'Please check the form data'));
                                } catch (parseError) {
                                    console.error('Could not parse validation error response');
                                }
                            }

                            throw new Error(`HTTP ${response.status}: Server returned an error`);
                        }

                        // Try to parse as JSON
                        let result;
                        try {
                            result = JSON.parse(responseText);
                        } catch (parseError) {
                            console.error('JSON parse error:', parseError);
                            console.error('Response was:', responseText);
                            throw new Error('Server returned invalid JSON response');
                        }

                        console.log('Parsed result:', result);

                        if (!result.success) {
                            throw new Error(result.message || 'Checkout initialization failed');
                        }

                        this.clientSecret = result.client_secret;
                        this.orderId = result.order_id;

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
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
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
