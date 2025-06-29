<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $recordTitleAttribute = 'sku';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('sku')
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\TextInput::make('price')
                ->required()
                ->numeric()
                ->prefix('$'),

            Forms\Components\TextInput::make('compare_price')
                ->numeric()
                ->prefix('$'),

            Forms\Components\TextInput::make('inventory_quantity')
                ->required()
                ->numeric()
                ->default(0),

            Forms\Components\FileUpload::make('image')
                ->image()
                ->directory('variants'),

            Forms\Components\Toggle::make('is_active')
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('sku')
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD'),

                Tables\Columns\TextColumn::make('inventory_quantity')
                    ->label('Stock')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
