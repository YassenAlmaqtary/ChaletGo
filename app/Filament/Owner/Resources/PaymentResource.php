<?php

namespace App\Filament\Owner\Resources;

use App\Filament\Owner\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'مدفوعاتي';

    protected static ?string $modelLabel = 'مدفوعة';

    protected static ?string $pluralModelLabel = 'مدفوعاتي';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'إدارة الحجوزات';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('booking.chalet', function (Builder $query) {
                $query->where('owner_id', Auth::id());
            })
            ->with(['booking.chalet', 'booking.customer']);
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->isOwner() ?? false;
    }

    public static function canView($record): bool
    {
        return $record->booking->chalet->owner_id === Auth::id();
    }

    public static function canEdit($record): bool
    {
        return $record->booking->chalet->owner_id === Auth::id();
    }

    public static function canDelete($record): bool
    {
        return false; // المالك لا يمكنه حذف المدفوعات
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات المدفوعة')
                    ->schema([
                        Forms\Components\Select::make('booking_id')
                            ->label('الحجز')
                            ->relationship(
                                'booking',
                                'booking_number',
                                fn (Builder $query) => $query->whereHas(
                                    'chalet',
                                    fn (Builder $q) => $q->where('owner_id', Auth::id())
                                )
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set): void {
                                if (! $state) {
                                    return;
                                }
                                $booking = Booking::query()->find((int) $state);
                                if ($booking) {
                                    $set('amount', $booking->final_amount);
                                }
                            })
                            ->disabled(fn () => filled(request()->query('booking_id')))
                            ->dehydrated(true)
                            ->visibleOn('create')
                            ->columnSpanFull(),
                        Forms\Components\Placeholder::make('booking_number')
                            ->label('رقم الحجز')
                            ->content(fn (?Payment $record) => $record?->booking?->booking_number ?? 'غير محدد')
                            ->visibleOn('edit'),
                        Forms\Components\Placeholder::make('chalet_name')
                            ->label('الشاليه')
                            ->content(fn (?Payment $record) => $record?->booking?->chalet?->name ?? 'غير محدد')
                            ->visibleOn('edit'),
                        Forms\Components\Placeholder::make('customer_name')
                            ->label('العميل')
                            ->content(fn (?Payment $record) => $record?->booking?->customer?->name ?? 'غير محدد')
                            ->visibleOn('edit'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('تفاصيل الدفع')
                    ->schema([
                        Forms\Components\Select::make('payment_method')
                            ->label('طريقة الدفع')
                            ->options([
                                'credit_card' => 'بطاقة ائتمانية',
                                'bank_transfer' => 'تحويل بنكي',
                                'cash' => 'نقدي',
                                'digital_wallet' => 'محفظة رقمية',
                            ])
                            ->default('cash')
                            ->required()
                            ->native(false)
                            ->disabled(fn (?Payment $record) => $record !== null),
                        Forms\Components\TextInput::make('amount')
                            ->label('المبلغ')
                            ->required()
                            ->numeric()
                            ->prefix('ريال')
                            ->disabled(fn (?Payment $record) => $record !== null),
                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'pending' => 'في الانتظار',
                                'completed' => 'مكتمل',
                                'failed' => 'فاشل',
                                'refunded' => 'مسترد',
                            ])
                            ->default('completed')
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('transaction_id')
                            ->label('رقم المعاملة')
                            ->maxLength(255)
                            ->placeholder('يُولَّد تلقائياً إن تُرك فارغاً')
                            ->disabled(fn (?Payment $record) => $record !== null),
                        Forms\Components\Textarea::make('payment_details')
                            ->label('تفاصيل الدفع')
                            ->columnSpanFull()
                            ->disabled(fn (?Payment $record) => $record !== null),
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('تاريخ الدفع')
                            ->default(now())
                            ->disabled(fn (?Payment $record) => $record !== null),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking.booking_number')
                    ->label('رقم الحجز')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('booking.chalet.name')
                    ->label('الشاليه')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('booking.customer.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'credit_card' => 'بطاقة ائتمانية',
                        'bank_transfer' => 'تحويل بنكي',
                        'cash' => 'نقدي',
                        'digital_wallet' => 'محفظة رقمية',
                        default => $state,
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('SAR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'في الانتظار',
                        'completed' => 'مكتمل',
                        'failed' => 'فاشل',
                        'refunded' => 'مسترد',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('رقم المعاملة')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('تاريخ الدفع')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
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
                        'completed' => 'مكتمل',
                        'failed' => 'فاشل',
                        'refunded' => 'مسترد',
                    ])
                    ->native(false),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'credit_card' => 'بطاقة ائتمانية',
                        'bank_transfer' => 'تحويل بنكي',
                        'cash' => 'نقدي',
                        'digital_wallet' => 'محفظة رقمية',
                    ])
                    ->native(false),
                Tables\Filters\SelectFilter::make('booking.chalet_id')
                    ->label('الشاليه')
                    ->relationship('booking.chalet', 'name', fn (Builder $query) => 
                        $query->where('owner_id', Auth::id())
                    )
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('paid_at')
                    ->label('تاريخ الدفع')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('paid_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('paid_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل')
                    ->visible(fn (Payment $record) => $record->status !== 'completed' && $record->status !== 'refunded'),
                Tables\Actions\Action::make('refund')
                    ->label('استرداد')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('استرداد المدفوعة')
                    ->modalDescription('هل أنت متأكد من استرداد هذه المدفوعة؟')
                    ->modalSubmitActionLabel('استرداد')
                    ->visible(fn (Payment $record) => $record->status === 'completed')
                    ->action(function (Payment $record): void {
                        $record->update(['status' => 'refunded']);
                        Notification::make()
                            ->title('تم استرداد المدفوعة')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'view' => Pages\ViewPayment::route('/{record}'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}





