<?php

namespace App\Filament\Resources\ChildResource\RelationManagers;

use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Los padres no cuelgan del niño sino de su Family (ver Child::parents()),
 * así que este manager no usa un `relationship` estándar: intercepta el
 * guardado para crear el User contra la Family del niño en pantalla, y
 * filtra la tabla por esa misma Family.
 */
class ParentsRelationManager extends RelationManager
{
    protected static string $relationship = 'parents';

    protected static ?string $title = 'Padres';

    protected static ?string $modelLabel = 'Padre o madre';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Acceso del padre o madre')
                    ->description('Crea la cuenta con la que va a entrar a Mi Escuelita.')
                    ->icon('heroicon-o-key')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Correo electrónico')
                            ->prefixIcon('heroicon-o-envelope')
                            ->email()
                            ->required()
                            ->unique(table: 'users', ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->helperText('El padre puede cambiarla luego desde su perfil.')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('active')
                            ->label('Activo')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->icon('heroicon-o-envelope')
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Desde')
                    ->date('d/m/Y'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Registrar padre')
                    ->modalHeading('Registrar padre o madre')
                    ->using(function (array $data): Model {
                        /** @var \App\Models\Child $child */
                        $child = $this->getOwnerRecord();

                        return User::create([
                            ...$data,
                            'role' => User::ROLE_FAMILIA,
                            'family_id' => $child->family_id,
                        ]);
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Todavía no hay padres registrados')
            ->emptyStateDescription('Registrá el correo del papá o la mamá para que puedan entrar a Mi Escuelita.')
            ->emptyStateIcon('heroicon-o-key');
    }
}
