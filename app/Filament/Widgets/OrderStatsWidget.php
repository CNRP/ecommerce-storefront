<?php

// app/Filament/Widgets/OrderStatsWidget.php

namespace App\Filament\Widgets;

use App\Models\Order\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Get current period stats
        $todayOrders = Order::whereDate('created_at', today())->count();
        $weekOrders = Order::whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ])->count();
        $monthOrders = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Get previous period for comparison
        $yesterdayOrders = Order::whereDate('created_at', today()->subDay())->count();
        $lastWeekOrders = Order::whereBetween('created_at', [
            now()->subWeek()->startOfWeek(),
            now()->subWeek()->endOfWeek(),
        ])->count();
        $lastMonthOrders = Order::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        // Calculate revenue
        $todayRevenue = Order::whereDate('created_at', today())
            ->where('payment_status', 'succeeded')
            ->sum('total_amount');
        $monthRevenue = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('payment_status', 'succeeded')
            ->sum('total_amount');

        // Pending orders that need attention
        $pendingPayment = Order::where('status', 'pending_payment')->count();
        $requiresAction = Order::whereIn('payment_status', [
            'requires_action',
            'requires_payment_method',
            'failed',
        ])->count();

        return [
            Stat::make('Today\'s Orders', $todayOrders)
                ->description($this->getChangeDescription($todayOrders, $yesterdayOrders))
                ->descriptionIcon($todayOrders >= $yesterdayOrders ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($todayOrders >= $yesterdayOrders ? 'success' : 'danger')
                ->chart($this->getOrderTrendChart(7)), // Last 7 days

            Stat::make('Weekly Orders', $weekOrders)
                ->description($this->getChangeDescription($weekOrders, $lastWeekOrders))
                ->descriptionIcon($weekOrders >= $lastWeekOrders ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($weekOrders >= $lastWeekOrders ? 'success' : 'danger'),

            Stat::make('Monthly Orders', $monthOrders)
                ->description($this->getChangeDescription($monthOrders, $lastMonthOrders))
                ->descriptionIcon($monthOrders >= $lastMonthOrders ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthOrders >= $lastMonthOrders ? 'success' : 'danger'),

            Stat::make('Today\'s Revenue', '£'.number_format($todayRevenue / 100, 2))
                ->description('Confirmed payments only')
                ->descriptionIcon('heroicon-m-currency-pound')
                ->color('success'),

            Stat::make('Monthly Revenue', '£'.number_format($monthRevenue / 100, 2))
                ->description('Confirmed payments only')
                ->descriptionIcon('heroicon-m-currency-pound')
                ->color('success'),

            Stat::make('Needs Attention', $pendingPayment + $requiresAction)
                ->description("{$pendingPayment} pending payment, {$requiresAction} payment issues")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($pendingPayment + $requiresAction > 0 ? 'warning' : 'success'),
        ];
    }

    private function getChangeDescription(int $current, int $previous): string
    {
        if ($previous === 0) {
            return $current > 0 ? 'New orders!' : 'No change';
        }

        $change = $current - $previous;
        $percentage = round(($change / $previous) * 100);

        if ($change > 0) {
            return "+{$percentage}% from previous period";
        } elseif ($change < 0) {
            return "{$percentage}% from previous period";
        }

        return 'No change from previous period';
    }

    private function getOrderTrendChart(int $days): array
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Order::whereDate('created_at', $date)->count();
            $data[] = $count;
        }

        return $data;
    }
}

// app/Filament/Widgets/OrderStatusChart.php

namespace App\Filament\Widgets;

use App\Models\Order\Order;
use Filament\Widgets\ChartWidget;

class OrderStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Order Status Distribution';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $statuses = [
            'pending_payment' => 'Pending Payment',
            'processing' => 'Processing',
            'partially_fulfilled' => 'Partially Fulfilled',
            'fulfilled' => 'Fulfilled',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
        ];

        $data = [];
        $labels = [];

        foreach ($statuses as $status => $label) {
            $count = Order::where('status', $status)->count();
            if ($count > 0) {
                $data[] = $count;
                $labels[] = $label;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data,
                    'backgroundColor' => [
                        '#f59e0b', // warning - pending_payment
                        '#3b82f6', // info - processing
                        '#8b5cf6', // primary - partially_fulfilled
                        '#10b981', // success - fulfilled
                        '#059669', // success - completed
                        '#ef4444', // danger - cancelled
                        '#dc2626', // danger - refunded
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}

// app/Filament/Widgets/RecentOrdersWidget.php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Orders';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => ['pending_payment', 'payment_failed'],
                        'info' => 'processing',
                        'primary' => 'partially_fulfilled',
                        'success' => ['fulfilled', 'completed'],
                        'danger' => ['cancelled', 'refunded'],
                    ]),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->getStateUsing(fn (Order $record): string => $record->getTotalMoney()->format())
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, g:i A')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ]);
    }
}

// app/Filament/Widgets/OrderRevenueChart.php

namespace App\Filament\Widgets;

use App\Models\Order\Order;
use Filament\Widgets\ChartWidget;

class OrderRevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue Trend (Last 30 Days)';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $revenue = Order::whereDate('created_at', $date)
                ->where('payment_status', 'succeeded')
                ->sum('total_amount');

            $data[] = $revenue / 100; // Convert pence to pounds
            $labels[] = $date->format('M j');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (£)',
                    'data' => $data,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "£" + value.toFixed(2); }',
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
