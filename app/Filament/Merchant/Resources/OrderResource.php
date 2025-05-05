<?php

namespace App\Filament\Merchant\Resources;

use App\Enum\OrderStatus;
use App\Enum\PaymentStatus;
use App\Enum\ShippingStatus;
use App\Filament\Merchant\Resources\OrderResource\Pages;
use App\Filament\Merchant\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Infolists\Components\OrderNoteEntry;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                "store",
                "user",
                "address"
            ])
            ->whereRelation("store", "user_id", auth()->id());
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Information')
                    ->description('Basic information for the order')
                    ->columns(2)
                    ->schema([
                        Group::make([
                            TextEntry::make('id')
                                ->columnSpan(1)
                                ->label('Order ID')
                                ->size(TextEntry\TextEntrySize::Large)
                                ->formatStateUsing(fn($state): string => "#$state"),
                            TextEntry::make('store')
                                ->columnSpan(1)
                                ->label('Store')
                                ->html()
                                ->formatStateUsing(function ($state) {
                                    return new HtmlString(
                                        "<div class='flex items-center gap-2'>
                                                    <img src='" . $state->logo . "' alt='Store Logo' class='w-8 h-8 rounded-full mr-2'>
                                                    <span class='font-semibold'>" . $state->name . "</span>
                                                </div>"
                                    );
                                }),
                            TextEntry::make('user')
                                ->columnSpan(1)
                                ->label('User')
                                ->html()
                                ->formatStateUsing(function ($state) {
                                    return new HtmlString(
                                        "<div class='flex items-center gap-2'>
                                            <img src='" . $state->avatar . "' alt='User avatar' class='w-8 h-8 rounded-full mr-2'>
                                            <span class='font-semibold'>" . $state->name . " " . $state->email . "</span>
                                        </div>"
                                    );
                                }),
                        ])
                            ->columnSpan(2)
                            ->columns(3),
                        OrderNoteEntry::make("note")
                            ->hidden(fn($record): bool => !$record->note)
                            ->columnSpan(2),
                        TextEntry::make('address.full_address')
                            ->columnSpan(1)
                            ->label('Address'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(function ($state) {
                                return OrderStatus::from($state)->color();
                            }),
                    ]),
                Section::make('Payment')
                    ->collapsible()
                    ->collapsed()
                    ->description('Payment method, payment status, product price, total price and tip')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('payment_method')
                            ->columnSpan(1)
                            ->formatStateUsing(function ($state) {
                                return match ($state) {
                                    'card' => 'Credit Card',
                                    'cod' => 'Cash on Delivery',
                                    default => 'Unknown',
                                };
                            }),
                        TextEntry::make('payment_status')
                            ->columnSpan(1)
                            ->badge()
                            ->color(function ($state) {
                                return PaymentStatus::from($state)->color();
                            }),

                        TextEntry::make('total_price')
                            ->columnSpan(1)
                            ->money('EUR'),
                        TextEntry::make('products_price')
                            ->columnSpan(1)
                            ->money('EUR'),

                        TextEntry::make('discount')
                            ->columnSpan(1)
                            ->money('EUR'),
                        TextEntry::make('tip')
                            ->columnSpan(1)
                            ->money('EUR'),
                    ]),
                Section::make('Shipping')
                    ->collapsible()
                    ->collapsed()
                    ->description('Shipping method, shipping status, shipping price and delivery time')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('shipping_method')
                            ->columnSpan(1)
                            ->formatStateUsing(function ($state) {
                                return match ($state) {
                                    'delivery' => 'Delivery',
                                    'takeaway' => 'Takeaway',
                                    default => 'Unknown',
                                };
                            }),
                        TextEntry::make('shipping_status')
                            ->columnSpan(1)
                            ->badge()
                            ->color(function ($state) {
                                return ShippingStatus::from($state)->color();
                            }),

                        TextEntry::make('shipping_price')
                            ->columnSpan(1)
                            ->money('EUR'),
                        TextEntry::make('delivery_time')
                            ->columnSpan(1)
                            ->formatStateUsing(function ($state) {
                                return "$state minutes";
                            }),
                    ]),
                Section::make('Products')
                    ->columns(1)
                    ->schema([
                        RepeatableEntry::make('products')
                            ->schema([
                                ImageEntry::make('product.main_image')
                                    ->width("100%"),
                                TextEntry::make('product_name'),
                                TextEntry::make('quantity')
                                    ->formatStateUsing(fn($state): string => "x $state"),
                                TextEntry::make('price')
                                    ->label("Unit price")
                                    ->money('EUR'),
                                TextEntry::make('total_price')
                                    ->money('EUR'),

                                OrderNoteEntry::make('note')
                                    ->hidden(fn($record): bool => !$record->note)
                                    ->columnSpanFull(),
                            ])
                            ->columns(5)
                    ]),
                Actions::make([
                    Action::make('status')
                        ->color("info")
                        ->label("Start processing")
                        ->visible(fn(Model $record): bool => $record->status === OrderStatus::pending->value)
                        ->icon('heroicon-s-play')
                        ->action(function (Model $record) {
                            $record->status = OrderStatus::processing->value;
                            $record->save();
                        }),
                    Action::make('status')
                        ->color("success")
                        ->label("Out for delivery")
                        ->visible(fn(Model $record): bool => $record->status === OrderStatus::processing->value)
                        ->icon('heroicon-s-arrow-up-right')
                        ->action(function (Model $record) {
                            $record->status = OrderStatus::outForDelivery->value;
                            $record->save();
                        }),
                    Action::make('status')
                        ->color("danger")
                        ->label("Cancel order")
                        ->icon('heroicon-o-x-mark')
                        ->action(function (Model $record) {
                            $record->status = OrderStatus::cancelled->value;
                            $record->save();
                        }),
                ])
                    ->columnSpanFull()
                    ->alignEnd(),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('store.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('address.full_address'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->since(),
                // SelectColumn::make('status')
                //     ->extraAttributes(function ($state) {
                //         $bgColor = OrderStatus::from($state)->color();
                //         return ['class' => "bg-{$bgColor}-500"];
                //     })
                //     ->options(
                //         collect(OrderStatus::cases())
                //             ->mapWithKeys(fn($enum) => [$enum->value => $enum->name])
                //             ->toArray()
                //     ),
                TextColumn::make("status")
                    ->badge()
                    ->color(function ($state) {
                        return OrderStatus::from($state)->color();
                    }),
                TextColumn::make('payment_method')
                    ->icon(fn(string $state): string => match ($state) {
                        'card' => 'heroicon-o-credit-card',
                        'cod' => 'heroicon-o-banknotes',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'card' => 'info',
                        'cod' => 'success',
                        default => 'danger',
                    }),
                TextColumn::make('payment_status')
                    ->badge()
                    ->color(function ($state) {
                        return PaymentStatus::from($state)->color();
                    }),
                TextColumn::make('shipping_method'),
                TextColumn::make('shipping_status')
                    ->badge()
                    ->color(function ($state) {
                        return ShippingStatus::from($state)->color();
                    }),
                TextColumn::make('total_price')
                    ->money('EUR')

            ])
            ->filters([
                SelectFilter::make('status')
                    ->searchable()
                    ->native(false)
                    ->options(
                        collect(OrderStatus::cases())
                            ->mapWithKeys(fn($enum) => [$enum->value => $enum->name])
                            ->toArray()
                    ),
                SelectFilter::make('payment_status')
                    ->searchable()
                    ->native(false)
                    ->options(
                        collect(PaymentStatus::cases())
                            ->mapWithKeys(fn($enum) => [$enum->value => $enum->name])
                            ->toArray()
                    ),
                Filter::make('price')
                    ->form([
                        TextInput::make('more_than')
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                $set('less_than', null);
                            })
                            ->default(null)
                            ->live()
                            ->placeholder("More than...")
                            ->minValue(0)
                            ->suffix('€')
                            ->numeric(),
                        TextInput::make('less_than')
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                $set('more_than', null);
                            })
                            ->default(null)
                            ->live()
                            ->placeholder("Less than...")
                            ->minValue(0)
                            ->suffix('€')
                            ->numeric(),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if ($data['more_than']) {
                            return 'More than ' . Number::currency($data['more_than'], 'EUR');
                        }
                        if ($data['less_than']) {
                            return 'Less than ' . Number::currency($data['less_than'], 'EUR');
                        }

                        return null;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['less_than'],
                                fn(Builder $query, $price): Builder => $query->where('total_price', '<', $price),
                            )
                            ->when(
                                $data['more_than'],
                                fn(Builder $query, $price): Builder => $query->where('total_price', '>', $price),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
