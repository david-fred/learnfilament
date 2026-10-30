<?php

namespace App\Filament\Admin\Resources\Products\Schemas;

use App\Enums\ProductStatusEnum;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->unique(),
                TextInput::make('price')
                    ->prefix('₦')
                    ->required()
                    ->numeric(),
                TextInput::make('description'),
                //->required(),
                Select::make('status')
                    ->options(ProductStatusEnum::class)
                    ->required(),
                Select::make('tags')
                    ->relationship('tags', 'name')
                    ->preload()
                    ->multiple(),
                // ->required(),

                /**ModalTableSelect::make('category_id')
                ->relationship('category', 'name')
                ->**/
                Select::make('category_id')
                    ->relationship('category', 'name'),
                //  ->required(),
                TextInput::make('amount')
                    ->label('Stock Quantity')
                    ->numeric()
                    ->default(0)
                    ->required()

            ]);
    }
}
