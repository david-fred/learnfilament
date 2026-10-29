<?php

namespace App\Filament\Admin\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
//use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\BulkAction;
//use Filament\Support\Enums\Icon;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use App\Actions\UpdateCustomerAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use App\Models\Customer;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use App\Actions\DeleteRecordAction;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(isGlobal: true)
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('is_verified')
                    ->label('Verified'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                ActionGroup::make([
                    EditAction::make()
                    ->successNotification(null)
                        ->schema([
                            TextInput::make('name')->required(),
                            TextInput::make('email')->required()->email(),
                        ])
                        ->action(function (Customer $record, array $data, UpdateCustomerAction $updateCustomer): void {
                            try {
                                $updateCustomer->handle($record, $data);
                                Notification::make()->title('Customer updated successfully')->success()->send();
                            } catch (\Throwable $e) {
                                Notification::make()->title('Error updating customer')->body($e->getMessage())->danger()->send();
                            }
                        }),
                    DeleteAction::make()
                        ->successNotification(null)
                        ->action(function (Customer $record, DeleteRecordAction $action) {
                            $action->handle($record);
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                    BulkAction::make('markAsVerified')
                        ->icon(Heroicon::OutlinedCheckBadge)
                        ->requiresConfirmation()
                        ->action(fn(EloquentCollection $records) => $records->each->update(['is_verified' => true]))
                        ->deselectRecordsAfterCompletion(),
                    //]),
                ])
            ]);
    }
}
