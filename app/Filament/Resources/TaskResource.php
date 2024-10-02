<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Task;
use Filament\Tables;
use App\Models\Council;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\CouncilPosition;
use Filament\Resources\Resource;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TaskResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TaskResource\RelationManagers;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                    // Forms\Components\Select::make('council_id')
                    // ->label('Council')
                    // ->options(Council::all()->pluck('name', 'id'))
                    // ->searchable()
                    // ->preload(),

                  Forms\Components\Select::make('council_position_id')
                ->label('User')
                ->options(CouncilPosition::all()->map(function($item){
                    return [
                        'name'=> $item->user->getFilamentName(),
                        'id'=>$item->id,
                    ];
                })->pluck('name', 'id'))
                ->preload()

                ->searchable(),
                Forms\Components\TextInput::make('title')
                    ->maxLength(255),
                    Forms\Components\RichEditor::make('task_details')
                    ->toolbarButtons([
                        'attachFiles',
                        'blockquote',
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'h2',
                        'h3',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'underline',
                        'undo',
                    ])->columnSpanFull(),
                Forms\Components\DateTimePicker::make('due_date'),
                Forms\Components\Select::make('status')
    ->options(TASK::STATUS_OPTIONS)
    ->native(false)
    ->default(Task::STATUS_TODO),
                Forms\Components\Toggle::make('is_lock'),
                Forms\Components\Toggle::make('is_done'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ViewColumn::make('council_position_id')->view('tables.columns.counsil-position')->label('User'),
                // Tables\Columns\TextColumn::make('approved_by_council_position_id')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('title')->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('task_details')->wrap()->markdown()
                    ->searchable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_lock')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_done')
                    ->boolean(),
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
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
