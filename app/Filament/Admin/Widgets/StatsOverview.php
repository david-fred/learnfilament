<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Enums\ProductStatusEnum;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Revenue', '₦' . number_format(Order::sum('price'), 2))
                ->description('Total orders revenue')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make(' Customers', Customer::count())
                ->description('Total registered customers')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Orders', Order::count())
                ->description('Total orders placed')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),
            Stat::make('In Stock', Product::where('status', ProductStatusEnum::IN_STOCK)->count())
                ->color('success'),
            Stat::make('Out of Stock', Product::where('status', ProductStatusEnum::OUT_OF_STOCK)->count())
                ->color('danger'),
            Stat::make('Discontinued', Product::where('status', ProductStatusEnum::DISCONTINUED)->count())
                ->color('gray'),
            Stat::make('Coming Soon', Product::where('status', ProductStatusEnum::COMING_SOON)->count())
                ->color('info'),
        ];
    }

    protected static ?int $sort = 1;
}
