<?php

namespace App\Filament\Admin\Resources\Tags\Pages;

use App\Filament\Admin\Resources\Tags\TagResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;
use App\Actions\CreateTagAction;
use Illuminate\Support\Facades\Log;

class ManageTags extends ManageRecords
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->successNotification(null)
            ->action(function (CreateTagAction $createProduct, array $data) {
                    try {
                        $createProduct->handle($data);
                        Notification::make()
                            ->title('Tag created successfully')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Error creating tag')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                        Log::error($e->getMessage());
                    }
                }),
        ];
    }
}
