<?php

namespace App\Filament\Admin\Resources\Categories\Pages;

use App\Filament\Admin\Resources\Categories\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use App\Actions\CreateCategoryAction;
use Illuminate\Support\Facades\Log;

class ManageCategories extends ManageRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->successNotification(null)
                ->action(function (CreateCategoryAction $createProduct, array $data) {
                    try {
                        $createProduct->handle($data);
                        Notification::make()
                            ->title('Category created successfully')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Error creating Category')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                        Log::error($e->getMessage());
                    }
                }),
        ];
    }
}
