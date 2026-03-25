<?php

namespace App\Filament\Owner\Resources;

use App\Filament\Owner\Resources\ChaletImageResource\Pages;
use App\Models\ChaletImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\FileUpload;

class ChaletImageResource extends Resource
{
    protected static ?string $model = ChaletImage::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'صور شاليهاتي';

    protected static ?string $modelLabel = 'صورة';

    protected static ?string $pluralModelLabel = 'صور شاليهاتي';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationGroup = 'إدارة الشاليهات';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('chalet', function (Builder $query) {
                $query->where('owner_id', Auth::id());
            })
            ->with(['chalet']);
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->isOwner() ?? false;
    }

    public static function canView($record): bool
    {
        return $record->chalet->owner_id === Auth::id();
    }

    public static function canEdit($record): bool
    {
        return $record->chalet->owner_id === Auth::id();
    }

    public static function canDelete($record): bool
    {
        return $record->chalet->owner_id === Auth::id();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الصورة')
                    ->schema([
                        Forms\Components\Select::make('chalet_id')
                            ->label('الشاليه')
                            ->relationship('chalet', 'name', fn (Builder $query) => 
                                $query->where('owner_id', Auth::id())
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($record) => $record !== null),
                        FileUpload::make('image_path')
                            ->label('الصورة')
                            ->image()
                            ->directory(fn ($record) => $record && $record->chalet_id ? 'chalets/' . $record->chalet_id : 'chalets/temp')
                            ->visibility('public')
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->required(fn ($context) => $context === 'create')
                            ->helperText('يمكنك رفع صورة واحدة بحد أقصى 5 ميجابايت'),
                        Forms\Components\TextInput::make('alt_text')
                            ->label('النص البديل')
                            ->maxLength(255)
                            ->helperText('وصف الصورة للمحركات البحثية'),
                    ])->columns(2),

                Forms\Components\Section::make('إعدادات العرض')
                    ->schema([
                        Forms\Components\Toggle::make('is_primary')
                            ->label('صورة رئيسية')
                            ->helperText('الصورة الرئيسية تظهر في المقدمة')
                            ->afterStateUpdated(function ($state, $set, $get, $record) {
                                if ($state && $record && $record->chalet_id) {
                                    // إلغاء الصورة الرئيسية من جميع الصور الأخرى للشاليه نفسه
                                    ChaletImage::where('chalet_id', $record->chalet_id)
                                        ->where('id', '!=', $record->id)
                                        ->update(['is_primary' => false]);
                                }
                            }),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('ترتيب العرض')
                            ->numeric()
                            ->default(0)
                            ->helperText('كلما قل الرقم، كلما ظهرت الصورة أولاً'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('الصورة')
                    ->getStateUsing(fn ($record) => $record->image_url ?? asset('storage/' . $record->image_path))
                    ->height(100)
                    ->width(150),
                Tables\Columns\TextColumn::make('chalet.name')
                    ->label('الشاليه')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('alt_text')
                    ->label('النص البديل')
                    ->searchable()
                    ->limit(40)
                    ->wrap(),
                Tables\Columns\IconColumn::make('is_primary')
                    ->label('رئيسية')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('chalet_id')
                    ->label('الشاليه')
                    ->relationship('chalet', 'name', fn (Builder $query) => 
                        $query->where('owner_id', Auth::id())
                    )
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_primary')
                    ->label('الصورة الرئيسية')
                    ->trueLabel('رئيسية')
                    ->falseLabel('عادية')
                    ->native(false),
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
                        // إلغاء الصورة الرئيسية من جميع الصور الأخرى للشاليه نفسه
                        ChaletImage::where('chalet_id', $record->chalet_id)
                            ->where('id', '!=', $record->id)
                            ->update(['is_primary' => false]);
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
                    Tables\Actions\BulkAction::make('set_all_primary')
                        ->label('تعيين المحدد كرئيسية')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            // تعيين أول صورة كرئيسية فقط
                            if ($records && $records->isNotEmpty()) {
                                $firstRecord = $records->first();
                                // إلغاء الصورة الرئيسية من جميع الصور الأخرى للشاليه
                                ChaletImage::where('chalet_id', $firstRecord->chalet_id)
                                    ->update(['is_primary' => false]);
                                // تعيين أول صورة كرئيسية
                                $firstRecord->update(['is_primary' => true]);
                            }
                        })
                        ->visible(fn ($records) => $records !== null && $records->count() > 0),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChaletImages::route('/'),
            'create' => Pages\CreateChaletImage::route('/create'),
            'edit' => Pages\EditChaletImage::route('/{record}/edit'),
        ];
    }
}

