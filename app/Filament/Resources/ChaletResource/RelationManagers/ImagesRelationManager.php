<?php

namespace App\Filament\Resources\ChaletResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'صور الشاليه';

    protected static ?string $modelLabel = 'صورة';

    protected static ?string $pluralModelLabel = 'الصور';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('image_path')
                    ->label('الصورة')
                    ->image()
                    ->directory(fn () => 'chalets/' . $this->getOwnerRecord()->id)
                    ->visibility('public')
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth('1920')
                    ->imageResizeTargetHeight('1080')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(5120)
                    ->required(),
                Forms\Components\TextInput::make('alt_text')
                    ->label('النص البديل')
                    ->maxLength(255)
                    ->placeholder('وصف الصورة'),
                Forms\Components\Toggle::make('is_primary')
                    ->label('صورة رئيسية')
                    ->afterStateUpdated(function ($state, $record) {
                        if ($state && $record) {
                            // Remove primary from other images
                            $record->chalet->images()
                                ->where('id', '!=', $record->id)
                                ->update(['is_primary' => false]);
                        }
                    }),
                Forms\Components\TextInput::make('sort_order')
                    ->label('ترتيب العرض')
                    ->numeric()
                    ->default(fn () => $this->getOwnerRecord()->images()->max('sort_order') + 1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('alt_text')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('الصورة')
                    ->height(80)
                    ->width(120),
                Tables\Columns\TextColumn::make('alt_text')
                    ->label('النص البديل')
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_primary')
                    ->label('رئيسية')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_primary')
                    ->label('الصورة الرئيسية')
                    ->trueLabel('رئيسية')
                    ->falseLabel('عادية'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة صورة')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['sort_order'] = $data['sort_order'] ??
                            ($this->getOwnerRecord()->images()->max('sort_order') + 1);
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\Action::make('set_primary')
                    ->label('جعل رئيسية')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->action(function ($record) {
                        // Remove primary from all images
                        $record->chalet->images()->update(['is_primary' => false]);
                        // Set this as primary
                        $record->update(['is_primary' => true]);
                    })
                    ->visible(fn ($record) => !$record->is_primary),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->before(function ($record) {
                        // Delete file from storage
                        if ($record->image_path && Storage::disk('public')->exists($record->image_path)) {
                            Storage::disk('public')->delete($record->image_path);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->image_path && Storage::disk('public')->exists($record->image_path)) {
                                    Storage::disk('public')->delete($record->image_path);
                                }
                            }
                        }),
                ]),
            ]);
    }
}
