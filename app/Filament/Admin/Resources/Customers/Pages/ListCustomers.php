<?php

namespace App\Filament\Admin\Resources\Customers\Pages;

use App\Filament\Admin\Resources\Customers\CustomerResource;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ListRecords;
use App\Actions\CreateCustomerAction;
use Illuminate\Support\Facades\Log;
use Throwable;
use Filament\Forms\Components\TextInput;
use App\Models\Customer;
use Filament\Notifications\Notification;
use App\Actions\UpdateCustomerAction;




class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->successNotification(null)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(150),
                    TextInput::make('email')
                        ->required()
                        ->email()
                ])
                ->action(function (CreateCustomerAction $createCustomer, array $data) {
                    try {
                        $createCustomer->handle($data);
                        Notification::make()
                            ->title('Customer created successfully')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Error creating customer')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                        Log::error($e->getMessage());
                    }
                })
        ];
    }
}
