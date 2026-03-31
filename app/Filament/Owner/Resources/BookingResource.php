<?php

namespace App\Filament\Owner\Resources;

use App\Filament\Owner\Resources\BookingResource\Pages;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'حجوزاتي';

    protected static ?string $modelLabel = 'حجز';

    protected static ?string $pluralModelLabel = 'حجوزاتي';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'إدارة الحجوزات';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('chalet', function (Builder $query) {
                $query->where('owner_id', Auth::id());
            });
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الحجز')
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
                        Forms\Components\Select::make('customer_id')
                            ->label('العميل')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($record) => $record !== null),
                        Forms\Components\TextInput::make('booking_number')
                            ->label('رقم الحجز')
                            ->required()
                            ->maxLength(255)
                            ->disabled(),
                        Forms\Components\DatePicker::make('check_in_date')
                            ->label('تاريخ الوصول')
                            ->required()
                            ->disabled(fn ($record) => $record !== null),
                        Forms\Components\DatePicker::make('check_out_date')
                            ->label('تاريخ المغادرة')
                            ->required()
                            ->disabled(fn ($record) => $record !== null),
                        Forms\Components\TextInput::make('guests_count')
                            ->label('عدد الضيوف')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->disabled(fn ($record) => $record !== null),
                    ])->columns(2),

                Forms\Components\Section::make('المعلومات المالية')
                    ->schema([
                        Forms\Components\TextInput::make('total_amount')
                            ->label('المبلغ الإجمالي')
                            ->required()
                            ->numeric()
                            ->prefix('ريال')
                            ->disabled(),
                        Forms\Components\TextInput::make('discount_amount')
                            ->label('مبلغ الخصم')
                            ->required()
                            ->numeric()
                            ->default(0.00)
                            ->prefix('ريال')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('حالة الحجز')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'pending' => 'في الانتظار',
                                'confirmed' => 'مؤكد',
                                'cancelled' => 'ملغي',
                                'completed' => 'مكتمل',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Textarea::make('special_requests')
                            ->label('طلبات خاصة')
                            ->columnSpanFull()
                            ->disabled(),
                    ]),
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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),
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
                    ->money('SAR')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'danger' => 'cancelled',
                        'info' => 'completed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'في الانتظار',
                        'confirmed' => 'مؤكد',
                        'cancelled' => 'ملغي',
                        'completed' => 'مكتمل',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الحجز')
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
                    ])
                    ->native(false),
                Tables\Filters\SelectFilter::make('chalet_id')
                    ->label('الشاليه')
                    ->relationship('chalet', 'name', fn (Builder $query) => 
                        $query->where('owner_id', Auth::id())
                    )
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\Action::make('confirm')
                    ->label('تأكيد')
                    ->tooltip('تأكيد الحجز')
                    ->icon('heroicon-o-check-circle')
                    ->iconButton()
                    ->color('success')
                    ->visible(fn (Booking $record): bool => $record->status === Booking::STATUS_PENDING)
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد الحجز')
                    ->modalDescription('هل تريد تأكيد هذا الحجز؟')
                    ->modalSubmitActionLabel('تأكيد')
                    ->action(function (Booking $record): void {
                        if ($record->status !== Booking::STATUS_PENDING) {
                            return;
                        }
                        $record->update(['status' => Booking::STATUS_CONFIRMED]);
                        Notification::make()
                            ->title('تم تأكيد الحجز')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('cancelBooking')
                    ->label('إلغاء')
                    ->tooltip('إلغاء الحجز')
                    ->icon('heroicon-o-x-circle')
                    ->iconButton()
                    ->color('danger')
                    ->visible(fn (Booking $record): bool => $record->canBeCancelled())
                    ->form([
                        Forms\Components\Textarea::make('cancellation_reason')
                            ->label('سبب الإلغاء')
                            ->required()
                            ->maxLength(500)
                            ->rows(3),
                    ])
                    ->modalHeading('إلغاء الحجز')
                    ->modalSubmitActionLabel('إلغاء الحجز')
                    ->action(function (array $data, Booking $record): void {
                        if (! $record->canBeCancelled()) {
                            return;
                        }
                        $details = $record->booking_details ?? [];
                        $details['cancellation_reason'] = $data['cancellation_reason'];
                        $details['cancelled_by'] = Auth::user()?->name ?? 'المالك';
                        $details['cancelled_at'] = now()->format('Y-m-d H:i:s');
                        $record->update([
                            'status' => Booking::STATUS_CANCELLED,
                            'booking_details' => $details,
                        ]);
                        Notification::make()
                            ->title('تم إلغاء الحجز')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('add_payment')
                    ->label('إضافة دفعة')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->url(fn (Booking $record) => route('filament.owner.resources.payments.create', ['booking_id' => $record->id]))
                    ->visible(fn (Booking $record): bool => $record->status === Booking::STATUS_CONFIRMED && ! $record->isPaid()),
                Tables\Actions\EditAction::make()
                    ->label('تعديل')
                    ->visible(fn ($record) => $record->status !== 'cancelled' && $record->status !== 'completed'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'view' => Pages\ViewBooking::route('/{record}'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}





