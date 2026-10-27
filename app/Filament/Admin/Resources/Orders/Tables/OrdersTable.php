<?php

namespace App\Filament\Admin\Resources\Orders\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Average;
use App\Models\Order;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Checkbox;
use Illuminate\Collections\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use App\Filament\Admin\Resources\Orders\OrderResource;

/*use Filament\Support\Icons\Icon;
use Filament\Support\Icons\Icons;
use Filament\Support\Icons\IconData;*/



class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('product.name')
                    ->searchable(),
                TextColumn::make('price')
                    ->money('NGN')
                    ->summarize([
                        Sum::make()->money('NGN')->label('Total'),
                        Average::make()->money('NGN')->label('Average'),
                    ])
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultGroup('product.name')
            ->filters([
                //
            ])
            ->recordActions([
                //ActionGroup::make([
                EditAction::make(),
                /*  Action::make('Change is completed')
                        ->icon(Heroicon::OutlinedCheckBadge)
                        ->fillForm(function (Order $order) {
                            return [
                                'is_completed' => $order->is_completed,
                            ];
                        })
                        ->schema([
                            Checkbox::make('is_completed'),
                        ])
                        ->action(function (array $data, Order $Order): void {
                            $Order->update([
                                'is_completed' => $data['is_completed'],
                            ]);
                        })*/

                Action::make('markAsCompleted')
                    ->label('Mark as completed')
                    ->requiresConfirmation()
                    ->icon(Heroicon::OutlinedCheckBadge)
                    ->hidden(fn(Order $record) => $record->is_completed)
                    ->action(fn(Order $record) => $record->update(['is_completed' => true])),

                /*->label('')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => !$record->is_completed)
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update(['is_completed' => true]);
                })*/
                // ]),
            ])
           /* ->headerActions([
                Action::make('New Order')
                ->url(fn(): string => OrderResource::getUrl('create')),
            ])*/
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('markAsCompleted')
                        ->icon(Heroicon::OutlinedCheckBadge)
                        ->requiresConfirmation()
                        ->action(fn(EloquentCollection $records) => $records->each->update(['is_completed' => true]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
