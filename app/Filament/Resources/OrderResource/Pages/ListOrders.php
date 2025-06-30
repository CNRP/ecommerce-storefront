<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order\Order;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Order')
                ->icon('heroicon-o-plus'),

            Actions\Action::make('export_orders')
                ->label('Export Orders')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action(function () {
                    // Implement export functionality
                    \Filament\Notifications\Notification::make()
                        ->title('Export started')
                        ->body('You will receive an email when the export is complete.')
                        ->info()
                        ->send();
                }),

            Actions\Action::make('bulk_status_update')
                ->label('Bulk Status Update')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\Select::make('status')
                        ->label('New Status')
                        ->options([
                            'processing' => 'Processing',
                            'fulfilled' => 'Fulfilled',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ])
                        ->required(),

                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->placeholder('Reason for bulk status update')
                        ->required(),
                ])
                ->action(function (array $data) {
                    // This would be implemented with selected records
                    \Filament\Notifications\Notification::make()
                        ->title('Bulk update feature')
                        ->body('Use table bulk actions to update multiple orders.')
                        ->info()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Orders')
                ->badge(Order::count())
                ->badgeColor('gray'),

            'pending_payment' => Tab::make('Pending Payment')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending_payment'))
                ->badge(Order::where('status', 'pending_payment')->count())
                ->badgeColor('warning'),

            'processing' => Tab::make('Processing')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'processing'))
                ->badge(Order::where('status', 'processing')->count())
                ->badgeColor('info'),

            'fulfillment' => Tab::make('Fulfillment')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['partially_fulfilled', 'fulfilled']))
                ->badge(Order::whereIn('status', ['partially_fulfilled', 'fulfilled'])->count())
                ->badgeColor('primary'),

            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed'))
                ->badge(Order::where('status', 'completed')->count())
                ->badgeColor('success'),

            'issues' => Tab::make('Issues')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['payment_failed', 'cancelled', 'refunded']))
                ->badge(Order::whereIn('status', ['payment_failed', 'cancelled', 'refunded'])->count())
                ->badgeColor('danger'),

            'today' => Tab::make('Today')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today()))
                ->badge(Order::whereDate('created_at', today())->count())
                ->badgeColor('success'),

            'this_week' => Tab::make('This Week')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]))
                ->badge(Order::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ])->count())
                ->badgeColor('info'),

            'high_value' => Tab::make('High Value')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('total_amount', '>=', 10000)) // £100+
                ->badge(Order::where('total_amount', '>=', 10000)->count())
                ->badgeColor('warning'),

            'requires_attention' => Tab::make('Requires Attention')
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->where(function ($q) {
                        $q->where('payment_status', 'requires_action')
                            ->orWhere('payment_status', 'requires_payment_method')
                            ->orWhere('payment_status', 'failed')
                            ->orWhere(function ($subQ) {
                                $subQ->where('status', 'processing')
                                    ->where('created_at', '<', now()->subDays(3));
                            });
                    });
                })
                ->badge(function (): int {
                    return Order::where(function ($q) {
                        $q->where('payment_status', 'requires_action')
                            ->orWhere('payment_status', 'requires_payment_method')
                            ->orWhere('payment_status', 'failed')
                            ->orWhere(function ($subQ) {
                                $subQ->where('status', 'processing')
                                    ->where('created_at', '<', now()->subDays(3));
                            });
                    })->count();
                })
                ->badgeColor('danger'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // You can add dashboard widgets here
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // You can add summary widgets here
        ];
    }
}
