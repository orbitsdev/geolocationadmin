<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Council;
use Filament\Forms\Form;
use App\Models\Collection;
use Filament\Tables\Table;
use App\Models\CouncilPosition;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CollectionResource\Pages;
use Awcodes\FilamentTableRepeater\Components\TableRepeater;
use App\Filament\Resources\CollectionResource\RelationManagers;

class CollectionResource extends Resource
{
    protected static ?string $model = Collection::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('council_id')
    ->label('Council Batch')
    ->options(Council::all()->pluck('name', 'id'))
    ->searchable()
    ->preload()
    ,
                    Forms\Components\Select::make('council_position_id')
                ->label('User')
                ->options(CouncilPosition::all()->map(function($item){
                    return [
                        'name'=> $item->user->getFilamentName(),
                        'id'=>$item->id,
                    ];
                })->pluck('name', 'id'))
                ->preload(),
                Forms\Components\TextInput::make('title')
                    ->maxLength(255),
                    Select::make('type')
                    ->label('Chart')
                ->options(Collection::CHART_OPTIONS)
                ->default(Collection::LINE_CHART)
                    ->searchable(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),


                    TableRepeater::make('collections_items')
                        ->relationship('collectionItems')
                        ->schema([
                            Forms\Components\TextInput::make('label')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric(),

                        ])
                        ->withoutHeader()


                        ->columnSpan('full')
                        ->label('Items')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('council.name')
                ->numeric(),

                    ViewColumn::make('council_position_id')->view('tables.columns.counsil-position')->label('User'),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
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
            'index' => Pages\ListCollections::route('/'),
            'create' => Pages\CreateCollection::route('/create'),
            'edit' => Pages\EditCollection::route('/{record}/edit'),
        ];
    }
}
