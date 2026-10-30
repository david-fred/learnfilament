<?php

namespace App\Filament\Admin\Resources\Orders\Pages;

use App\Actions\DeleteOrderAction;
use App\Filament\Admin\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->successRedirectUrl(OrderResource::getUrl())
                ->action(fn(Order $record, DeleteOrderAction $deleteOrder) => $deleteOrder->handle($record)),
        ];
    }
}
