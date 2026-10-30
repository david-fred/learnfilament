<?php

namespace App\Filament\Admin\Resources\Categories;

use App\Filament\Admin\Resources\Categories\Pages\ManageCategories;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Actions\UpdateCategoryAction;
use App\Actions\DeleteCategoryAction;
use App\Actions\BulkDeleteCategoriesAction;
use DomainException;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Product Count')
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
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->successNotification(null)
                        ->action(function (Category $record, array $data, UpdateCategoryAction $action) {
                            $action->handle($record, $data);
                        }),
                    DeleteAction::make()
                        ->successNotification(null)
                        ->action(function (Category $record, DeleteCategoryAction $deleteCategory, Action $action) {
                            try {
                                $deleteCategory->handle($record);
                            } catch (DomainException $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Cannot delete category')
                                    ->body($e->getMessage())
                                    ->send();

                                $action->halt();
                            }
                        }),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (EloquentCollection $records, BulkDeleteCategoriesAction $bulkDelete, Action $action) {
                            try {
                                $bulkDelete->handle($records);
                            } catch (DomainException $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Cannot delete categories')
                                    ->body($e->getMessage())
                                    ->send();

                                $action->halt();
                            }
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCategories::route('/'),
        ];
    }
}
