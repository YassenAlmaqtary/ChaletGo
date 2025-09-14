<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChaletImageResource\Pages;
use App\Filament\Resources\ChaletImageResource\RelationManagers;
use App\Models\ChaletImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChaletImageResource extends Resource
{
    protected static ?string $model = ChaletImage::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'صور الشاليات';

    protected static ?string $modelLabel = 'صورة';

    protected static ?string $pluralModelLabel = 'صور الشاليات';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'إدارة الشاليات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('chalet_id')
                    ->relationship('chalet', 'name')
                    ->required(),
                Forms\Components\FileUpload::make('image_path')
                    ->image()
                    ->required(),
                Forms\Components\TextInput::make('alt_text')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Toggle::make('is_primary')
                    ->required(),
                Forms\Components\TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('chalet.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image_path'),
                Tables\Columns\TextColumn::make('alt_text')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_primary')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListChaletImages::route('/'),
            'create' => Pages\CreateChaletImage::route('/create'),
            'edit' => Pages\EditChaletImage::route('/{record}/edit'),
        ];
    }
}
