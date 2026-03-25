<?php

namespace App\Filament\Owner\Resources\ChaletResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use App\Models\ChaletImage;
use Filament\Forms\Components\FileUpload;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'صور الشاليه';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image_path')
                    ->label('الصورة')
                    ->image()
                    ->directory(fn ($record) => $record ? 'chalets/' . $record->chalet_id : 'chalets/temp')
                    ->visibility('public')
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth('1920')
                    ->imageResizeTargetHeight('1080')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(5120)
                    ->required()
                    ->helperText('يمكنك رفع صورة واحدة بحد أقصى 5 ميجابايت'),
                Forms\Components\TextInput::make('alt_text')
                    ->label('النص البديل')
                    ->maxLength(255)
                    ->helperText('وصف الصورة للمحركات البحثية'),
                Forms\Components\Toggle::make('is_primary')
                    ->label('صورة رئيسية')
                    ->helperText('الصورة الرئيسية تظهر في المقدمة')
                    ->afterStateUpdated(function ($state, $set, $get, $record) {
                        if ($state && $record) {
                            // إلغاء الصورة الرئيسية من جميع الصور الأخرى
                            $record->chalet->images()
                                ->where('id', '!=', $record->id)
                                ->update(['is_primary' => false]);
                        }
                    }),
                Forms\Components\TextInput::make('sort_order')
                    ->label('ترتيب العرض')
                    ->numeric()
                    ->default(0)
                    ->helperText('كلما قل الرقم، كلما ظهرت الصورة أولاً'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('alt_text')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('الصورة')
                    ->getStateUsing(fn ($record) => $record->image_url)
                    ->height(80)
                    ->width(120),
                Tables\Columns\TextColumn::make('alt_text')
                    ->label('النص البديل')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\IconColumn::make('is_primary')
                    ->label('رئيسية')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable()
                    ->numeric(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_primary')
                    ->label('الصورة الرئيسية')
                    ->trueLabel('رئيسية')
                    ->falseLabel('عادية')
                    ->native(false),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة صورة')
                    ->mutateFormDataUsing(function (array $data): array {
                        // تعيين ترتيب العرض تلقائياً
                        if (!isset($data['sort_order'])) {
                            $chalet = $this->getOwnerRecord();
                            $maxOrder = $chalet->images()->max('sort_order') ?? 0;
                            $data['sort_order'] = $maxOrder + 1;
                        }
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\Action::make('set_primary')
                    ->label('تعيين كرئيسية')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('تعيين كصورة رئيسية')
                    ->modalDescription('هل تريد تعيين هذه الصورة كصورة رئيسية للشاليه؟')
                    ->action(function ($record) {
                        // إلغاء الصورة الرئيسية من جميع الصور
                        $record->chalet->images()->update(['is_primary' => false]);
                        // تعيين هذه الصورة كرئيسية
                        $record->update(['is_primary' => true]);
                    })
                    ->visible(fn ($record) => !$record->is_primary),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->before(function ($record) {
                        // حذف الملف من التخزين
                        if ($record->image_path) {
                            Storage::disk('public')->delete($record->image_path);
                            // حذف الصورة المصغرة إن وجدت
                            $thumbnailPath = str_replace(basename($record->image_path), 'thumb_' . basename($record->image_path), $record->image_path);
                            Storage::disk('public')->delete($thumbnailPath);
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('حذف الصورة')
                    ->modalDescription('هل أنت متأكد من حذف هذه الصورة؟ لا يمكن التراجع عن هذا الإجراء.')
                    ->modalSubmitActionLabel('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->image_path) {
                                    Storage::disk('public')->delete($record->image_path);
                                    $thumbnailPath = str_replace(basename($record->image_path), 'thumb_' . basename($record->image_path), $record->image_path);
                                    Storage::disk('public')->delete($thumbnailPath);
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order');
    }
}

