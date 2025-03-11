<?php

namespace App\Filament\Admin\Resources\StoreResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Information')
                    ->aside()
                    ->description("Name, category, price, active...")
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->translatable(),
                        Textarea::make('description')
                            ->translatable(),
                        TextInput::make('price')
                            ->prefix('€')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        Select::make('product_category_id')
                            ->required()
                            ->preload()
                            ->native(false)
                            ->relationship(
                                name:'productCategory', 
                                titleAttribute:'name',
                                modifyQueryUsing: fn(Builder $query)=> $query
                                    ->whereRelation("store", "user_id", auth()->id())
                            ),
                        Toggle::make('active')
                            ->default(true),
                    ]),
                    Section::make('Photos')
                        ->aside()
                        ->description("Add photo")
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('gallery')
                                ->collection('gallery')
                                ->multiple()
                                ->reorderable()
                        ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('sort')
            ->reorderable('sort')
            ->defaultGroup('productCategory.name')
            ->groups([
                Group::make('productCategory.name')
                    ->titlePrefixedWithLabel(false)
                // ->orderQueryUsing(fn (Builder $query, string $direction) => $query
                //     ->join('product_categories', 'product_categories.id', '=', 'products.id')
                //     ->orderBy('product_categories.sort'))
            ])
            ->columns([
                Tables\Columns\ImageColumn::make('mainImage'),
                Tables\Columns\TextColumn::make('name')
                    ->description(fn(Product $record) => $record->description),
                Tables\Columns\TextColumn::make('productCategory.name'),
                Tables\Columns\TextColumn::make('price')
                    ->suffix('€ '),
                Tables\Columns\BooleanColumn::make('active'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
