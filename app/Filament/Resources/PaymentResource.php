<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('booking_id')
                    ->label('الحجز')
                    ->relationship('booking', 'booking_number')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'credit_card' => 'بطاقة ائتمانية',
                        'bank_transfer' => 'تحويل بنكي',
                        'cash' => 'نقدي',
                        'digital_wallet' => 'محفظة رقمية',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label('المبلغ')
                    ->required()
                    ->numeric()
                    ->prefix('ريال'),
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'في الانتظار',
                        'completed' => 'مكتمل',
                        'failed' => 'فاشل',
                        'refunded' => 'مسترد',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('transaction_id')
                    ->label('رقم المعاملة')
                    ->maxLength(255),
                Forms\Components\Textarea::make('payment_details')
                    ->label('تفاصيل الدفع')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('paid_at')
                    ->label('تاريخ الدفع'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking.booking_number')
                    ->label('رقم الحجز')
                    ->searchable()
                    ->sortable(),
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
                    }),
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
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'credit_card' => 'بطاقة ائتمانية',
                        'bank_transfer' => 'تحويل بنكي',
                        'cash' => 'نقدي',
                        'digital_wallet' => 'محفظة رقمية',
                    ]),
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
                    ->label('تعديل'),
                Tables\Actions\Action::make('refund')
                    ->label('استرداد')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('استرداد المدفوعة')
                    ->modalDescription('هل أنت متأكد من استرداد هذه المدفوعة؟')
                    ->modalSubmitActionLabel('استرداد')
                    ->action(fn ($record) => $record->update(['status' => 'refunded']))
                    ->visible(fn ($record) => $record->status === 'completed'),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'المدفوعات';
    }

    public static function getPluralModelLabel(): string
    {
        return 'المدفوعات';
    }

    public static function getModelLabel(): string
    {
        return 'مدفوعة';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-credit-card';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة الحجوزات';
    }
}
