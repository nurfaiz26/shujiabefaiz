<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingTransactionResource\Pages;
use App\Filament\Resources\BookingTransactionResource\RelationManagers;
use App\Models\BookingTransaction;
use App\Models\HomeService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportConsoleCommands\Commands\MakeCommand;

class BookingTransactionResource extends Resource
{
    protected static ?string $model = BookingTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Transaction';

    public static function updateTotal(Set $set, Get $get)
    {
        $selectedHomeService = collect($get('transactionDetails'))->filter(fn($item) => !empty($item['home_service_id']));

        $prices = HomeService::find($selectedHomeService->pluck('home_service_id'))->pluck('price', 'id');

        $subtotal = $selectedHomeService->reduce(function ($subtotal, $item) use ($prices) {
            return $subtotal + ($prices[$item['home_service_id']] * 1);
        }, 0);

        $total_tax_amount = round($subtotal * 0.11);

        $total_amount = round($subtotal + $total_tax_amount);

        $set('sub_total', number_format($subtotal, 0, '.', ''));

        $set('total_tax_amount', number_format($total_tax_amount, 0, '.', ''));

        $set('total_amount', number_format($total_amount, 0, '.', ''));
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Wizard::make([
                    Step::make('Product and Price')
                        ->completedIcon('heroicon-m-hand-thumb-up')
                        ->description('Add your product items')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Repeater::make('transactionDetails')
                                        ->relationship('transactionDetails')
                                        ->schema([
                                            Select::make('home_service_id')
                                                ->relationship('homeService', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->label('Select Product')
                                                ->live()
                                                ->afterStateUpdated(function ($state, callable $set) {
                                                    $home_service = HomeService::find($state);
                                                    $set('price', $home_service ? $home_service->price : 0);
                                                }),

                                            TextInput::make('price')
                                                ->required()
                                                ->numeric()
                                                ->readOnly()
                                                ->label('Price')
                                                ->hint('Price will be filled automically based on product selection'),

                                        ])
                                        ->live()
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            self::updateTotal($set, $get);
                                        })
                                        ->minItems(1)
                                        ->columnSpan('full')
                                        ->label('Choose Products'),

                                    Grid::make()
                                        ->schema([
                                            TextInput::make('sub_total')
                                                ->numeric()
                                                ->required()
                                                ->readOnly()
                                                ->label('Sub Total Amount'),

                                            TextInput::make('total_tax_amount')
                                                ->numeric()
                                                ->required()
                                                ->readOnly()
                                                ->label('Total Tax Amount'),

                                            TextInput::make('total_amount')
                                                ->numeric()
                                                ->readOnly()
                                                ->required()
                                                ->label('Total Amount'),
                                        ])

                                ])
                        ]),

                    Step::make('Customer Information')
                        ->completedIcon('heroicon-m-hand-thumb-up')
                        ->description('For our marketing')
                        ->schema([

                            Grid::make(2)
                                ->schema([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('phone')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('email')
                                        ->required()
                                        ->maxLength(255),


                                ])
                        ]),

                    Step::make('Delivery Information')
                        ->completedIcon('heroicon-m-hand-thumb-up')
                        ->description('Put your correct address')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('city')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('post_code')
                                        ->required()
                                        ->maxLength(255),

                                    DatePicker::make('schedule_at')
                                        ->required(),

                                    TimePicker::make('started_time')
                                        ->required(),

                                    Textarea::make('address')
                                        ->required()
                                        ->maxLength(255),
                                ]),
                        ]),

                    Step::make('Payment Information')
                        ->completedIcon('heroicon-m-hand-thumb-up')
                        ->description('Review your payment')
                        ->schema([
                            Grid::Make(3)
                                ->schema([
                                    TextInput::make('booking_trx_id')
                                        ->required()
                                        ->maxLength(255),

                                    ToggleButtons::make('is_paid')
                                        ->label('Apakah sudah membayar')
                                        ->boolean()
                                        ->grouped()
                                        ->icons([
                                            true => 'heroicon-o-pencil',
                                            false => 'heroicon-o-clock'
                                        ])
                                        ->required(),

                                    FileUpload::make('proof')
                                        ->image()
                                        ->required(),
                                ])

                        ])
                ])
                    ->columnSpan('full')
                    ->columns(1)
                    ->skippable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('booking_trx_id')
                    ->searchable(),

                TextColumn::make('created_at'),

                IconColumn::make('is_paid')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->label('Terverifikasi'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->action(function (BookingTransaction $record) {
                        $record->is_paid = true;
                        $record->save();

                        Notification::make()
                            ->title('Order Approved')
                            ->success()
                            ->body('Teh order has been succesfully approved')
                            ->send();
                    })
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn(BookingTransaction $record) => !$record->is_paid)
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListBookingTransactions::route('/'),
            'create' => Pages\CreateBookingTransaction::route('/create'),
            'edit' => Pages\EditBookingTransaction::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
