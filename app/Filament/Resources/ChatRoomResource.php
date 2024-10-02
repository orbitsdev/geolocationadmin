<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Council;
use App\Models\ChatRoom;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\CouncilPosition;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ChatRoomResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Awcodes\FilamentTableRepeater\Components\TableRepeater;
use App\Filament\Resources\ChatRoomResource\RelationManagers;

class ChatRoomResource extends Resource
{
    protected static ?string $model = ChatRoom::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('council_id')
    ->label('Council')
    ->options(Council::all()->pluck('name', 'id'))
    ->searchable()
    ->preload()
    ,
                Forms\Components\TextInput::make('name')
                    ->maxLength(255),

                    TableRepeater::make('chat_messages')
                    ->relationship('messages')
                    ->schema([
                        Forms\Components\Select::make('council_position_id')
                        ->label('User')
                        ->options(CouncilPosition::all()->map(function($item){
                            return [
                                'name'=> $item->user->getFilamentName(),
                                'id'=>$item->id,
                            ];
                        })->pluck('name', 'id'))
                        ->preload(),
                        Forms\Components\Textarea::make('message')
                    ->columnSpanFull(),

                    ])
                    ->withoutHeader()


                    ->columnSpan('full')
                    ->label('Messages')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('council.name')
                ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                    Tables\Columns\TextColumn::make('messages_count')->counts('messages'),
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
            'index' => Pages\ListChatRooms::route('/'),
            'create' => Pages\CreateChatRoom::route('/create'),
            'edit' => Pages\EditChatRoom::route('/{record}/edit'),
        ];
    }
}
