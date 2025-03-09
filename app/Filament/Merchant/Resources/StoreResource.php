<?php

namespace App\Filament\Merchant\Resources;

use App\Filament\Merchant\Resources\StoreResource\Pages;
use App\Filament\Merchant\Resources\StoreResource\RelationManagers;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use ArberMustafa\FilamentLocationPickrField\Forms\Components\LocationPickr;

class StoreResource extends Resource
{
    protected static ?string $model = Store::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where("user_id", auth()->user()->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Information')
                    ->aside()
                    ->description('Name, address, categories, phone number...')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->translatable(),
                        TextInput::make('address')
                            ->required()
                            ->translatable(),
                        Select::make('categories')
                            ->preload()
                            ->multiple()
                            ->relationship(titleAttribute:'name'),
                        TextInput::make('phone')
                            ->tel()
                    ]),
                Section::make('Operating Information')
                    ->aside()
                    ->description('Minimum cart value, working hours...')
                    ->schema([
                        TextInput::make('minimum_cart_value')
                            ->prefix('€')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('delivery_range')
                            ->prefix('km')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('working_hours')
                    ]),
                Section::make('Location')
                    ->aside()
                    ->description("Drag the marker and set the store's location")
                    ->schema([
                        LocationPickr::make('location')
                            ->height(config('filament-locationpickr-field.default_height'))
                            ->defaultLocation(config('filament-locationpickr-field.default_location'))
                            ->defaultZoom(config('filament-locationpickr-field.default_zoom'))
                            ->draggable(),
                    ]),
                Section::make('Photos')
                    ->aside()
                    ->description("Add a logo and a cover photo")
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('logo')
                            ->collection('logo'),
                        SpatieMediaLibraryFileUpload::make('cover')
                            ->collection('cover')
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('logo')
                    ->collection('logo'),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('address'),
                TextColumn::make('categories.name'),
                TextColumn::make('minimum_cart_value')
                    ->money("EUR"),
                TextColumn::make('delivery_range')->suffix(' km')
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
            'index' => Pages\ListStores::route('/'),
            'create' => Pages\CreateStore::route('/create'),
            'edit' => Pages\EditStore::route('/{record}/edit'),
        ];
    }
}
