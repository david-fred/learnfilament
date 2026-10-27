<?php

namespace App\Filament\Admin\Resources\Products\Pages;

use App\Models\Product;
use App\Filament\Admin\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addStock')
                ->label('Add Stock')
                ->color('gray')
                ->form([
                    Select::make('product_id')
                        ->label('Product')
                        ->options(Product::query()->pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                    TextInput::make('amount')
                        ->label('Amount')
                        ->numeric()
                        ->required()

                ])
                ->action(function (array $data) {
                    Product::where('id', $data['product_id'])
                        ->update([
                            'amount' => $data['amount']
                        ]);
                }),
            CreateAction::make()->slideOver(),
        ];
    }
}
