<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use App\Filament\Resources\RoleResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\RoleResource\RelationManagers;

use App\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->columnSpanFull(),
                Select::make('permissions')
                    ->multiple()
                    ->relationship('permissions', 'name')
                    ->options(
                        Permission::all()->pluck('name', 'id')
                    )
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Name Permission')
                    ])
                    ->createOptionModalHeading('Add New Permission')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('permissions')
                    ->formatStateUsing(fn ($record) => $record->permissions->pluck('name')->join(', '))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('permissions', function (Builder $query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy(
                            Permission::select('name')
                                ->join('model_has_permissions', 'permissions.id', '=', 'model_has_permissions.permission_id')
                                ->whereColumn('model_has_permissions.model_id', 'users.id')
                                ->orderBy('name', $direction)
                                ->limit(1)
                        , $direction);
                    }),
            ])
            ->filters([
                //
                ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes()->where('name', '!=', 'Admin'));
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
