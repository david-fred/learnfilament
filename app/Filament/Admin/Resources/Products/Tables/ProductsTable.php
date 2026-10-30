<?php

namespace App\Filament\Admin\Resources\Products\Tables;

use App\Enums\ProductStatusEnum;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use App\Actions\BulkDeleteProductsAction;
use App\Actions\DeleteProductAction;
use DomainException;



class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                // ->rules(['required', 'string', 'min:3', 'max:255']),
                TextColumn::make('price')
                    ->sortable()
                    ->formatStateUsing(fn($state): float => $state / 100)
                    ->money('NGN'),
                TextColumn::make('description')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name'),
                // ->url(fn (Product $record): string => CategoryResource::getUrl('edit', ['record' => $record->category]))
                TextColumn::make('tags.name')
                    ->badge(),
                TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
                SelectColumn::make('status')
                    ->searchableOptions()
                    ->options(ProductStatusEnum::class),
                CheckboxColumn::make('is_active'),
                TextColumn::make('amount')
                    ->badge()
                    ->label('Quantity'),
            ])
            ->defaultSort('name')
            ->filters([
                /* SelectFilter::make('category')
                ->relationship('category','name'), */

                SelectFilter::make('status')
                    ->options(ProductStatusEnum::class),
                SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Filter::make('created_from')
                    ->schema([
                        DatePicker::make('created_from'),

                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date));
                    }),
                Filter::make('created_until')
                    ->schema([
                        DatePicker::make('created_until'),

                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_until'], fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->recordActions([
                ActionGroup::make([
                    Action::make('addStock')
                        ->label('Add Stock')
                        ->color('success')
                        ->schema([
                            TextInput::make('amount')
                                ->label('Quantity')
                                ->numeric()
                                ->minValue(1)
                                ->required(),
                        ])
                        ->action(function (array $data, Product $record) {
                            $record->increment('amount', $data['amount']);

                            Notification::make()
                                ->title('Stock added')
                                ->success()
                                ->send();
                        }),

                    EditAction::make(),
                    DeleteAction::make()
                        ->action(function (Product $record, DeleteProductAction $deleteProduct, Action $action) {
                            try {
                                $deleteProduct->handle($record);
                            } catch (DomainException $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Cannot delete product')
                                    ->body($e->getMessage())
                                    ->send();

                                $action->halt();
                            }
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (EloquentCollection $records, BulkDeleteProductsAction $bulkDelete, Action $action) {
                            try {
                                $bulkDelete->handle($records);
                            } catch (DomainException $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Cannot delete products')
                                    ->body($e->getMessage())
                                    ->send();

                                $action->halt();
                            }
                        }),
                ]),
            ]);
    }
}
