<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChaletResource\Pages;
use App\Filament\Resources\ChaletResource\RelationManagers;
use App\Models\Chalet;
use App\Models\User;
use App\Models\Amenity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;

class ChaletResource extends Resource
{
    protected static ?string $model = Chalet::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'الشاليات';

    protected static ?string $modelLabel = 'شاليه';

    protected static ?string $pluralModelLabel = 'الشاليات';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'إدارة الشاليات';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات أساسية')
                    ->schema([
                        Forms\Components\Select::make('owner_id')
                            ->label('المالك')
                            ->relationship('owner', 'name', fn (Builder $query) => $query->where('user_type', 'owner'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الشاليه')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $context, $state, callable $set) =>
                                $context === 'create' ? $set('slug', \Str::slug($state)) : null
                            ),
                        Forms\Components\TextInput::make('slug')
                            ->label('الرابط')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->rules(['alpha_dash']),
                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('location')
                            ->label('الموقع')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('الإحداثيات')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label('خط العرض')
                            ->numeric()
                            ->step(0.00000001)
                            ->placeholder('24.7136'),
                        Forms\Components\TextInput::make('longitude')
                            ->label('خط الطول')
                            ->numeric()
                            ->step(0.00000001)
                            ->placeholder('46.6753'),
                    ])->columns(2),

                Forms\Components\Section::make('التفاصيل والأسعار')
                    ->schema([
                        Forms\Components\TextInput::make('price_per_night')
                            ->label('السعر لليلة الواحدة')
                            ->required()
                            ->numeric()
                            ->prefix('ريال')
                            ->step(0.01),
                        Forms\Components\TextInput::make('max_guests')
                            ->label('أقصى عدد ضيوف')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        Forms\Components\TextInput::make('bedrooms')
                            ->label('عدد غرف النوم')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        Forms\Components\TextInput::make('bathrooms')
                            ->label('عدد دورات المياه')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                    ])->columns(2),

                Forms\Components\Section::make('المرافق')
                    ->schema([
                        Forms\Components\CheckboxList::make('amenities')
                            ->label('المرافق المتاحة')
                            ->relationship('amenities', 'name')
                            ->columns(3)
                            ->gridDirection('row'),
                    ]),

                Forms\Components\Section::make('الصور')
                    ->schema([
                        Forms\Components\Placeholder::make('images_note')
                            ->label('')
                            ->content('بعد حفظ الشاليه، يمكنك إضافة الصور من خلال تبويب "صور الشاليه" أدناه.')
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record === null),

                        FileUpload::make('temp_images')
                            ->label('صور الشاليه')
                            ->image()
                            ->multiple()
                            ->directory('chalets/temp')
                            ->visibility('public')
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080')
                            ->maxFiles(10)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->helperText('يمكنك رفع حتى 10 صور بحد أقصى 5 ميجابايت لكل صورة')
                            ->columnSpanFull()
                            ->reorderable()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record === null),
                    ]),

                Forms\Components\Section::make('الإعدادات')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('مميز')
                            ->default(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('primary_image_url')
                    ->label('الصورة')
                    ->getStateUsing(function ($record) {
                        $primaryImage = $record->images()->where('is_primary', true)->first();
                        return $primaryImage ? asset('storage/' . $primaryImage->image_path): null;
                    })
                    ->height(60)
                    ->width(80),
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الشاليه')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('owner.name')
                    ->label('المالك')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('الموقع')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_per_night')
                    ->label('السعر/ليلة')
                    ->money('SAR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_guests')
                    ->label('أقصى ضيوف')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bedrooms')
                    ->label('غرف النوم')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bathrooms')
                    ->label('دورات المياه')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('مميز')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('bookings_count')
                    ->label('عدد الحجوزات')
                    ->counts('bookings')
                    ->sortable(),
                Tables\Columns\TextColumn::make('average_rating')
                    ->label('التقييم')
                    ->numeric(decimalPlaces: 1)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('owner_id')
                    ->label('المالك')
                    ->relationship('owner', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->native(false),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('مميز')
                    ->trueLabel('مميز')
                    ->falseLabel('عادي')
                    ->native(false),
                Tables\Filters\Filter::make('price_range')
                    ->form([
                        Forms\Components\TextInput::make('price_from')
                            ->label('السعر من')
                            ->numeric()
                            ->prefix('ريال'),
                        Forms\Components\TextInput::make('price_to')
                            ->label('السعر إلى')
                            ->numeric()
                            ->prefix('ريال'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['price_from'],
                                fn (Builder $query, $price): Builder => $query->where('price_per_night', '>=', $price),
                            )
                            ->when(
                                $data['price_to'],
                                fn (Builder $query, $price): Builder => $query->where('price_per_night', '<=', $price),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('تفعيل المحدد')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('إلغاء تفعيل المحدد')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChalets::route('/'),
            'create' => Pages\CreateChalet::route('/create'),
            'edit' => Pages\EditChalet::route('/{record}/edit'),
        ];
    }
}
