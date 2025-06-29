<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product\Product;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Preview on Website')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->modalHeading('Product Preview')
                ->modalDescription('See how this product appears on your website')
                ->modalContent(fn (Product $record): \Illuminate\Contracts\View\View => view('filament.actions.product-preview', [
                    'productUrl' => url("/products/{$record->slug}"),
                    'productName' => $record->name,
                ]))
                ->modalWidth('7xl')
                ->modalFooterActions([
                    Actions\Action::make('open_in_new_tab')
                        ->label('Open in New Tab')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn (Product $record): string => url("/products/{$record->slug}"))
                        ->openUrlInNewTab()
                        ->color('gray'),
                ])
                ->slideOver(),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            // Disable relation managers on view page since info is in tabs
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Header Summary Section
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\Group::make([
                                        Infolists\Components\TextEntry::make('name')
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                            ->weight(FontWeight::Bold),

                                        Infolists\Components\TextEntry::make('short_description')
                                            ->color('gray')
                                            ->placeholder('No short description provided'),

                                        Infolists\Components\Grid::make(3)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('sku')
                                                    ->label('SKU')
                                                    ->copyable()
                                                    ->badge()
                                                    ->color('primary'),

                                                Infolists\Components\TextEntry::make('status')
                                                    ->badge()
                                                    ->color(fn (string $state): string => match ($state) {
                                                        'draft' => 'warning',
                                                        'published' => 'success',
                                                        'archived' => 'danger',
                                                    }),

                                                Infolists\Components\TextEntry::make('vendor.business_name')
                                                    ->label('Store')
                                                    ->default('Main Store')
                                                    ->badge()
                                                    ->color('info'),
                                            ]),
                                    ]),

                                    Infolists\Components\Group::make([
                                        Infolists\Components\ImageEntry::make('image')
                                            ->label('')
                                            ->height(200)
                                            ->width(200)
                                            ->extraImgAttributes(['class' => 'rounded-lg']),
                                    ])->grow(false),
                                ]),
                        ]),

                        // Quick Stats Bar
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('price')
                                    ->label('Selling Price')
                                    ->money('USD')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->color('success')
                                    ->icon('heroicon-m-currency-dollar')
                                    ->iconPosition(IconPosition::Before),

                                Infolists\Components\TextEntry::make('effective_stock')
                                    ->label('Stock Level')
                                    ->getStateUsing(function (Product $record): string {
                                        if (! $record->track_inventory) {
                                            return 'Not tracked';
                                        }

                                        return (string) $record->getEffectiveStockQuantity();
                                    })
                                    ->badge()
                                    ->color(fn (Product $record): string => ! $record->track_inventory ? 'secondary' : $record->getStockColor())
                                    ->icon('heroicon-m-cube')
                                    ->iconPosition(IconPosition::Before),

                                Infolists\Components\TextEntry::make('categories_count')
                                    ->label('Categories')
                                    ->getStateUsing(fn (Product $record): string => $record->categories()->count().' assigned')
                                    ->icon('heroicon-m-tag')
                                    ->iconPosition(IconPosition::Before),

                                Infolists\Components\IconEntry::make('is_featured')
                                    ->label('Featured')
                                    ->boolean()
                                    ->trueIcon('heroicon-s-star')
                                    ->falseIcon('heroicon-o-star')
                                    ->trueColor('warning')
                                    ->falseColor('gray'),
                            ]),
                    ])
                    ->columnSpanFull(),

                Infolists\Components\Tabs::make('Product Details')
                    ->tabs([
                        // Overview Tab
                        Infolists\Components\Tabs\Tab::make('Overview')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Infolists\Components\Grid::make(3)
                                    ->schema([
                                        // Left Column - Product Details
                                        Infolists\Components\Group::make([
                                            Infolists\Components\Section::make('Product Information')
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('description')
                                                        ->label('Description')
                                                        ->html()
                                                        ->placeholder('No description provided'),

                                                    Infolists\Components\TextEntry::make('slug')
                                                        ->label('URL Slug')
                                                        ->badge()
                                                        ->color('gray')
                                                        ->copyable(),
                                                ]),

                                            Infolists\Components\Section::make('Organization')
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('categories.name')
                                                        ->label('Categories')
                                                        ->badge()
                                                        ->separator(',')
                                                        ->placeholder('No categories assigned'),

                                                    Infolists\Components\TextEntry::make('tags')
                                                        ->label('Tags')
                                                        ->badge()
                                                        ->separator(',')
                                                        ->placeholder('No tags assigned'),
                                                ]),
                                        ])
                                            ->columnSpan(['lg' => 2]),

                                        // Right Column - Sidebar Info
                                        Infolists\Components\Group::make([
                                            Infolists\Components\Section::make('Publication')
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('published_at')
                                                        ->label('Published Date')
                                                        ->dateTime()
                                                        ->placeholder('Not published'),

                                                    Infolists\Components\TextEntry::make('sort_order')
                                                        ->label('Sort Order')
                                                        ->badge(),
                                                ]),

                                            Infolists\Components\Section::make('System Info')
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('created_at')
                                                        ->label('Created')
                                                        ->dateTime()
                                                        ->since(),

                                                    Infolists\Components\TextEntry::make('updated_at')
                                                        ->label('Last Updated')
                                                        ->dateTime()
                                                        ->since(),
                                                ]),
                                        ])
                                            ->columnSpan(['lg' => 1]),
                                    ]),
                            ]),

                        // Pricing & Inventory Tab
                        Infolists\Components\Tabs\Tab::make('Pricing & Inventory')
                            ->icon('heroicon-m-currency-dollar')
                            ->schema([
                                Infolists\Components\Section::make('Pricing Information')
                                    ->schema([
                                        Infolists\Components\Grid::make(2)
                                            ->schema([
                                                Infolists\Components\Group::make([
                                                    Infolists\Components\TextEntry::make('price')
                                                        ->label('Selling Price')
                                                        ->money('USD')
                                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                                        ->weight(FontWeight::Bold)
                                                        ->color('success'),

                                                    Infolists\Components\TextEntry::make('compare_price')
                                                        ->label('Compare at Price')
                                                        ->money('USD')
                                                        ->placeholder('No compare price'),

                                                    Infolists\Components\TextEntry::make('discount_percentage')
                                                        ->label('Discount')
                                                        ->getStateUsing(fn (Product $record): ?string => $record->getDiscountPercentage() ? $record->getDiscountPercentage().'%' : null
                                                        )
                                                        ->badge()
                                                        ->color('success')
                                                        ->placeholder('No discount'),
                                                ]),

                                                Infolists\Components\Group::make([
                                                    Infolists\Components\TextEntry::make('cost_price')
                                                        ->label('Cost per Item')
                                                        ->money('USD')
                                                        ->placeholder('No cost price'),

                                                    Infolists\Components\TextEntry::make('profit_margin')
                                                        ->label('Profit Margin')
                                                        ->getStateUsing(function (Product $record): ?string {
                                                            if (! $record->cost_price || ! $record->price) {
                                                                return null;
                                                            }
                                                            $margin = (($record->price - $record->cost_price) / $record->price) * 100;

                                                            return number_format($margin, 1).'%';
                                                        })
                                                        ->badge()
                                                        ->color(fn ($state): string => $state && floatval($state) > 30 ? 'success' : 'warning')
                                                        ->placeholder('Cannot calculate'),
                                                ]),
                                            ]),
                                    ]),

                                Infolists\Components\Section::make('Inventory Management')
                                    ->schema([
                                        Infolists\Components\Grid::make(4)
                                            ->schema([
                                                Infolists\Components\IconEntry::make('track_inventory')
                                                    ->label('Track Inventory')
                                                    ->boolean()
                                                    ->trueIcon('heroicon-o-check-circle')
                                                    ->falseIcon('heroicon-o-x-circle')
                                                    ->trueColor('success')
                                                    ->falseColor('danger'),

                                                Infolists\Components\TextEntry::make('inventory_quantity')
                                                    ->label('Current Stock')
                                                    ->visible(fn (Product $record): bool => $record->track_inventory && ! $record->hasVariants())
                                                    ->badge()
                                                    ->color(fn (Product $record): string => $record->getStockColor()),

                                                Infolists\Components\TextEntry::make('low_stock_threshold')
                                                    ->label('Low Stock Alert')
                                                    ->visible(fn (Product $record): bool => $record->track_inventory)
                                                    ->badge()
                                                    ->color('gray'),

                                                Infolists\Components\TextEntry::make('stock_status')
                                                    ->label('Stock Status')
                                                    ->getStateUsing(fn (Product $record): string => $record->getStockText())
                                                    ->badge()
                                                    ->color(fn (Product $record): string => ! $record->track_inventory ? 'secondary' : $record->getStockColor()),
                                            ]),
                                    ]),

                                // Variants Section
                                Infolists\Components\Section::make('Product Variants')
                                    ->schema([
                                        Infolists\Components\RepeatableEntry::make('variants')
                                            ->schema([
                                                Infolists\Components\Grid::make(5)
                                                    ->schema([
                                                        Infolists\Components\TextEntry::make('name')
                                                            ->label('Variant')
                                                            ->weight(FontWeight::SemiBold),

                                                        Infolists\Components\TextEntry::make('sku')
                                                            ->label('SKU')
                                                            ->copyable()
                                                            ->badge()
                                                            ->color('primary'),

                                                        Infolists\Components\TextEntry::make('price')
                                                            ->money('USD')
                                                            ->color('success'),

                                                        Infolists\Components\TextEntry::make('inventory_quantity')
                                                            ->label('Stock')
                                                            ->badge()
                                                            ->color(fn ($state): string => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger')),

                                                        Infolists\Components\IconEntry::make('is_active')
                                                            ->label('Active')
                                                            ->boolean()
                                                            ->trueColor('success')
                                                            ->falseColor('danger'),
                                                    ]),
                                            ])
                                            ->contained(false),
                                    ])
                                    ->visible(fn (Product $record): bool => $record->hasVariants())
                                    ->collapsed(),
                            ]),

                        // Media & Gallery Tab
                        Infolists\Components\Tabs\Tab::make('Media & Gallery')
                            ->icon('heroicon-m-photo')
                            ->schema([
                                Infolists\Components\Section::make('Main Product Image')
                                    ->schema([
                                        Infolists\Components\ImageEntry::make('image')
                                            ->label('')
                                            ->height(400)
                                            ->width(400)
                                            ->extraImgAttributes(['class' => 'mx-auto rounded-lg shadow-lg']),
                                    ])
                                    ->visible(fn (Product $record): bool => ! empty($record->image)),

                                Infolists\Components\Section::make('Image Gallery')
                                    ->description('Additional product images')
                                    ->schema([
                                        Infolists\Components\RepeatableEntry::make('gallery')
                                            ->label('')
                                            ->schema([
                                                Infolists\Components\ImageEntry::make('url')
                                                    ->label('')
                                                    ->height(200)
                                                    ->width(200)
                                                    ->extraImgAttributes(['class' => 'rounded-lg shadow']),
                                            ])
                                            ->columns(4)
                                            ->contained(false),
                                    ])
                                    ->visible(fn (Product $record): bool => is_array($record->gallery) && count($record->gallery) > 0),

                                Infolists\Components\Section::make('No Additional Images')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('gallery_status')
                                            ->label('')
                                            ->getStateUsing(fn (): string => 'No additional images have been uploaded for this product.')
                                            ->color('gray'),
                                    ])
                                    ->visible(fn (Product $record): bool => ! is_array($record->gallery) || count($record->gallery) === 0),
                            ]),

                        // SEO & Technical Tab
                        Infolists\Components\Tabs\Tab::make('SEO & Technical')
                            ->icon('heroicon-m-cog-6-tooth')
                            ->schema([
                                Infolists\Components\Grid::make(2)
                                    ->schema([
                                        // SEO Section
                                        Infolists\Components\Group::make([
                                            Infolists\Components\Section::make('Search Engine Optimization')
                                                ->icon('heroicon-m-magnifying-glass')
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('seo_meta.title')
                                                        ->label('SEO Title')
                                                        ->placeholder('No SEO title set')
                                                        ->helperText('Recommended: 50-60 characters'),

                                                    Infolists\Components\TextEntry::make('seo_meta.description')
                                                        ->label('SEO Description')
                                                        ->placeholder('No SEO description set')
                                                        ->helperText('Recommended: 150-160 characters'),

                                                    Infolists\Components\TextEntry::make('seo_meta.keywords')
                                                        ->label('SEO Keywords')
                                                        ->badge()
                                                        ->separator(',')
                                                        ->placeholder('No SEO keywords set'),
                                                ]),

                                            Infolists\Components\Section::make('URL & Sharing')
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('product_url')
                                                        ->label('Product URL')
                                                        ->getStateUsing(fn (Product $record): string => url("/products/{$record->slug}")
                                                        )
                                                        ->url(fn (Product $record): string => url("/products/{$record->slug}")
                                                        )
                                                        ->openUrlInNewTab()
                                                        ->copyable(),
                                                ]),
                                        ]),

                                        // Technical Section
                                        Infolists\Components\Group::make([
                                            Infolists\Components\Section::make('Physical Specifications')
                                                ->icon('heroicon-m-cube')
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('weight')
                                                        ->label('Weight')
                                                        ->suffix(' kg')
                                                        ->placeholder('No weight specified'),

                                                    Infolists\Components\TextEntry::make('dimensions')
                                                        ->label('Dimensions (L × W × H)')
                                                        ->getStateUsing(function (Product $record): ?string {
                                                            if (! $record->dimensions) {
                                                                return null;
                                                            }
                                                            $dim = $record->dimensions;

                                                            return isset($dim['length'], $dim['width'], $dim['height'])
                                                                ? "{$dim['length']} × {$dim['width']} × {$dim['height']} cm"
                                                                : null;
                                                        })
                                                        ->placeholder('No dimensions specified'),
                                                ]),

                                            Infolists\Components\Section::make('System Metadata')
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('id')
                                                        ->label('Product ID')
                                                        ->badge()
                                                        ->color('gray'),

                                                    Infolists\Components\TextEntry::make('created_at')
                                                        ->label('Created')
                                                        ->dateTime(),

                                                    Infolists\Components\TextEntry::make('updated_at')
                                                        ->label('Last Modified')
                                                        ->dateTime(),
                                                ]),
                                        ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
