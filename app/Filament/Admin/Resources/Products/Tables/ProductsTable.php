<?php

namespace App\Filament\Admin\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;


class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->sortable()
                    ->formatStateUsing(fn($state): float => $state / 100)
                    ->money('NGN'),
                TextColumn::make('description')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('amount')
                    ->badge(),
            ])
            ->defaultSort('name')
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('addStock')
                    ->label('Add Stock')
                    ->form([
                        TextInput::make('quantity')->numeric()->required(),
                    ])
                    ->action(function (array $data, $record) {
                        $record->increment('stock', $data['quantity']);
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
