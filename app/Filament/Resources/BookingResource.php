<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'الحجوزات';

    protected static ?string $modelLabel = 'حجز';

    protected static ?string $pluralModelLabel = 'الحجوزات';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'إدارة الحجوزات';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('chalet_id')
                    ->label('الشاليه')
                    ->relationship('chalet', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('customer_id')
                    ->label('العميل')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('booking_number')
                    ->label('رقم الحجز')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('check_in_date')
                    ->label('تاريخ الوصول')
                    ->required(),
                Forms\Components\DatePicker::make('check_out_date')
                    ->label('تاريخ المغادرة')
                    ->required(),
                Forms\Components\TextInput::make('guests_count')
                    ->label('عدد الضيوف')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('total_amount')
                    ->label('المبلغ الإجمالي')
                    ->required()
                    ->numeric()
                    ->prefix('ريال'),
                Forms\Components\TextInput::make('discount_amount')
                    ->label('مبلغ الخصم')
                    ->required()
                    ->numeric()
                    ->default(0.00)
                    ->prefix('ريال'),
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'في الانتظار',
                        'confirmed' => 'مؤكد',
                        'cancelled' => 'ملغي',
                        'completed' => 'مكتمل',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('special_requests')
                    ->label('طلبات خاصة')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('booking_details')
                    ->label('تفاصيل الحجز')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking_number')
                    ->label('رقم الحجز')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('chalet.name')
                    ->label('الشاليه')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('العميل')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('check_in_date')
                    ->label('تاريخ الوصول')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out_date')
                    ->label('تاريخ المغادرة')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('guests_count')
                    ->label('عدد الضيوف')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('المبلغ الإجمالي')
                    ->money('YR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_paid')
                    ->label('مدفوع')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->isPaid())
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'في الانتظار',
                        'confirmed' => 'مؤكد',
                        'cancelled' => 'ملغي',
                        'completed' => 'مكتمل',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'في الانتظار',
                        'confirmed' => 'مؤكد',
                        'cancelled' => 'ملغي',
                        'completed' => 'مكتمل',
                    ]),
                Tables\Filters\SelectFilter::make('chalet')
                    ->label('الشاليه')
                    ->relationship('chalet', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('check_in_date')
                    ->label('تاريخ الوصول')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('من'),
                        Forms\Components\DatePicker::make('until')
                            ->label('إلى'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('check_in_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('check_in_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\Action::make('confirm')
                    ->label('تأكيد')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد الحجز')
                    ->modalDescription('هل أنت متأكد من تأكيد هذا الحجز؟')
                    ->modalSubmitActionLabel('تأكيد')
                    ->action(fn ($record) => $record->update(['status' => 'confirmed']))
                    ->visible(fn ($record) => $record->status === 'pending'),
                Tables\Actions\Action::make('cancel')
                    ->label('إلغاء')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('إلغاء الحجز')
                    ->modalDescription('هل أنت متأكد من إلغاء هذا الحجز؟')
                    ->modalSubmitActionLabel('إلغاء الحجز')
                    ->action(fn ($record) => $record->update(['status' => 'cancelled']))
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'confirmed'])),
                Tables\Actions\Action::make('add_payment')
                    ->label('إضافة دفعة')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->url(fn ($record) => route('filament.admin.resources.payments.create', ['booking_id' => $record->id]))
                    ->visible(fn ($record) => $record->status === 'confirmed' && !$record->isPaid()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'الحجوزات';
    }

    public static function getPluralModelLabel(): string
    {
        return 'الحجوزات';
    }

    public static function getModelLabel(): string
    {
        return 'حجز';
    }
}
