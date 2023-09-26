<?php

namespace App\Filament\Resources;

use Form\Set;
use Filament\Forms;
use Filament\Tables;
use App\Models\Produk;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Enums\ProduckTypeEnum;
use Filament\Resources\Resource;
use Filament\Forms\FormsComponent;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Group;
use function Laravel\Prompts\select;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\ProdukResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Factories\Relationship;
use App\Filament\Resources\ProdukResource\Pages\EditProduk;
use App\Filament\Resources\ProdukResource\RelationManagers;
use App\Filament\Resources\ProdukResource\Pages\ListProduks;
use App\Filament\Resources\ProdukResource\Pages\CreateProduk;
use Filament\Tables\Filters\SelectFilter;

class ProdukResource extends Resource
{
    protected static ?string $model = Produk::class;

    protected static ?string $navigationIcon = 'heroicon-o-Bolt';


    protected static ?string $navigationGroup = 'shop';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('name')
                                    ->reactive()
                                    ->required()
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                                TextInput::make('slug'),
                                RichEditor::make('decsription')
                                    ->columnSpan('full')
                            ])->columns(2),
                        Section::make('Pricing & Inventory')
                            ->schema([
                                TextInput::make('sku')
                                    ->label('SKU(Stok Keeping Unit)'),
                                TextInput::make('price')
                                    ->numeric()
                                    ->rules('regex:/^\d{1,6}(\.\d{0,2})?$/'),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100),
                                Select::make('type')
                                    ->options([
                                        'downloadable' => ProduckTypeEnum::DOWNLOADABLE->value,
                                        'deliverable' =>  ProduckTypeEnum::DELIVERABLE->value,
                                    ])
                            ])->columns(2),
                    ]),
                Group::make()
                    ->schema([
                        Forms\Components\Section::make('status')
                            ->schema([
                                Toggle::make('is_visible')
                                    ->label('Visibility')
                                    ->helperText('Enable or Disbale produk Visibility')
                                    ->default(true),
                                Toggle::make('is_featured')
                                    ->label('Featured')
                                    ->helperText('Enable or Disbale produk Featured')
                                    ->default(true),
                                DatePicker::make('publish_at')
                                    ->label('Availability')
                                    ->default(now())
                            ]),
                        Section::make('Image')
                            ->schema([
                                FileUpload::make('image')
                                    ->directory('form-attachments')
                                    ->preserveFilenames()
                                    ->image()
                                    ->imageEditor()
                            ])->collapsible(),

                        Section::make('Associatons')
                            ->schema([
                                Select::make('brand_id')
                                    ->Relationship('brand', 'name')
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('brand.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->sortable()
                    ->toggleable()
                    ->label('Visibility')
                    ->boolean(),
                Tables\Columns\TextColumn::make('price'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('publish_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
            ])
            ->filters([
                TernaryFilter::make('is_visible')
                    ->label('Visibility')
                    ->boolean()
                    ->trueLabel('only Visible Produk')
                    ->falselabel('only  Hiden Produk')
                    ->native(false),
                SelectFilter::make('brand')
                    ->relationship('brand', 'name')
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
            'index' => Pages\ListProduks::route('/'),
            'create' => Pages\CreateProduk::route('/create'),
            'edit' => Pages\EditProduk::route('/{record}/edit'),
        ];
    }
}
