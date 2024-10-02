<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Council;
use App\Models\Position;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\CouncilPosition;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CouncilPositionResource\Pages;
use App\Filament\Resources\CouncilPositionResource\RelationManagers;

class CouncilPositionResource extends Resource
{
    protected static ?string $model = CouncilPosition::class;

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
                    Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->options(User::all()->map(function($item){
                        return [
                            'name'=> $item->getFilamentName(),
                            'id'=>$item->id,
                        ];
                    })->pluck('name', 'id'))
                    ->preload()

                    ->searchable(),
                    Select::make('position')
                    ->label('Position')
                    ->options(Position::all()->pluck('name', 'name'))
                    ->preload()

                    ->searchable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('council.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_id')
                ->label('User')
                ->formatStateUsing(function(Model $record){
                    return $record->user->getFilamentName();
                })->searchable(),

                Tables\Columns\TextColumn::make('position')
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
            'index' => Pages\ListCouncilPositions::route('/'),
            'create' => Pages\CreateCouncilPosition::route('/create'),
            'edit' => Pages\EditCouncilPosition::route('/{record}/edit'),
        ];
    }
}
