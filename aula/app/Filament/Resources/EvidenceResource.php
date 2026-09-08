<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EvidenceResource\Pages;
use App\Models\Evidence;
use App\Models\Observation;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class EvidenceResource extends Resource
{
    protected static ?string $model = Evidence::class;

    protected static ?string $modelLabel = 'Evidencia';

    protected static ?string $pluralModelLabel = 'Evidencias';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-inbox';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión Académica';

    protected static ?int $navigationSort = 13;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('child.name')
                    ->label('Niño')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('family.name')
                    ->label('Familia')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('content.title')
                    ->label('Experiencia')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Fecha de envío')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Evidence::STATUS_PENDING => 'warning',
                        Evidence::STATUS_SUBMITTED => 'info',
                        Evidence::STATUS_VIEWED => 'gray',
                        Evidence::STATUS_RESPONDED => 'success',
                        Evidence::STATUS_ARCHIVED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Evidence::STATUS_PENDING => 'Pendiente',
                        Evidence::STATUS_SUBMITTED => 'Enviada',
                        Evidence::STATUS_VIEWED => 'En revisión',
                        Evidence::STATUS_RESPONDED => 'Respondida',
                        Evidence::STATUS_ARCHIVED => 'Archivada',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('comment')
                    ->label('Comentario de la familia')
                    ->limit(50)
                    ->placeholder('—'),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->recordActions([
                Action::make('responder')
                    ->label('Responder')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->visible(fn (Evidence $record): bool => auth()->user()->can('respond', $record))
                    ->schema([
                        Forms\Components\Textarea::make('comentario_familia')
                            ->label('Comentario de la familia')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(3)
                            ->default(fn (Evidence $record): ?string => $record->comment),
                        Forms\Components\Textarea::make('respuesta')
                            ->label('Tu respuesta')
                            ->required()
                            ->rows(4)
                            ->helperText('La respuesta queda registrada como observación para el niño y su familia. No se usan calificaciones ni notas.'),
                    ])
                    ->action(function (Evidence $record, array $data): void {
                        Observation::create([
                            'child_id' => $record->child_id,
                            'content_id' => $record->content_id,
                            'teacher_id' => auth()->id(),
                            'observation' => $data['respuesta'],
                        ]);

                        $record->update([
                            'status' => Evidence::STATUS_RESPONDED,
                            'reviewed_at' => now(),
                        ]);
                    })
                    ->modalHeading('Responder evidencia')
                    ->successNotificationTitle('Respuesta guardada'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * La guía solo ve evidencias de SU ambiente: contenidos que ella
     * creó o niños de sus ambientes. El staff (admin/coordinación) ve
     * todas. Es la misma regla que EvidencePolicy::view.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user && $user->isGuia() && ! $user->isStaff()) {
            $query->where(function (Builder $q) use ($user) {
                $q->whereHas('content', fn (Builder $c) => $c->where('teacher_id', $user->id))
                    ->orWhereHas('child.environment', fn (Builder $e) => $e->where('teacher_id', $user->id));
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvidence::route('/'),
        ];
    }
}
