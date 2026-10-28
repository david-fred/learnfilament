<?php

namespace App\Filament\Admin\Resources\Products\Pages;

use App\Filament\Admin\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use App\Models\Product;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
            ->before(function (DeleteAction $action, Product $record) {
                if ($record->orders()->exists()) {
                    Notification::make()
                        ->danger()
                        ->title('Cannot delete product')
                        ->body('This product has existing orders.')
                        ->send();

                    $action->halt();
                }
            }),
        ];
    }

   /*  protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['price'] = $data['price'] / 100;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['price'] = $data['price'] * 100;

        return $data;
    }*/
}
