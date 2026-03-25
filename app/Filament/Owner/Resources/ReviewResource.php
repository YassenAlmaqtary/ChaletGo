<?php

namespace App\Filament\Owner\Resources;

use App\Filament\Owner\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?string $navigationLabel = 'تقييمات شاليهاتي';

    protected static ?string $modelLabel = 'تقييم';

    protected static ?string $pluralModelLabel = 'تقييمات شاليهاتي';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationGroup = 'إدارة التفاعل';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('chalet', function (Builder $query) {
                $query->where('owner_id', Auth::id());
            })
            ->with(['chalet', 'customer', 'booking']);
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->isOwner() ?? false;
    }

    public static function canView($record): bool
    {
        return $record->chalet->owner_id === Auth::id();
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::whereHas('chalet', function (Builder $query) {
            $query->where('owner_id', Auth::id());
        })->where('is_approved', false)->count();

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
                        Forms\Components\Placeholder::make('rating')
                            ->label('التقييم')
                            ->content(fn (?Review $record) => $record ? str_repeat('⭐', $record->rating) . " ({$record->rating}/5)" : 'غير محدد'),
                        Forms\Components\Placeholder::make('is_approved')
                            ->label('الحالة')
                            ->content(fn (?Review $record) => $record?->is_approved ? '✅ معتمد' : '⏳ قيد المراجعة'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('التعليق')
                    ->schema([
                        Forms\Components\Placeholder::make('comment')
                            ->label('نص التقييم')
                            ->content(fn (?Review $record) => $record?->comment ?? 'لا يوجد تعليق')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('معلومات إضافية')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('تاريخ الإضافة')
                            ->content(fn (?Review $record) => $record?->created_at?->format('d/m/Y H:i') ?? 'غير محدد'),
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
                    ->searchable()
                    ->weight('bold'),
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
                    ->formatStateUsing(fn (int $state): string => str_repeat('⭐', $state) . " ({$state}/5)"),
                Tables\Columns\TextColumn::make('comment')
                    ->label('التعليق')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_approved')
                    ->label('معتمد')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label('الحالة')
                    ->trueLabel('معتمد')
                    ->falseLabel('قيد المراجعة')
                    ->native(false),
                Tables\Filters\SelectFilter::make('rating')
                    ->label('التقييم')
                    ->options([
                        5 => '5 نجوم',
                        4 => '4 نجوم',
                        3 => '3 نجوم',
                        2 => 'نجمتان',
                        1 => 'نجمة واحدة',
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
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'view' => Pages\ViewReview::route('/{record}'),
        ];
    }
}

