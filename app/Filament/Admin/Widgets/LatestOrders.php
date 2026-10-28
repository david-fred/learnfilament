<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;


class LatestOrders extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest()->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('user.name')->label('Customer'),
                TextColumn::make('product.name')->label('Product'),
                TextColumn::make('price')->money('NGN'),
                TextColumn::make('created_at')->dateTime()->label('Date'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
    protected static ?int $sort = 4;
}
