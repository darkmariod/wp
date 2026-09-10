<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Familias';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Datos del Usuario')
                    ->description('Quién es y qué rol cumple dentro de la plataforma.')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Forms\Components\FileUpload::make('photo_path')
                            ->label('Foto de perfil')
                            ->image()
                            ->disk('public')
                            ->directory('avatars')
                            ->imageEditor()
                            ->circleCropper()
                            ->maxSize(1024)
                            ->nullable()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Correo electrónico')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('role')
                            ->label('Rol')
                            ->options([
                                User::ROLE_ADMINISTRADOR => 'Administrador',
                                User::ROLE_COORDINACION => 'Coordinación',
                                User::ROLE_GUIA => 'Guía',
                                User::ROLE_FAMILIA => 'Familia',
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('active')
                            ->label('Activo')
                            ->default(true),
                    ])->columns(2),

                Section::make('Contraseña')
                    ->description('Dejala vacía al editar para no cambiarla.')
                    ->icon('heroicon-o-key')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255),
                    ])->collapsible(),

                Section::make('Vínculos')
                    ->description('Solo aplica a usuarios con rol Familia.')
                    ->icon('heroicon-o-link')
                    ->schema([
                        Forms\Components\Select::make('family_id')
                            ->label('Familia')
                            ->relationship('family', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Rol')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'administrador' => 'danger',
                        'coordinacion' => 'warning',
                        'guia' => 'success',
                        'familia' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'administrador' => 'Administrador',
                        'coordinacion' => 'Coordinación',
                        'guia' => 'Guía',
                        'familia' => 'Familia',
                    }),
                Tables\Columns\IconColumn::make('active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('family.name')
                    ->label('Familia')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Rol')
                    ->options([
                        'administrador' => 'Administrador',
                        'coordinacion' => 'Coordinación',
                        'guia' => 'Guía',
                        'familia' => 'Familia',
                    ]),
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Activo'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
