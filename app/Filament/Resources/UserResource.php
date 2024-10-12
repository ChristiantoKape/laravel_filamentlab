<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use App\Filament\Resources\UserResource\Pages;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required()
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),
                        Forms\Components\DateTimePicker::make('email_verified_at'),
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('roles')
                                    ->relationship('roles', 'name')
                                    ->options(
                                        Role::all()->pluck('name', 'id')
                                    )
                                    ->default(fn (?Model $record) => $record ? $record->roles->pluck('id')->first() : null)
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        if ($state) {
                                            $role = Role::findById($state);
                                            $permissions = $role->permissions->pluck('id')->toArray();
                                            $set('permissions', $permissions);
                                        } else {
                                            $set('permissions', []);
                                        }
                                    }),
                                Forms\Components\Select::make('permissions')
                                    ->multiple()
                                    ->label('Permissions')
                                    ->relationship('permissions', 'name')
                                    ->options(function (Get $get) {
                                        $roleId = $get('roles');
                                        if (!$roleId) {
                                            return Permission::pluck('name', 'id')->toArray();
                                        }

                                        $role = Role::where('id', $roleId)->first();

                                        $roleName = strtolower($role->name);
                                        $permissions = Permission::where('name', 'like', '%' . $roleName . '%')
                                            ->pluck('name', 'id');

                                        return $permissions;
                                    })
                                    ->hidden(fn (Get $get) => !$get('roles'))
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles')
                    ->formatStateUsing(fn ($record) => $record->roles->pluck('name')->join(', ')),
                Tables\Columns\TextColumn::make('permissions')
                    ->formatStateUsing(fn ($record) => $record->permissions->pluck('name')->join(', ')),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
