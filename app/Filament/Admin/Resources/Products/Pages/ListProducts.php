<?php

namespace App\Filament\Admin\Resources\Products\Pages;

use App\Actions\CreateProductAction;
use App\Models\Product;
use App\Filament\Admin\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;
use Throwable;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addStock')
                ->label('Add Stock')
                ->color('gray')
                ->schema([
                    Select::make('product_id')
                        ->label('Product')
                        ->options(Product::query()->pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                    TextInput::make('amount')
                        ->label('Quantity')
                        ->numeric()
                        ->required()

                ])
                ->action(function (array $data) {
                    Product::where('id', $data['product_id'])
                        ->update([
                            'amount' => $data['amount']
                        ]);
                }),

            CreateAction::make()
                ->action(function (CreateProductAction $createProduct, array $data) {
                    try {
                        $createProduct->handle($data);
                        Notification::make()
                            ->title('Product created successfully')
                            ->success()
                            ->send();
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('Error creating product')
                            ->body($e->getMessage())
                            ->error()
                            ->send();
                        Log::error($e->getMessage());
                    }
                }),
        ];
    }
}
