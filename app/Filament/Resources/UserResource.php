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
use Filament\Forms\Components\TextInput;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
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
                                    ->multiple()
                                    ->relationship('roles', 'name')
                                    ->options(
                                        Role::all()->pluck('name', 'id')
                                    )
                                    ->default(fn (?Model $record) => $record ? $record->roles->pluck('id')->toArray() : [])
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, ?Model $record) {
                                        if (empty($state)) {
                                            // Jika roles kosong, hapus semua permissions
                                            $set('permissions', []);
                                            if ($record) {
                                                $record->permissions()->detach();
                                            }
                                            return;
                                        }
                                        
                                        $roles = Role::whereIn('id', $state)->get();
                                        $permissions = $roles->flatMap->permissions->pluck('id')->unique()->toArray();
                                        $set('permissions', $permissions);
                                    }),
                                Forms\Components\Select::make('permissions')
                                    ->multiple()
                                    ->label('Direct Permission')
                                    ->relationship('permissions', 'name')
                                    ->options(function (Get $get) {
                                        $roleIds = $get('roles');
                                        if (empty($roleIds)) {
                                            return Permission::pluck('name', 'id')->toArray();
                                        }
                                    
                                        $roles = Role::whereIn('id', $roleIds)->get();
                                        $roleNames = $roles->pluck('name')->map(fn($name) => strtolower($name))->toArray();
                                    
                                        return Permission::where(function ($query) use ($roleNames) {
                                            foreach ($roleNames as $roleName) {
                                                $query->orWhere('name', 'like', '%' . $roleName . '%');
                                            }
                                        })->pluck('name', 'id')->toArray();
                                    })
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('Permission Name')
                                    ])
                                    ->createOptionModalHeading('Add New Permission')
                                    ->hidden(fn (Get $get) => empty($get('roles')))
                                    ->dehydrated(fn (Get $get) => !empty($get('roles')))
                                    ->preload()
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles')
                    ->formatStateUsing(fn ($record) => $record->roles->pluck('name')->join(', '))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('roles', function (Builder $query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy(
                            Role::select('name')
                                ->join('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
                                ->whereColumn('model_has_roles.model_id', 'users.id')
                                ->orderBy('name', $direction)
                                ->limit(1)
                        , $direction);
                    }),
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
            // ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes()->where('name', '!=', 'Admin'));
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
