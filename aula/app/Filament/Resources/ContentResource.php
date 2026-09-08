<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentResource\Pages;
use App\Models\Content;
use App\Models\Environment;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use UnitEnum;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static ?string $modelLabel = 'Contenido';

    protected static ?string $pluralModelLabel = 'Contenidos';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión Académica';

    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Contenido')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->dehydrated()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options(fn (Get $get): array => static::tipoOptions($get('environment_id')))
                            ->required()
                            ->native(false)
                            ->helperText(fn (Get $get, ?string $state): ?string => static::tipoHelper($get('environment_id'))),
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                Content::STATUS_DRAFT => 'Borrador',
                                Content::STATUS_SCHEDULED => 'Programado',
                                Content::STATUS_PUBLISHED => 'Publicado',
                                Content::STATUS_ARCHIVED => 'Archivado',
                            ])
                            ->default(Content::STATUS_DRAFT)
                            ->required()
                            ->native(false),
                    ])->columns(2),

                Section::make('Asignación')
                    ->schema([
                        Forms\Components\Select::make('environment_id')
                            ->label('Ambiente')
                            ->relationship('environment', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->live(),
                        Forms\Components\Select::make('area_id')
                            ->label('Área')
                            ->relationship('area', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\Select::make('teacher_id')
                            ->label('Guía')
                            ->relationship('teacher', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(3),

                Section::make('Detalle')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción corta')
                            ->rows(2)
                            ->nullable(),
                        Forms\Components\RichEditor::make('body')
                            ->label('Cuerpo')
                            ->nullable()
                            ->columnSpanFull(),
                    ])->collapsible(),

                Section::make('Multimedia')
                    ->schema([
                        Forms\Components\FileUpload::make('cover_image')
                            ->label('Imagen de portada')
                            ->image()
                            ->directory('content')
                            ->disk('public')
                            ->visibility('public')
                            ->nullable(),
                        Forms\Components\TextInput::make('video_url')
                            ->label('URL de video')
                            ->url()
                            ->nullable()
                            ->placeholder('https://youtube.com/watch?v=...'),
                    ])->columns(2)->collapsible(),

                Section::make('Publicación')
                    ->schema([
                        Forms\Components\Toggle::make('requires_evidence')
                            ->label('Requiere evidencia de la familia')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Fecha de publicación')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->nullable()
                            ->helperText('Si el estado es "Programado", se publicará automáticamente en esta fecha.'),
                    ])->columns(2)->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'experience' => 'success',
                        'reading' => 'info',
                        'video' => 'warning',
                        'task' => 'gray',
                        'document' => 'gray',
                        'gallery' => 'info',
                        'announcement' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'experience' => 'Experiencia',
                        'reading' => 'Lectura',
                        'video' => 'Video',
                        'task' => 'Tarea',
                        'document' => 'Documento',
                        'gallery' => 'Galería',
                        'announcement' => 'Anuncio',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'scheduled' => 'warning',
                        'published' => 'success',
                        'archived' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Borrador',
                        'scheduled' => 'Programado',
                        'published' => 'Publicado',
                        'archived' => 'Archivado',
                    }),
                Tables\Columns\TextColumn::make('environment.name')
                    ->label('Ambiente')
                    ->sortable(),
                Tables\Columns\TextColumn::make('area.name')
                    ->label('Área')
                    ->sortable(),
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Guía')
                    ->sortable(),
                Tables\Columns\IconColumn::make('requires_evidence')
                    ->label('Evidencia')
                    ->boolean(),
                Tables\Columns\TextColumn::make('evidence_count')
                    ->label('Envíos')
                    ->counts('evidence')
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publicado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'scheduled' => 'Programado',
                        'published' => 'Publicado',
                        'archived' => 'Archivado',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'experience' => 'Experiencia',
                        'reading' => 'Lectura',
                        'video' => 'Video',
                        'task' => 'Tarea',
                        'document' => 'Documento',
                        'gallery' => 'Galería',
                        'announcement' => 'Anuncio',
                    ]),
                Tables\Filters\SelectFilter::make('environment_id')
                    ->label('Ambiente')
                    ->relationship('environment', 'name'),
                Tables\Filters\SelectFilter::make('area_id')
                    ->label('Área')
                    ->relationship('area', 'name'),
                Tables\Filters\Filter::make('requires_evidence')
                    ->query(fn ($query) => $query->where('requires_evidence', true))
                    ->label('Requiere evidencia'),
                Tables\Filters\Filter::make('published')
                    ->query(fn ($query) => $query->where('status', 'published')->where('published_at', '<=', now()))
                    ->label('Publicados'),
                Tables\Filters\Filter::make('scheduled_pending')
                    ->query(fn ($query) => $query->where('status', 'scheduled')->where('published_at', '>', now()))
                    ->label('Programados pendientes'),
            ])
            ->recordActions([
                Action::make('vista_previa')
                    ->label('Vista previa')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Content $record) => URL::temporarySignedRoute(
                        'mi-escuelita.preview.show',
                        now()->addMinutes(30),
                        ['content' => $record->id],
                    ))
                    ->openUrlInNewTab(),
                Action::make('duplicar')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (Content $record): void {
                        $clone = $record->replicate();
                        $clone->title = $record->title.' (copia)';
                        $clone->slug = Str::slug($clone->title);
                        $clone->status = Content::STATUS_DRAFT;
                        $clone->published_at = null;
                        $clone->save();

                        foreach ($record->media as $medium) {
                            $extension = pathinfo($medium->path, PATHINFO_EXTENSION);
                            $newPath = 'content/'.Str::uuid().'.'.$extension;
                            Storage::disk('local')->copy($medium->path, $newPath);
                            $clone->media()->create([
                                'type' => $medium->type,
                                'path' => $newPath,
                                'original_name' => $medium->original_name,
                                'mime_type' => $medium->mime_type,
                                'size' => $medium->size,
                            ]);
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Duplicar contenido')
                    ->modalDescription('Se creará una copia de este contenido como borrador.'),
                Action::make('publicar')
                    ->label('Publicar')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('success')
                    ->visible(fn (Content $record): bool => $record->status !== Content::STATUS_PUBLISHED)
                    ->action(function (Content $record): void {
                        $record->update([
                            'status' => Content::STATUS_PUBLISHED,
                            'published_at' => now(),
                        ]);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Publicar contenido')
                    ->modalDescription('El contenido quedará visible para las familias del ambiente asignado.'),
                Action::make('despublicar')
                    ->label('Despublicar')
                    ->icon('heroicon-o-arrow-down-circle')
                    ->color('warning')
                    ->visible(fn (Content $record): bool => $record->status === Content::STATUS_PUBLISHED)
                    ->action(function (Content $record): void {
                        $record->update(['status' => Content::STATUS_DRAFT]);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Despublicar contenido')
                    ->modalDescription('El contenido dejará de estar visible para las familias.'),
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

    /**
     * Solo los ambientes "razonadoras" restringen los tipos disponibles:
     * lectura y tarea. El resto (absorbentes o sin ambiente) acepta todos.
     */
    public static function tipoOptions(?int $environmentId): array
    {
        $ambiente = $environmentId ? Environment::find($environmentId) : null;

        if ($ambiente?->stage === Environment::STAGE_RAZONADORAS) {
            return [
                Content::TYPE_READING => 'Lectura',
                Content::TYPE_TASK => 'Tarea',
            ];
        }

        return [
            Content::TYPE_EXPERIENCE => 'Experiencia',
            Content::TYPE_READING => 'Lectura',
            Content::TYPE_VIDEO => 'Video',
            Content::TYPE_TASK => 'Tarea',
            Content::TYPE_DOCUMENT => 'Documento',
            Content::TYPE_GALLERY => 'Galería',
            Content::TYPE_ANNOUNCEMENT => 'Anuncio',
        ];
    }

    public static function tipoHelper(?int $environmentId): ?string
    {
        $ambiente = $environmentId ? Environment::find($environmentId) : null;

        if ($ambiente?->stage === Environment::STAGE_RAZONADORAS) {
            return 'En este ambiente (razonadoras) solo se permiten lecturas y tareas.';
        }

        return null;
    }

    /**
     * Validación de servidor (además del form reactivo): en ambientes
     * "razonadoras" solo se permiten lectura y tarea, sin importar qué
     * envíe el cliente.
     */
    public static function esTipoPermitidoEnAmbiente(?int $environmentId, string $type): bool
    {
        $ambiente = $environmentId ? Environment::find($environmentId) : null;

        if ($ambiente?->stage !== Environment::STAGE_RAZONADORAS) {
            return true;
        }

        return in_array($type, [Content::TYPE_READING, Content::TYPE_TASK], true);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContent::route('/'),
            'create' => Pages\CreateContent::route('/create'),
            'edit' => Pages\EditContent::route('/{record}/edit'),
        ];
    }
}
