<?php

namespace App\Filament\Owner\Resources;

use App\Filament\Owner\Resources\ChaletResource\Pages;
use App\Filament\Owner\Resources\ChaletResource\RelationManagers\ImagesRelationManager;
use App\Models\Chalet;
use App\Models\Amenity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ChaletResource extends Resource
{
    protected static ?string $model = Chalet::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'شاليهاتي';

    protected static ?string $modelLabel = 'شاليه';

    protected static ?string $pluralModelLabel = 'شاليهاتي';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'إدارة الشاليهات';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('owner_id', Auth::id());
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->isOwner() ?? false;
    }

    public static function canView($record): bool
    {
        return $record->owner_id === Auth::id();
    }

    public static function canEdit($record): bool
    {
        return $record->owner_id === Auth::id();
    }

    public static function canDelete($record): bool
    {
        return $record->owner_id === Auth::id();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات أساسية')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الشاليه')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true),
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
                            ->relationship('amenities', 'name', fn (Builder $query) => 
                                $query->where('owner_id', Auth::id())->where('is_active', true)
                            )
                            ->columns(3)
                            ->gridDirection('row')
                            ->helperText('يمكنك إضافة مرافق جديدة من قسم "مرافقي" في القائمة الجانبية'),
                    ]),

                Forms\Components\Section::make('الإعدادات')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true)
                            ->helperText('عند إلغاء التفعيل، لن يظهر الشاليه في نتائج البحث'),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('مميز')
                            ->default(false)
                            ->helperText('الشاليهات المميزة تظهر في المقدمة'),
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
                        return $primaryImage ? asset('storage/' . $primaryImage->image_path) : null;
                    })
                    ->height(60)
                    ->width(80),
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الشاليه')
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
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('تفعيل المحدد')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('إلغاء تفعيل المحدد')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ImagesRelationManager::class,
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





