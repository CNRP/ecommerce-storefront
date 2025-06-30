<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order\Order;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $recordTitleAttribute = 'order_number';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        // Form is handled in individual pages for better maintainability
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => ['pending_payment', 'payment_failed'],
                        'info' => 'processing',
                        'primary' => 'partially_fulfilled',
                        'success' => ['fulfilled', 'completed'],
                        'danger' => ['cancelled', 'refunded'],
                    ])
                    ->icons([
                        'heroicon-o-clock' => ['pending_payment'],
                        'heroicon-o-exclamation-triangle' => 'payment_failed',
                        'heroicon-o-cog-6-tooth' => 'processing',
                        'heroicon-o-truck' => 'partially_fulfilled',
                        'heroicon-o-check-circle' => 'fulfilled',
                        'heroicon-o-star' => 'completed',
                        'heroicon-o-x-circle' => 'cancelled',
                        'heroicon-o-arrow-path' => 'refunded',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('vendor.business_name')
                    ->label('Store')
                    ->default('Main Store')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->getStateUsing(fn (Order $record): string => $record->getTotalMoney()->format())
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Payment')
                    ->colors([
                        'warning' => ['pending', 'requires_action', 'requires_payment_method'],
                        'success' => 'succeeded',
                        'danger' => ['failed', 'canceled'],
                        'info' => 'processing',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->getStateUsing(fn (Order $record): string => $record->getTotalItems().' items')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('estimated_delivery_date')
                    ->label('Est. Delivery')
                    ->date('M j, Y')
                    ->placeholder('Not set')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending_payment' => 'Pending Payment',
                        'payment_failed' => 'Payment Failed',
                        'processing' => 'Processing',
                        'partially_fulfilled' => 'Partially Fulfilled',
                        'fulfilled' => 'Fulfilled',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'requires_action' => 'Requires Action',
                        'requires_payment_method' => 'Requires Payment Method',
                        'succeeded' => 'Succeeded',
                        'failed' => 'Failed',
                        'canceled' => 'Canceled',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('vendor')
                    ->relationship('vendor', 'business_name')
                    ->label('Store'),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('Created from'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('Created until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('total_amount')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('amount_from')
                            ->label('Total from')
                            ->numeric()
                            ->prefix('£'),
                        \Filament\Forms\Components\TextInput::make('amount_to')
                            ->label('Total to')
                            ->numeric()
                            ->prefix('£'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_from'],
                                fn (Builder $query, $amount): Builder => $query->where('total_amount', '>=', $amount * 100),
                            )
                            ->when(
                                $data['amount_to'],
                                fn (Builder $query, $amount): Builder => $query->where('total_amount', '<=', $amount * 100),
                            );
                    }),

                Tables\Filters\TernaryFilter::make('requires_payment')
                    ->placeholder('All orders')
                    ->trueLabel('Requires payment')
                    ->falseLabel('Payment complete')
                    ->queries(
                        true: fn (Builder $query) => $query->whereIn('payment_status', ['pending', 'requires_action', 'requires_payment_method']),
                        false: fn (Builder $query) => $query->where('payment_status', 'succeeded'),
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('mark_as_processing')
                        ->label('Mark as Processing')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function (Order $record): void {
                            $record->updateStatus('processing', 'Status updated via admin panel', auth()->user());
                        })
                        ->visible(fn (Order $record): bool => $record->canTransitionTo('processing')),

                    Tables\Actions\Action::make('mark_as_fulfilled')
                        ->label('Mark as Fulfilled')
                        ->icon('heroicon-o-truck')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Order $record): void {
                            $record->updateStatus('fulfilled', 'Order fulfilled via admin panel', auth()->user());
                        })
                        ->visible(fn (Order $record): bool => $record->canTransitionTo('fulfilled')),

                    Tables\Actions\Action::make('cancel_order')
                        ->label('Cancel Order')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Cancel Order')
                        ->modalDescription('Are you sure you want to cancel this order? This action cannot be undone.')
                        ->action(function (Order $record): void {
                            $record->updateStatus('cancelled', 'Order cancelled via admin panel', auth()->user());
                        })
                        ->visible(fn (Order $record): bool => $record->canBeCancelled()),

                    Tables\Actions\Action::make('send_tracking_email')
                        ->label('Send Tracking Email')
                        ->icon('heroicon-o-envelope')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->action(function (Order $record): void {
                            // Implement tracking email logic
                            \Filament\Notifications\Notification::make()
                                ->title('Tracking email sent')
                                ->success()
                                ->send();
                        })
                        ->visible(fn (Order $record): bool => in_array($record->status, ['fulfilled', 'partially_fulfilled'])),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_as_processing')
                        ->label('Mark as Processing')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            foreach ($records as $record) {
                                if ($record->canTransitionTo('processing')) {
                                    $record->updateStatus('processing', 'Bulk status update via admin panel', auth()->user());
                                }
                            }
                        }),

                    Tables\Actions\BulkAction::make('export_orders')
                        ->label('Export Orders')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('primary')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            // Implement export functionality
                            \Filament\Notifications\Notification::make()
                                ->title('Export started')
                                ->body('You will receive an email when the export is complete.')
                                ->info()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Relations will be handled in the view page
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'order_number',
            'customer.name',
            'customer.email',
            'customer_details->name',
            'customer_details->email',
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending_payment')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = static::getNavigationBadge();

        return $count > 0 ? 'warning' : null;
    }
}
