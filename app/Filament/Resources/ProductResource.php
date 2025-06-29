<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product\Product;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        // Form is now handled in individual pages for better maintainability
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular()
                    ->size(50),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('vendor.business_name')
                    ->label('Store')
                    ->default('Main Store')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sku')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'draft',
                        'success' => 'published',
                        'danger' => 'archived',
                    ]),

                Tables\Columns\TextColumn::make('effective_stock')
                    ->label('Stock')
                    ->getStateUsing(function (Product $record): string {
                        if (! $record->track_inventory) {
                            return 'Not tracked';
                        }

                        $effectiveStock = $record->getEffectiveStockQuantity();

                        if ($record->hasVariants()) {
                            $inStockVariants = $record->variants()->where('inventory_quantity', '>', 0)->count();
                            $totalVariants = $record->variants()->count();

                            return "{$effectiveStock} ({$inStockVariants}/{$totalVariants} variants)";
                        }

                        return (string) $effectiveStock;
                    })
                    ->badge()
                    ->color(function (Product $record): string {
                        if (! $record->track_inventory) {
                            return 'secondary';
                        }

                        return $record->getStockColor();
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        // Sort by effective stock quantity
                        return $query->orderByRaw("
                            CASE
                                WHEN track_inventory = 0 THEN 9999
                                WHEN (SELECT COUNT(*) FROM product_variants WHERE product_id = products.id) > 0
                                THEN (SELECT COALESCE(SUM(inventory_quantity), 0) FROM product_variants WHERE product_id = products.id)
                                ELSE inventory_quantity
                            END {$direction}
                        ");
                    }),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),

                Tables\Filters\SelectFilter::make('vendor')
                    ->relationship('vendor', 'business_name')
                    ->label('Store'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),

                Tables\Filters\Filter::make('out_of_stock')
                    ->query(function (Builder $query): Builder {
                        return $query->where('track_inventory', true)
                            ->where(function ($q) {
                                // Simple products with 0 stock
                                $q->where(function ($subQ) {
                                    $subQ->whereDoesntHave('variants')
                                        ->where('inventory_quantity', '<=', 0);
                                })
                                // OR products with variants but no variant stock
                                    ->orWhere(function ($subQ) {
                                        $subQ->whereHas('variants')
                                            ->whereRaw('(SELECT SUM(inventory_quantity) FROM product_variants WHERE product_id = products.id) <= 0');
                                    });
                            });
                    })
                    ->label('Out of stock'),

                Tables\Filters\Filter::make('low_stock')
                    ->query(function (Builder $query): Builder {
                        return $query->where('track_inventory', true)
                            ->where(function ($q) {
                                // Simple products with low stock
                                $q->where(function ($subQ) {
                                    $subQ->whereDoesntHave('variants')
                                        ->whereRaw('inventory_quantity > 0 AND inventory_quantity <= low_stock_threshold');
                                })
                                // OR products with variants with low total stock
                                    ->orWhere(function ($subQ) {
                                        $subQ->whereHas('variants')
                                            ->whereRaw('(SELECT SUM(inventory_quantity) FROM product_variants WHERE product_id = products.id) > 0')
                                            ->whereRaw('(SELECT SUM(inventory_quantity) FROM product_variants WHERE product_id = products.id) <= low_stock_threshold');
                                    });
                            });
                    })
                    ->label('Low stock'),

                Tables\Filters\TernaryFilter::make('track_inventory')
                    ->label('Inventory tracking'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
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
            RelationManagers\VariantsRelationManager::class,
            RelationManagers\CategoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'view' => Pages\ViewProduct::route('/{record}'),
        ];
    }
}
