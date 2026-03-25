<?php

namespace App\Filament\Owner\Resources;

use App\Filament\Owner\Resources\AmenityResource\Pages;
use App\Models\Amenity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AmenityResource extends Resource
{
    protected static ?string $model = Amenity::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'مرافقي';

    protected static ?string $modelLabel = 'مرفق';

    protected static ?string $pluralModelLabel = 'مرافقي';

    protected static ?int $navigationSort = 4;

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
                Forms\Components\Section::make('معلومات المرفق')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم المرفق')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('مثال: مسبح، جاكوزي، حديقة...'),
                        Forms\Components\TextInput::make('icon')
                            ->label('أيقونة')
                            ->maxLength(255)
                            ->placeholder('heroicon-o-swimming-pool')
                            ->helperText('اسم أيقونة Heroicons (اختياري)'),
                        Forms\Components\Select::make('category')
                            ->label('الفئة')
                            ->options([
                                'general' => 'عام',
                                'entertainment' => 'ترفيه',
                                'safety' => 'أمان',
                                'comfort' => 'راحة',
                                'outdoor' => 'خارجي',
                            ])
                            ->required()
                            ->default('general')
                            ->native(false),
                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true)
                            ->helperText('عند إلغاء التفعيل، لن يظهر المرفق في قائمة المرافق المتاحة'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المرفق')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('icon')
                    ->label('الأيقونة')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('category')
                    ->label('الفئة')
                    ->colors([
                        'gray' => 'general',
                        'success' => 'entertainment',
                        'warning' => 'safety',
                        'info' => 'comfort',
                        'primary' => 'outdoor',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'general' => 'عام',
                        'entertainment' => 'ترفيه',
                        'safety' => 'أمان',
                        'comfort' => 'راحة',
                        'outdoor' => 'خارجي',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('chalets_count')
                    ->label('عدد الشاليهات')
                    ->counts('chalets')
                    ->sortable()
                    ->description('عدد الشاليهات التي تستخدم هذا المرفق'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('الفئة')
                    ->options([
                        'general' => 'عام',
                        'entertainment' => 'ترفيه',
                        'safety' => 'أمان',
                        'comfort' => 'راحة',
                        'outdoor' => 'خارجي',
                    ])
                    ->native(false),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->requiresConfirmation()
                    ->modalHeading('حذف المرفق')
                    ->modalDescription('هل أنت متأكد من حذف هذا المرفق؟ سيتم إزالته من جميع الشاليهات المرتبطة به.')
                    ->modalSubmitActionLabel('حذف'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAmenities::route('/'),
            'create' => Pages\CreateAmenity::route('/create'),
            'edit' => Pages\EditAmenity::route('/{record}/edit'),
        ];
    }
}

