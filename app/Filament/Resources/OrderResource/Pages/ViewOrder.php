<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order\Order;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print_invoice')
                ->label('Print Invoice')
                ->icon('heroicon-o-printer')
                ->color('gray'),
            // ->url(fn (Order $record): string => route('orders.invoice', $record))
            // ->openUrlInNewTab(),

            Actions\Action::make('send_invoice')
                ->label('Send Invoice')
                ->icon('heroicon-o-envelope')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Send Invoice Email')
                ->modalDescription('Send an invoice copy to the customer\'s email address.')
                ->action(function (Order $record): void {
                    // Implement send invoice logic
                    \Filament\Notifications\Notification::make()
                        ->title('Invoice sent successfully')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('refund_order')
                ->label('Process Refund')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Process Refund')
                ->modalDescription('This will process a full refund for this order.')
                ->action(function (Order $record): void {
                    // Implement refund logic
                    if ($record->canBeRefunded()) {
                        $record->updateStatus('refunded', 'Refund processed via admin panel', auth()->user());
                        \Filament\Notifications\Notification::make()
                            ->title('Refund processed successfully')
                            ->success()
                            ->send();
                    }
                })
                ->visible(fn (Order $record): bool => $record->canBeRefunded()),

            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Header Summary Section
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\Group::make([
                                        Infolists\Components\TextEntry::make('order_number')
                                            ->label('Order Number')
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                            ->weight(FontWeight::Bold)
                                            ->copyable(),

                                        Infolists\Components\TextEntry::make('status')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'pending_payment', 'payment_failed' => 'warning',
                                                'processing' => 'info',
                                                'partially_fulfilled' => 'primary',
                                                'fulfilled', 'completed' => 'success',
                                                'cancelled', 'refunded' => 'danger',
                                                default => 'gray',
                                            }),

                                        Infolists\Components\Grid::make(2)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('created_at')
                                                    ->label('Order Date')
                                                    ->dateTime('M j, Y g:i A'),

                                                Infolists\Components\TextEntry::make('payment_status')
                                                    ->label('Payment Status')
                                                    ->badge()
                                                    ->color(fn (string $state): string => match ($state) {
                                                        'succeeded' => 'success',
                                                        'pending', 'requires_action', 'requires_payment_method' => 'warning',
                                                        'failed', 'canceled' => 'danger',
                                                        default => 'gray',
                                                    }),
                                            ]),
                                    ]),

                                    Infolists\Components\Group::make([
                                        Infolists\Components\TextEntry::make('total_amount')
                                            ->label('Order Total')
                                            ->getStateUsing(fn (Order $record): string => $record->getTotalMoney()->format())
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                            ->weight(FontWeight::Bold)
                                            ->color('success')
                                            ->icon('heroicon-m-currency-pound')
                                            ->iconPosition(IconPosition::Before),

                                        Infolists\Components\TextEntry::make('vendor.business_name')
                                            ->label('Store')
                                            ->default('Main Store')
                                            ->badge()
                                            ->color('info'),

                                        Infolists\Components\TextEntry::make('items_summary')
                                            ->label('Items')
                                            ->getStateUsing(fn (Order $record): string => $record->getTotalItems().' items total')
                                            ->icon('heroicon-m-cube')
                                            ->iconPosition(IconPosition::Before),
                                    ])->grow(false),
                                ]),
                        ]),
                    ])
                    ->columnSpanFull(),

                Infolists\Components\Tabs::make('Order Details')
                    ->tabs([
                        // Overview Tab
                        Infolists\Components\Tabs\Tab::make('Overview')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Infolists\Components\Grid::make(3)
                                    ->schema([
                                        // Left Column - Order Items
                                        Infolists\Components\Group::make([
                                            Infolists\Components\Section::make('Order Items')
                                                ->schema([
                                                    Infolists\Components\RepeatableEntry::make('items')
                                                        ->schema([
                                                            Infolists\Components\Grid::make(6)
                                                                ->schema([
                                                                    Infolists\Components\ImageEntry::make('product.image')
                                                                        ->label('')
                                                                        ->height(60)
                                                                        ->width(60)
                                                                        ->extraImgAttributes(['class' => 'rounded-lg']),

                                                                    Infolists\Components\Group::make([
                                                                        Infolists\Components\TextEntry::make('product.name')
                                                                            ->label('')
                                                                            ->weight(FontWeight::SemiBold),
                                                                        Infolists\Components\TextEntry::make('variant.name')
                                                                            ->label('')
                                                                            ->color('gray')
                                                                            ->placeholder(''),
                                                                        Infolists\Components\TextEntry::make('product.sku')
                                                                            ->label('')
                                                                            ->color('gray')
                                                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Small),
                                                                    ])->columnSpan(2),

                                                                    Infolists\Components\TextEntry::make('unit_price')
                                                                        ->label('Unit Price')
                                                                        ->money('GBP'),

                                                                    Infolists\Components\TextEntry::make('quantity')
                                                                        ->label('Qty')
                                                                        ->alignCenter(),

                                                                    Infolists\Components\TextEntry::make('total_price')
                                                                        ->label('Total')
                                                                        ->getStateUsing(fn ($record): string => '£'.number_format($record->total_price / 100, 2))
                                                                        ->weight(FontWeight::SemiBold),
                                                                ]),
                                                        ])
                                                        ->contained(false),
                                                ]),

                                            Infolists\Components\Section::make('Order Summary')
                                                ->schema([
                                                    Infolists\Components\Grid::make(2)
                                                        ->schema([
                                                            Infolists\Components\TextEntry::make('subtotal_amount')
                                                                ->label('Subtotal')
                                                                ->getStateUsing(fn (Order $record): string => $record->getSubtotalMoney()->format()),

                                                            Infolists\Components\TextEntry::make('shipping_amount')
                                                                ->label('Shipping')
                                                                ->getStateUsing(fn (Order $record): string => $record->getShippingMoney()->format()),

                                                            Infolists\Components\TextEntry::make('tax_amount')
                                                                ->label('VAT')
                                                                ->getStateUsing(function (Order $record): string {
                                                                    $rate = number_format($record->tax_rate * 100, 1);
                                                                    $amount = $record->getTaxMoney()->format();

                                                                    return "{$amount} ({$rate}%)";
                                                                }),

                                                            Infolists\Components\TextEntry::make('discount_amount')
                                                                ->label('Discount')
                                                                ->getStateUsing(fn (Order $record): string => $record->discount_amount > 0 ? '-'.$record->getDiscountMoney()->format() : '£0.00')
                                                                ->color('success')
                                                                ->visible(fn (Order $record): bool => $record->discount_amount > 0),
                                                        ]),

                                                    Infolists\Components\TextEntry::make('total_amount')
                                                        ->label('Total Amount')
                                                        ->getStateUsing(fn (Order $record): string => $record->getTotalMoney()->format())
                                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                                        ->weight(FontWeight::Bold)
                                                        ->color('success'),
                                                ]),
                                        ])
                                            ->columnSpan(['lg' => 2]),

                                        // Right Column - Customer & Addresses
                                        Infolists\Components\Group::make([
                                            Infolists\Components\Section::make('Customer Information')
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('customer.name')
                                                        ->label('Customer Name')
                                                        ->weight(FontWeight::SemiBold),

                                                    Infolists\Components\TextEntry::make('customer.email')
                                                        ->label('Email')
                                                        ->copyable(),

                                                    Infolists\Components\TextEntry::make('customer.phone')
                                                        ->label('Phone')
                                                        ->placeholder('Not provided'),

                                                    Infolists\Components\TextEntry::make('customer_notes')
                                                        ->label('Customer Notes')
                                                        ->placeholder('No notes provided')
                                                        ->columnSpanFull(),
                                                ]),

                                            Infolists\Components\Section::make('Billing Address')
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('billing_address')
                                                        ->label('')
                                                        ->getStateUsing(function (Order $record): string {
                                                            $address = $record->billing_address;
                                                            if (! $address) {
                                                                return 'No billing address provided';
                                                            }

                                                            return collect([
                                                                $address['name'] ?? null,
                                                                $address['company'] ?? null,
                                                                $address['address_line_1'] ?? null,
                                                                $address['address_line_2'] ?? null,
                                                                $address['city'] ?? null,
                                                                $address['state_county'] ?? null,
                                                                $address['postal_code'] ?? null,
                                                                $address['country'] ?? null,
                                                            ])->filter()->join("\n");
                                                        })
                                                        ->extraAttributes(['style' => 'white-space: pre-line;']),
                                                ]),

                                            Infolists\Components\Section::make('Shipping Address')
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('shipping_address')
                                                        ->label('')
                                                        ->getStateUsing(function (Order $record): string {
                                                            $address = $record->shipping_address;
                                                            if (! $address) {
                                                                return 'No shipping address provided';
                                                            }

                                                            return collect([
                                                                $address['name'] ?? null,
                                                                $address['company'] ?? null,
                                                                $address['address_line_1'] ?? null,
                                                                $address['address_line_2'] ?? null,
                                                                $address['city'] ?? null,
                                                                $address['state_county'] ?? null,
                                                                $address['postal_code'] ?? null,
                                                                $address['country'] ?? null,
                                                            ])->filter()->join("\n");
                                                        })
                                                        ->extraAttributes(['style' => 'white-space: pre-line;']),
                                                ]),
                                        ])
                                            ->columnSpan(['lg' => 1]),
                                    ]),
                            ]),

                        // Payment & Financial Tab
                        Infolists\Components\Tabs\Tab::make('Payment & Financial')
                            ->icon('heroicon-m-credit-card')
                            ->schema([
                                Infolists\Components\Section::make('Payment Information')
                                    ->schema([
                                        Infolists\Components\Grid::make(3)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('payment_status')
                                                    ->label('Payment Status')
                                                    ->badge()
                                                    ->color(fn (string $state): string => match ($state) {
                                                        'succeeded' => 'success',
                                                        'pending', 'requires_action', 'requires_payment_method' => 'warning',
                                                        'failed', 'canceled' => 'danger',
                                                        default => 'gray',
                                                    }),

                                                Infolists\Components\TextEntry::make('stripe_payment_intent_id')
                                                    ->label('Stripe Payment Intent')
                                                    ->copyable()
                                                    ->placeholder('Not available'),

                                                Infolists\Components\TextEntry::make('payment_confirmed_at')
                                                    ->label('Payment Confirmed')
                                                    ->dateTime('M j, Y g:i A')
                                                    ->placeholder('Not confirmed'),
                                            ]),
                                    ]),

                                Infolists\Components\Section::make('Financial Breakdown')
                                    ->schema([
                                        Infolists\Components\Grid::make(4)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('subtotal_amount')
                                                    ->label('Subtotal')
                                                    ->getStateUsing(fn (Order $record): string => $record->getSubtotalMoney()->format()),

                                                Infolists\Components\TextEntry::make('tax_details')
                                                    ->label('VAT Details')
                                                    ->getStateUsing(function (Order $record): string {
                                                        $rate = number_format($record->tax_rate * 100, 1);
                                                        $inclusive = $record->tax_inclusive ? 'Inclusive' : 'Exclusive';

                                                        return "{$rate}% ({$inclusive})";
                                                    }),

                                                Infolists\Components\TextEntry::make('shipping_amount')
                                                    ->label('Shipping')
                                                    ->getStateUsing(fn (Order $record): string => $record->getShippingMoney()->format()),

                                                Infolists\Components\TextEntry::make('currency')
                                                    ->label('Currency')
                                                    ->badge(),
                                            ]),

                                        Infolists\Components\TextEntry::make('total_amount')
                                            ->label('Final Total')
                                            ->getStateUsing(fn (Order $record): string => $record->getTotalMoney()->format())
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                            ->weight(FontWeight::Bold)
                                            ->color('success'),
                                    ]),

                                Infolists\Components\Section::make('Related Payments')
                                    ->schema([
                                        Infolists\Components\RepeatableEntry::make('payments')
                                            ->schema([
                                                Infolists\Components\Grid::make(5)
                                                    ->schema([
                                                        Infolists\Components\TextEntry::make('amount')
                                                            ->money('GBP'),

                                                        Infolists\Components\TextEntry::make('status')
                                                            ->badge(),

                                                        Infolists\Components\TextEntry::make('payment_method')
                                                            ->placeholder('Unknown'),

                                                        Infolists\Components\TextEntry::make('created_at')
                                                            ->dateTime('M j, Y g:i A'),

                                                        Infolists\Components\TextEntry::make('stripe_payment_intent_id')
                                                            ->label('Stripe ID')
                                                            ->copyable()
                                                            ->limit(20),
                                                    ]),
                                            ])
                                            ->contained(false),
                                    ])
                                    ->visible(fn (Order $record): bool => $record->payments()->count() > 0),
                            ]),

                        // Fulfillment & Shipping Tab
                        Infolists\Components\Tabs\Tab::make('Fulfillment & Shipping')
                            ->icon('heroicon-m-truck')
                            ->schema([
                                Infolists\Components\Section::make('Fulfillment Status')
                                    ->schema([
                                        Infolists\Components\Grid::make(4)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('fulfillment_status')
                                                    ->label('Status')
                                                    ->getStateUsing(function (Order $record): string {
                                                        if ($record->isFullyFulfilled()) {
                                                            return 'Fully Fulfilled';
                                                        } elseif ($record->isPartiallyFulfilled()) {
                                                            return 'Partially Fulfilled';
                                                        }

                                                        return 'Pending Fulfillment';
                                                    })
                                                    ->badge()
                                                    ->color(function (Order $record): string {
                                                        if ($record->isFullyFulfilled()) {
                                                            return 'success';
                                                        }
                                                        if ($record->isPartiallyFulfilled()) {
                                                            return 'warning';
                                                        }

                                                        return 'gray';
                                                    }),

                                                Infolists\Components\TextEntry::make('fulfillment_progress')
                                                    ->label('Progress')
                                                    ->getStateUsing(fn (Order $record): string => $record->getTotalFulfilledItems().' of '.$record->getTotalItems().' items'
                                                    ),

                                                Infolists\Components\TextEntry::make('estimated_delivery_date')
                                                    ->label('Est. Delivery')
                                                    ->date('M j, Y')
                                                    ->placeholder('Not set'),

                                                Infolists\Components\TextEntry::make('shipped_at')
                                                    ->label('Shipped')
                                                    ->dateTime('M j, Y g:i A')
                                                    ->placeholder('Not shipped'),
                                            ]),
                                    ]),

                                Infolists\Components\Section::make('Item Fulfillment Details')
                                    ->schema([
                                        Infolists\Components\RepeatableEntry::make('items')
                                            ->schema([
                                                Infolists\Components\Grid::make(5)
                                                    ->schema([
                                                        Infolists\Components\TextEntry::make('product.name')
                                                            ->weight(FontWeight::SemiBold),

                                                        Infolists\Components\TextEntry::make('quantity')
                                                            ->label('Ordered')
                                                            ->alignCenter(),

                                                        Infolists\Components\TextEntry::make('quantity_fulfilled')
                                                            ->label('Fulfilled')
                                                            ->alignCenter()
                                                            ->color(fn ($record): string => $record->quantity_fulfilled >= $record->quantity ? 'success' : 'warning'
                                                            ),

                                                        Infolists\Components\TextEntry::make('fulfillment_status')
                                                            ->label('Status')
                                                            ->getStateUsing(function ($record): string {
                                                                if ($record->quantity_fulfilled >= $record->quantity) {
                                                                    return 'Complete';
                                                                } elseif ($record->quantity_fulfilled > 0) {
                                                                    return 'Partial';
                                                                }

                                                                return 'Pending';
                                                            })
                                                            ->badge()
                                                            ->color(function ($record): string {
                                                                if ($record->quantity_fulfilled >= $record->quantity) {
                                                                    return 'success';
                                                                }
                                                                if ($record->quantity_fulfilled > 0) {
                                                                    return 'warning';
                                                                }

                                                                return 'gray';
                                                            }),

                                                        Infolists\Components\TextEntry::make('tracking_number')
                                                            ->label('Tracking')
                                                            ->placeholder('No tracking')
                                                            ->copyable(),
                                                    ]),
                                            ])
                                            ->contained(false),
                                    ]),

                                Infolists\Components\Section::make('Shipping Information')
                                    ->schema([
                                        Infolists\Components\Grid::make(3)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('shipping_method')
                                                    ->label('Shipping Method')
                                                    ->getStateUsing(fn (Order $record): string => $record->metadata['shipping_method'] ?? 'Standard Shipping'
                                                    ),

                                                Infolists\Components\TextEntry::make('shipped_at')
                                                    ->label('Ship Date')
                                                    ->dateTime('M j, Y g:i A')
                                                    ->placeholder('Not shipped'),

                                                Infolists\Components\TextEntry::make('delivered_at')
                                                    ->label('Delivered')
                                                    ->dateTime('M j, Y g:i A')
                                                    ->placeholder('Not delivered'),
                                            ]),
                                    ]),
                            ]),

                        // Order History & Timeline Tab
                        Infolists\Components\Tabs\Tab::make('Order History')
                            ->icon('heroicon-m-clock')
                            ->schema([
                                Infolists\Components\Section::make('Status History')
                                    ->schema([
                                        Infolists\Components\RepeatableEntry::make('statusHistories')
                                            ->schema([
                                                Infolists\Components\Grid::make(4)
                                                    ->schema([
                                                        Infolists\Components\TextEntry::make('from_status')
                                                            ->label('From')
                                                            ->badge()
                                                            ->placeholder('Initial'),

                                                        Infolists\Components\TextEntry::make('to_status')
                                                            ->label('To')
                                                            ->badge()
                                                            ->color(fn (string $state): string => match ($state) {
                                                                'pending_payment', 'payment_failed' => 'warning',
                                                                'processing' => 'info',
                                                                'partially_fulfilled' => 'primary',
                                                                'fulfilled', 'completed' => 'success',
                                                                'cancelled', 'refunded' => 'danger',
                                                                default => 'gray',
                                                            }),

                                                        Infolists\Components\TextEntry::make('user.name')
                                                            ->label('Changed By')
                                                            ->placeholder('System'),

                                                        Infolists\Components\TextEntry::make('created_at')
                                                            ->label('When')
                                                            ->dateTime('M j, Y g:i A'),
                                                    ]),

                                                Infolists\Components\TextEntry::make('notes')
                                                    ->label('Notes')
                                                    ->placeholder('No notes')
                                                    ->columnSpanFull(),
                                            ])
                                            ->contained(false),
                                    ]),

                                Infolists\Components\Section::make('Important Dates')
                                    ->schema([
                                        Infolists\Components\Grid::make(2)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('created_at')
                                                    ->label('Order Created')
                                                    ->dateTime('M j, Y g:i A'),

                                                Infolists\Components\TextEntry::make('payment_confirmed_at')
                                                    ->label('Payment Confirmed')
                                                    ->dateTime('M j, Y g:i A')
                                                    ->placeholder('Not confirmed'),

                                                Infolists\Components\TextEntry::make('shipped_at')
                                                    ->label('Shipped')
                                                    ->dateTime('M j, Y g:i A')
                                                    ->placeholder('Not shipped'),

                                                Infolists\Components\TextEntry::make('delivered_at')
                                                    ->label('Delivered')
                                                    ->dateTime('M j, Y g:i A')
                                                    ->placeholder('Not delivered'),

                                                Infolists\Components\TextEntry::make('completed_at')
                                                    ->label('Completed')
                                                    ->dateTime('M j, Y g:i A')
                                                    ->placeholder('Not completed'),

                                                Infolists\Components\TextEntry::make('cancelled_at')
                                                    ->label('Cancelled')
                                                    ->dateTime('M j, Y g:i A')
                                                    ->placeholder('Not cancelled'),
                                            ]),
                                    ]),
                            ]),

                        // Notes & Metadata Tab
                        Infolists\Components\Tabs\Tab::make('Notes & Metadata')
                            ->icon('heroicon-m-document-text')
                            ->schema([
                                Infolists\Components\Section::make('Order Notes')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('notes')
                                            ->label('Internal Notes')
                                            ->placeholder('No internal notes')
                                            ->columnSpanFull(),

                                        Infolists\Components\TextEntry::make('customer_notes')
                                            ->label('Customer Notes')
                                            ->placeholder('No customer notes')
                                            ->columnSpanFull(),
                                    ]),

                                Infolists\Components\Section::make('System Information')
                                    ->schema([
                                        Infolists\Components\Grid::make(2)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('id')
                                                    ->label('Order ID')
                                                    ->badge()
                                                    ->color('gray'),

                                                Infolists\Components\TextEntry::make('guest_token')
                                                    ->label('Guest Token')
                                                    ->placeholder('Registered customer')
                                                    ->copyable(),

                                                Infolists\Components\TextEntry::make('currency')
                                                    ->label('Currency')
                                                    ->badge(),

                                                Infolists\Components\TextEntry::make('tax_inclusive')
                                                    ->label('Tax Inclusive')
                                                    ->getStateUsing(fn (Order $record): string => $record->tax_inclusive ? 'Yes' : 'No')
                                                    ->badge()
                                                    ->color(fn (Order $record): string => $record->tax_inclusive ? 'success' : 'warning'),
                                            ]),
                                    ]),

                                Infolists\Components\Section::make('Custom Metadata')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('metadata')
                                            ->label('Additional Data')
                                            ->getStateUsing(function (Order $record): string {
                                                if (! $record->metadata || empty($record->metadata)) {
                                                    return 'No additional metadata';
                                                }

                                                return json_encode($record->metadata, JSON_PRETTY_PRINT);
                                            })
                                            ->extraAttributes(['style' => 'white-space: pre-line; font-family: monospace;'])
                                            ->columnSpanFull(),
                                    ])
                                    ->visible(fn (Order $record): bool => ! empty($record->metadata)),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
