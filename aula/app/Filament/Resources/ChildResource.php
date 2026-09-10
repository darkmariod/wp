<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChildResource\Pages;
use App\Models\Child;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Filament\Resources\ChildResource\RelationManagers\ParentsRelationManager;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class ChildResource extends Resource
{
    protected static ?string $model = Child::class;

    protected static ?string $modelLabel = 'Niño';

    protected static ?string $pluralModelLabel = 'Niños';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-face-smile';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Familias';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Datos del Niño')
                    ->description('El nombre, la fecha de nacimiento y una foto para reconocerlo en el panel.')
                    ->icon('heroicon-o-face-smile')
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
                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Fecha de nacimiento')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'active' => 'Activo',
                                'inactive' => 'Inactivo',
                            ])
                            ->default('active')
                            ->required(),
                    ])->columns(3),

                Section::make('Vínculos')
                    ->description('A qué familia pertenece y en qué aula está.')
                    ->icon('heroicon-o-link')
                    ->schema([
                        Forms\Components\Select::make('family_id')
                            ->label('Familia')
                            ->relationship('family', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre de la familia')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->maxLength(255),
                            ])
                            ->createOptionModalHeading('Nueva familia')
                            ->helperText('¿Es el primer niño de esta familia? Creala sin salir de esta pantalla.'),
                        Forms\Components\Select::make('environment_id')
                            ->label('Ambiente')
                            ->relationship('environment', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn (Child $record) => 'data:image/svg+xml;utf8,'.rawurlencode("<svg xmlns='http://www.w3.org/2000/svg' width='64' height='64'><rect width='64' height='64' rx='32' fill='#126333'/><text x='50%' y='54%' font-family='DM Sans, sans-serif' font-size='24' fill='white' text-anchor='middle' dominant-baseline='middle'>{$record->avatarInitials()}</text></svg>"))
                    ->grow(false),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('family.name')
                    ->label('Familia')
                    ->sortable(),
                Tables\Columns\TextColumn::make('environment.name')
                    ->label('Ambiente')
                    ->sortable(),
                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Nacimiento')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray')
                    ->formatStateUsing(fn (string $state): string => $state === 'active' ? 'Activo' : 'Inactivo'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('environment_id')
                    ->label('Ambiente')
                    ->relationship('environment', 'name'),
                Tables\Filters\SelectFilter::make('family_id')
                    ->label('Familia')
                    ->relationship('family', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                    ]),
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
            ParentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChildren::route('/'),
            'create' => Pages\CreateChild::route('/create'),
            'edit' => Pages\EditChild::route('/{record}/edit'),
        ];
    }
}
