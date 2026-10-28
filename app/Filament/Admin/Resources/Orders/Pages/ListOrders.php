<?php

namespace App\Filament\Admin\Resources\Orders\Pages;

use App\Filament\Admin\Resources\Orders\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Actions\CreateOrderAction;
use Filament\Notifications\Notification;
use Throwable;
use Illuminate\Support\Facades\Log;


class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->action(function (CreateOrderAction $createOrder, array $data) {
                    try {
                        $createOrder->handle($data);
                        Notification::make()
                            ->title('Order created successfully')
                            ->success()
                            ->send();
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('Error creating order')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                        Log::error($e->getMessage());
                    }
                }),
        ];
    }
}
