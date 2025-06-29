<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make([
                'default' => 1,
                'lg' => 3,
            ])
                ->schema([
                    // Main Content Area (2/3 width)
                    Forms\Components\Group::make([
                        // Product Information
                        Forms\Components\Section::make('Product Information')
                            ->description('Basic product details and content')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Product Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null
                                    )
                                    ->columnSpanFull(),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('slug')
                                            ->label('URL Slug')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ProductResource::getModel(), 'slug', ignoreRecord: true)
                                            ->helperText('Used in the product URL'),

                                        Forms\Components\TextInput::make('sku')
                                            ->label('SKU')
                                            ->required()
                                            ->unique(ProductResource::getModel(), 'sku', ignoreRecord: true)
                                            ->helperText('Stock Keeping Unit - must be unique'),
                                    ]),

                                Forms\Components\RichEditor::make('description')
                                    ->label('Product Description')
                                    ->required()
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'strike',
                                        'link',
                                        'heading',
                                        'bulletList',
                                        'orderedList',
                                        'blockquote',
                                        'codeBlock',
                                    ])
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('short_description')
                                    ->label('Short Description')
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->helperText('Brief description for product cards and previews')
                                    ->columnSpanFull(),
                            ]),

                        // Pricing & Inventory
                        Forms\Components\Section::make('Pricing & Inventory')
                            ->description('Set pricing and manage stock levels')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('price')
                                            ->label('Selling Price')
                                            ->required()
                                            ->numeric()
                                            ->prefix('$')
                                            ->step(0.01)
                                            ->helperText('Customer-facing price'),

                                        Forms\Components\TextInput::make('compare_price')
                                            ->label('Compare at Price')
                                            ->numeric()
                                            ->prefix('$')
                                            ->step(0.01)
                                            ->helperText('Original price (for discounts)'),

                                        Forms\Components\TextInput::make('cost_price')
                                            ->label('Cost per Item')
                                            ->numeric()
                                            ->prefix('$')
                                            ->step(0.01)
                                            ->helperText('Your cost (for profit calculation)'),
                                    ]),

                                Forms\Components\Toggle::make('track_inventory')
                                    ->label('Track Inventory')
                                    ->helperText('Enable to monitor stock levels')
                                    ->live()
                                    ->columnSpanFull(),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('inventory_quantity')
                                            ->label('Current Stock')
                                            ->numeric()
                                            ->minValue(0)
                                            ->helperText('Available units in stock')
                                            ->visible(fn (Forms\Get $get) => $get('track_inventory')),

                                        Forms\Components\TextInput::make('low_stock_threshold')
                                            ->label('Low Stock Alert')
                                            ->numeric()
                                            ->default(5)
                                            ->minValue(0)
                                            ->helperText('Alert when stock falls below this number')
                                            ->visible(fn (Forms\Get $get) => $get('track_inventory')),
                                    ]),
                            ]),

                        // SEO Section
                        Forms\Components\Section::make('Search Engine Optimization')
                            ->description('Optimize your product for search engines')
                            ->schema([
                                Forms\Components\TextInput::make('seo_meta.title')
                                    ->label('SEO Title')
                                    ->maxLength(60)
                                    ->helperText('Page title for search engines (60 chars max)')
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('seo_meta.description')
                                    ->label('SEO Description')
                                    ->rows(3)
                                    ->maxLength(160)
                                    ->helperText('Meta description for search results (160 chars max)')
                                    ->columnSpanFull(),

                                Forms\Components\TagsInput::make('seo_meta.keywords')
                                    ->label('SEO Keywords')
                                    ->helperText('Keywords for search optimization')
                                    ->columnSpanFull(),
                            ])
                            ->collapsed(),
                    ])
                        ->columnSpan(['lg' => 2]),

                    // Sidebar Area (1/3 width)
                    Forms\Components\Group::make([
                        // Publication Status
                        Forms\Components\Section::make('Publication')
                            ->description('Control when and how your product is published')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'published' => 'Published',
                                        'archived' => 'Archived',
                                    ])
                                    ->default('draft')
                                    ->required()
                                    ->native(false),

                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label('Publish Date')
                                    ->helperText('Leave empty to publish immediately')
                                    ->native(false),

                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Featured Product')
                                    ->helperText('Show in featured sections'),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Lower numbers appear first'),
                            ]),

                        // Store Assignment
                        Forms\Components\Section::make('Store Assignment')
                            ->description('Assign this product to a specific store')
                            ->schema([
                                Forms\Components\Select::make('vendor_id')
                                    ->label('Assign to Store')
                                    ->relationship('vendor', 'business_name')
                                    ->nullable()
                                    ->placeholder('Main Store')
                                    ->searchable()
                                    ->helperText('Leave empty for main store'),
                            ]),

                        // Product Images
                        Forms\Components\Section::make('Product Images')
                            ->description('Upload and manage product photos')
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->label('Main Product Image')
                                    ->image()
                                    ->directory('products')
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '1:1',
                                        '4:3',
                                        '16:9',
                                    ])
                                    ->helperText('Primary image shown in listings'),

                                Forms\Components\FileUpload::make('gallery')
                                    ->label('Additional Images')
                                    ->image()
                                    ->multiple()
                                    ->directory('products/gallery')
                                    ->imageEditor()
                                    ->reorderable()
                                    ->helperText('Additional product photos'),
                            ]),

                        // Organization
                        Forms\Components\Section::make('Organization')
                            ->description('Categorize and tag your product')
                            ->schema([
                                Forms\Components\Select::make('categories')
                                    ->label('Product Categories')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->helperText('Select relevant categories'),

                                Forms\Components\TagsInput::make('tags')
                                    ->label('Tags')
                                    ->helperText('Add tags for better organization'),
                            ]),

                        // Technical Details
                        Forms\Components\Section::make('Technical Details')
                            ->description('Physical specifications and dimensions')
                            ->schema([
                                Forms\Components\TextInput::make('weight')
                                    ->label('Weight (kg)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->suffix('kg'),

                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('dimensions.length')
                                        ->label('Length (cm)')
                                        ->numeric()
                                        ->step(0.1),

                                    Forms\Components\TextInput::make('dimensions.width')
                                        ->label('Width (cm)')
                                        ->numeric()
                                        ->step(0.1),

                                    Forms\Components\TextInput::make('dimensions.height')
                                        ->label('Height (cm)')
                                        ->numeric()
                                        ->step(0.1),
                                ])
                                    ->columns(3),
                            ])
                            ->collapsed(),
                    ])
                        ->columnSpan(['lg' => 1]),
                ]),
        ]);
    }
}
