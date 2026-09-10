<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnvironmentResource\Pages;
use App\Models\Environment;
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

class EnvironmentResource extends Resource
{
    protected static ?string $model = Environment::class;

    protected static ?string $modelLabel = 'Ambiente';

    protected static ?string $pluralModelLabel = 'Ambientes';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión Académica';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Datos del Ambiente')
                    ->description('El aula, su rango de edad y quién la guía.')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('age_range')
                            ->label('Rango de edad')
                            ->maxLength(255)
                            ->placeholder('Ej: 3-4 años'),
                        Forms\Components\Select::make('teacher_id')
                            ->label('Guía')
                            ->relationship('teacher', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->getOptionLabelFromRecordUsing(fn (User $user) => "{$user->name} ({$user->email})"),
                        Forms\Components\Toggle::make('active')
                            ->label('Activo')
                            ->default(true),
                        Forms\Components\TextInput::make('order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),

                Section::make('Descripción e Imagen')
                    ->description('Lo que ven las familias al conocer este ambiente.')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->nullable(),
                        Forms\Components\FileUpload::make('image')
                            ->label('Imagen')
                            ->image()
                            ->directory('environments')
                            ->nullable()
                            ->columnSpanFull(),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('Orden')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('age_range')
                    ->label('Edad'),
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Guía')
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('children.count')
                    ->label('Niños')
                    ->counts('children'),
            ])
            ->filters([
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
            'index' => Pages\ListEnvironments::route('/'),
            'create' => Pages\CreateEnvironment::route('/create'),
            'edit' => Pages\EditEnvironment::route('/{record}/edit'),
        ];
    }
}
