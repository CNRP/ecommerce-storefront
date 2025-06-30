<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Customer\Customer;
use App\Models\Product\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Wizard::make([
                Forms\Components\Wizard\Step::make('Customer & Store')
                    ->description('Select customer and store assignment')
                    ->schema([
                        Forms\Components\Section::make('Customer Information')
                            ->schema([
                                Forms\Components\Select::make('customer_id')
                                    ->label('Customer')
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required(),
                                        Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->required(),
                                        Forms\Components\TextInput::make('phone'),
                                    ])
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $customer = Customer::find($state);
                                            if ($customer) {
                                                $set('customer_details', [
                                                    'name' => $customer->name,
                                                    'email' => $customer->email,
                                                    'phone' => $customer->phone,
                                                ]);

                                                // Set addresses if customer has default addresses
                                                if ($customer->default_billing_address) {
                                                    $set('billing_address', $customer->default_billing_address);
                                                }
                                                if ($customer->default_shipping_address) {
                                                    $set('shipping_address', $customer->default_shipping_address);
                                                }
                                            }
                                        }
                                    }),

                                Forms\Components\Select::make('vendor_id')
                                    ->label('Assign to Store')
                                    ->relationship('vendor', 'business_name')
                                    ->nullable()
                                    ->placeholder('Main Store')
                                    ->searchable()
                                    ->helperText('Leave empty for main store'),
                            ]),

                        Forms\Components\Section::make('Customer Snapshot')
                            ->description('Customer details at time of order')
                            ->schema([
                                Forms\Components\KeyValue::make('customer_details')
                                    ->label('Customer Details')
                                    ->keyLabel('Field')
                                    ->valueLabel('Value')
                                    ->default([
                                        'name' => '',
                                        'email' => '',
                                        'phone' => '',
                                    ])
                                    ->required(),
                            ]),
                    ]),

                Forms\Components\Wizard\Step::make('Order Items')
                    ->description('Add products to the order')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->label('Order Items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\Select::make('product_id')
                                            ->label('Product')
                                            ->relationship('product', 'name')
                                            ->searchable()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                if ($state) {
                                                    $product = Product::find($state);
                                                    if ($product) {
                                                        $set('unit_price', $product->price * 100); // Convert to pence
                                                        $set('product_name', $product->name);
                                                        $set('product_sku', $product->sku);
                                                    }
                                                }
                                            }),

                                        Forms\Components\Select::make('variant_id')
                                            ->label('Variant')
                                            ->relationship('variant', 'name')
                                            ->nullable()
                                            ->searchable()
                                            ->visible(fn (Forms\Get $get) => $get('product_id'))
                                            ->options(function (Forms\Get $get) {
                                                $productId = $get('product_id');
                                                if (! $productId) {
                                                    return [];
                                                }

                                                return Product::find($productId)
                                                    ?->variants()
                                                    ->pluck('name', 'id')
                                                    ->toArray() ?? [];
                                            })
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                if ($state) {
                                                    $variant = \App\Models\Product\ProductVariant::find($state);
                                                    if ($variant) {
                                                        $set('unit_price', $variant->price * 100); // Convert to pence
                                                    }
                                                }
                                            }),

                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Quantity')
                                            ->required()
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                $unitPrice = $get('unit_price') ?? 0;
                                                $set('total_price', $unitPrice * intval($state));
                                            }),

                                        Forms\Components\TextInput::make('unit_price')
                                            ->label('Unit Price (pence)')
                                            ->required()
                                            ->numeric()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                $quantity = $get('quantity') ?? 1;
                                                $set('total_price', intval($state) * $quantity);
                                            }),
                                    ]),

                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('total_price')
                                            ->label('Total Price (pence)')
                                            ->required()
                                            ->numeric()
                                            ->disabled(),

                                        Forms\Components\TextInput::make('product_name')
                                            ->label('Product Name (Snapshot)')
                                            ->required()
                                            ->disabled(),

                                        Forms\Components\TextInput::make('product_sku')
                                            ->label('Product SKU (Snapshot)')
                                            ->required()
                                            ->disabled(),
                                    ]),
                            ])
                            ->addActionLabel('Add Product')
                            ->minItems(1)
                            ->collapsible()
                            ->cloneable()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Recalculate order totals when items change
                                $subtotal = collect($state)->sum('total_price');
                                $set('subtotal_amount', $subtotal);

                                // Calculate tax (assuming 20% VAT inclusive)
                                $taxRate = 0.20;
                                $taxAmount = round($subtotal * ($taxRate / (1 + $taxRate)));
                                $set('tax_amount', $taxAmount);

                                // Calculate total
                                $shipping = 0; // Will be set later
                                $discount = 0; // Will be set later
                                $total = $subtotal + $shipping - $discount;
                                $set('total_amount', $total);
                            }),
                    ]),

                Forms\Components\Wizard\Step::make('Addresses')
                    ->description('Set billing and shipping addresses')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Section::make('Billing Address')
                                    ->schema([
                                        Forms\Components\KeyValue::make('billing_address')
                                            ->label('')
                                            ->keyLabel('Field')
                                            ->valueLabel('Value')
                                            ->default([
                                                'name' => '',
                                                'line1' => '',
                                                'line2' => '',
                                                'city' => '',
                                                'state' => '',
                                                'postal_code' => '',
                                                'country' => 'GB',
                                            ])
                                            ->required(),
                                    ]),

                                Forms\Components\Section::make('Shipping Address')
                                    ->schema([
                                        Forms\Components\Toggle::make('same_as_billing')
                                            ->label('Same as billing address')
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                if ($state) {
                                                    $set('shipping_address', $get('billing_address'));
                                                }
                                            }),

                                        Forms\Components\KeyValue::make('shipping_address')
                                            ->label('')
                                            ->keyLabel('Field')
                                            ->valueLabel('Value')
                                            ->default([
                                                'name' => '',
                                                'line1' => '',
                                                'line2' => '',
                                                'city' => '',
                                                'state' => '',
                                                'postal_code' => '',
                                                'country' => 'GB',
                                            ])
                                            ->required()
                                            ->disabled(fn (Forms\Get $get) => $get('same_as_billing')),
                                    ]),
                            ]),
                    ]),

                Forms\Components\Wizard\Step::make('Pricing & Payment')
                    ->description('Configure pricing and payment details')
                    ->schema([
                        Forms\Components\Section::make('Order Totals')
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('subtotal_amount')
                                            ->label('Subtotal (pence)')
                                            ->required()
                                            ->numeric()
                                            ->disabled(),

                                        Forms\Components\TextInput::make('shipping_amount')
                                            ->label('Shipping (pence)')
                                            ->required()
                                            ->numeric()
                                            ->default(0)
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                $subtotal = $get('subtotal_amount') ?? 0;
                                                $discount = $get('discount_amount') ?? 0;
                                                $total = $subtotal + intval($state) - $discount;
                                                $set('total_amount', $total);
                                            }),

                                        Forms\Components\TextInput::make('discount_amount')
                                            ->label('Discount (pence)')
                                            ->required()
                                            ->numeric()
                                            ->default(0)
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                $subtotal = $get('subtotal_amount') ?? 0;
                                                $shipping = $get('shipping_amount') ?? 0;
                                                $total = $subtotal + $shipping - intval($state);
                                                $set('total_amount', $total);
                                            }),

                                        Forms\Components\TextInput::make('total_amount')
                                            ->label('Total (pence)')
                                            ->required()
                                            ->numeric()
                                            ->disabled(),
                                    ]),

                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('tax_amount')
                                            ->label('VAT Amount (pence)')
                                            ->required()
                                            ->numeric()
                                            ->disabled(),

                                        Forms\Components\TextInput::make('tax_rate')
                                            ->label('VAT Rate')
                                            ->required()
                                            ->numeric()
                                            ->step(0.0001)
                                            ->default(0.20)
                                            ->suffix('%')
                                            ->formatStateUsing(fn ($state): string => ($state * 100))
                                            ->dehydrateStateUsing(fn ($state): float => floatval($state) / 100),

                                        Forms\Components\Toggle::make('tax_inclusive')
                                            ->label('VAT Inclusive')
                                            ->default(true),
                                    ]),

                                Forms\Components\TextInput::make('currency')
                                    ->label('Currency')
                                    ->required()
                                    ->default('GBP')
                                    ->maxLength(3),
                            ]),

                        Forms\Components\Section::make('Payment Information')
                            ->schema([
                                Forms\Components\Select::make('payment_status')
                                    ->label('Initial Payment Status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'succeeded' => 'Succeeded',
                                    ])
                                    ->default('pending')
                                    ->required()
                                    ->native(false),

                                Forms\Components\DateTimePicker::make('payment_confirmed_at')
                                    ->label('Payment Confirmed At')
                                    ->visible(fn (Forms\Get $get) => $get('payment_status') === 'succeeded')
                                    ->native(false),
                            ]),
                    ]),

                Forms\Components\Wizard\Step::make('Additional Details')
                    ->description('Add notes and delivery information')
                    ->schema([
                        Forms\Components\Section::make('Order Notes')
                            ->schema([
                                Forms\Components\Textarea::make('notes')
                                    ->label('Internal Notes')
                                    ->placeholder('Private notes visible only to staff')
                                    ->rows(3),

                                Forms\Components\Textarea::make('customer_notes')
                                    ->label('Customer Notes')
                                    ->placeholder('Notes provided by the customer')
                                    ->rows(3),
                            ]),

                        Forms\Components\Section::make('Delivery Information')
                            ->schema([
                                Forms\Components\DateTimePicker::make('estimated_delivery_date')
                                    ->label('Estimated Delivery Date')
                                    ->native(false),

                                Forms\Components\KeyValue::make('metadata')
                                    ->label('Custom Metadata')
                                    ->keyLabel('Key')
                                    ->valueLabel('Value')
                                    ->addActionLabel('Add custom field')
                                    ->helperText('Store additional order information'),
                            ]),

                        Forms\Components\Section::make('Order Settings')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Initial Status')
                                    ->options([
                                        'pending_payment' => 'Pending Payment',
                                        'processing' => 'Processing',
                                    ])
                                    ->default('pending_payment')
                                    ->required()
                                    ->native(false),
                            ]),
                    ]),
            ])
                ->columnSpanFull()
                ->skippable(),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate order number if not set
        if (empty($data['order_number'])) {
            $data['order_number'] = \App\Models\Order\Order::generateOrderNumber();
        }

        // Generate guest token
        $data['guest_token'] = \App\Models\Order\Order::generateGuestToken();

        // Set payment confirmed timestamp if payment succeeded
        if ($data['payment_status'] === 'succeeded' && empty($data['payment_confirmed_at'])) {
            $data['payment_confirmed_at'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Create status history entry
        $this->getRecord()->statusHistories()->create([
            'user_id' => auth()->id(),
            'from_status' => null,
            'to_status' => $this->getRecord()->status,
            'notes' => 'Order created via admin panel',
            'created_at' => now(),
        ]);

        \Filament\Notifications\Notification::make()
            ->title('Order created successfully')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
