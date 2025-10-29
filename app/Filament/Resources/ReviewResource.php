<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?string $navigationLabel = 'التقييمات';

    protected static ?string $modelLabel = 'تقييم';

    protected static ?string $pluralModelLabel = 'التقييمات';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationGroup = 'إدارة التفاعل';

    public static function canViewAny(): bool
    {
        return static::currentUserIsAdmin();
    }

    public static function canView($record): bool
    {
        return static::currentUserIsAdmin();
    }

    public static function canEdit($record): bool
    {
        return static::currentUserIsAdmin();
    }

    public static function canDelete($record): bool
    {
        return static::currentUserIsAdmin();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    protected static function currentUserIsAdmin(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('is_approved', false)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات عامة')
                    ->schema([
                        Forms\Components\Placeholder::make('chalet_name')
                            ->label('الشاليه')
                            ->content(fn (?Review $record) => $record?->chalet?->name ?? 'غير محدد'),
                        Forms\Components\Placeholder::make('customer_name')
                            ->label('العميل')
                            ->content(fn (?Review $record) => $record?->customer?->name ?? 'غير محدد'),
                        Forms\Components\Placeholder::make('booking_number')
                            ->label('رقم الحجز')
                            ->content(fn (?Review $record) => $record?->booking?->booking_number ?? 'غير متوفر'),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('التقييم')
                    ->schema([
                        Forms\Components\Select::make('rating')
                            ->label('التقييم')
                            ->options([
                                1 => '1 نجمة',
                                2 => '2 نجمتان',
                                3 => '3 نجوم',
                                4 => '4 نجوم',
                                5 => '5 نجوم',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Toggle::make('is_approved')
                            ->label('معتمد')
                            ->helperText('فعّل هذا الخيار لإظهار التقييم للعملاء'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('التعليق')
                    ->schema([
                        Forms\Components\Textarea::make('comment')
                            ->label('نص التقييم')
                            ->rows(6)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('chalet.name')
                    ->label('الشاليه')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('العميل')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('booking.booking_number')
                    ->label('رقم الحجز')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('rating')
                    ->label('التقييم')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 2 => 'danger',
                        $state === 3 => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(fn (int $state): string => $state . ' / 5'),
                Tables\Columns\TextColumn::make('comment')
                    ->label('التعليق')
                    ->limit(60)
                    ->toggleable(),
                Tables\Columns\ToggleColumn::make('is_approved')
                    ->label('معتمد')
                    ->onColor('success')
                    ->offColor('warning')
                    ->onIcon('heroicon-o-check-circle')
                    ->offIcon('heroicon-o-clock')
                    ->disabled(fn () => !static::currentUserIsAdmin()),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_approved')
                    ->label('الحالة')
                    ->trueLabel('معتمد')
                    ->falseLabel('قيد المراجعة')
                    ->native(false),
                SelectFilter::make('rating')
                    ->label('التقييم')
                    ->options([
                        5 => '5 نجوم',
                        4 => '4 نجوم',
                        3 => '3 نجوم',
                        2 => 'نجمتان',
                        1 => 'نجمة واحدة',
                    ]),
                Filter::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('من'),
                        Forms\Components\DatePicker::make('until')
                            ->label('إلى'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $innerQuery, $date): Builder => $innerQuery->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $innerQuery, $date): Builder => $innerQuery->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل')
                    ->visible(fn () => static::currentUserIsAdmin()),
                Tables\Actions\Action::make('approve')
                    ->label('اعتماد')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('اعتماد التقييم')
                    ->modalDescription('هل تريد اعتماد هذا التقييم ليظهر للعملاء؟')
                    ->modalSubmitActionLabel('اعتماد')
                    ->action(fn (Review $record) => $record->update(['is_approved' => true]))
                    ->visible(fn (Review $record) => static::currentUserIsAdmin() && !$record->is_approved),
                Tables\Actions\Action::make('reject')
                    ->label('إلغاء الاعتماد')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('إلغاء اعتماد التقييم')
                    ->modalDescription('سيتم إخفاء التقييم من الواجهة العامة.')
                    ->modalSubmitActionLabel('إلغاء الاعتماد')
                    ->action(fn (Review $record) => $record->update(['is_approved' => false]))
                    ->visible(fn (Review $record) => static::currentUserIsAdmin() && $record->is_approved),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->visible(fn () => static::currentUserIsAdmin()),
                    Tables\Actions\BulkAction::make('bulk_approve')
                        ->label('اعتماد المحدد')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_approved' => true]))
                        ->visible(fn () => static::currentUserIsAdmin()),
                    Tables\Actions\BulkAction::make('bulk_reject')
                        ->label('إلغاء اعتماد المحدد')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_approved' => false]))
                        ->visible(fn () => static::currentUserIsAdmin()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'view' => Pages\ViewReview::route('/{record}'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
