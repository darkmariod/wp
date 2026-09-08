<?php

namespace App\Livewire\MiEscuelita;

use App\Models\Area;
use App\Models\Evidence;
use App\Models\Observation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Historial extends Component
{
    use WithPagination;

    /**
     * Búsqueda por título de la experiencia.
     */
    public string $search = '';

    /**
     * Filtro por área: null = todas.
     */
    public ?int $filtroArea = null;

    /**
     * Filtro por estado: 'todas', o una de las constantes de
     * Evidence::STATUS_*.
     */
    public string $filtro = 'todas';

    /**
     * Orden por fecha de envío: 'desc' o 'asc'.
     */
    public string $orden = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'filtroArea' => ['except' => null],
        'filtro' => ['except' => 'todas'],
        'orden' => ['except' => 'desc'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroArea(): void
    {
        $this->resetPage();
    }

    public function updatedFiltro(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function filtros(): array
    {
        return [
            'todas' => 'Todas',
            Evidence::STATUS_PENDING => 'Pendientes',
            Evidence::STATUS_SUBMITTED => 'Enviadas',
            Evidence::STATUS_VIEWED => 'En revisión',
            Evidence::STATUS_RESPONDED => 'Con respuesta',
            Evidence::STATUS_ARCHIVED => 'Archivadas',
        ];
    }

    #[Computed]
    public function areas(): Collection
    {
        return Area::query()
            ->where('active', true)
            ->orderBy('order')
            ->get(['id', 'name']);
    }

    public function toggleOrden(): void
    {
        $this->orden = $this->orden === 'desc' ? 'asc' : 'desc';
        $this->resetPage();
    }

    /**
     * Tabla con búsqueda, filtros por área y estado, orden por fecha y
     * paginación (10 por página). La paginación se resuelve en un método
     * común y corriente: no depende de estado dentro de un #[Computed].
     */
    public function evidencias()
    {
        $familyId = Auth::user()->family_id;

        $query = Evidence::query()
            ->where('family_id', $familyId)
            ->when($this->search !== '', fn ($q) => $q->whereHas(
                'content',
                fn ($content) => $content->where('title', 'like', '%'.$this->search.'%'),
            ))
            ->when($this->filtroArea, fn ($q, $areaId) => $q->whereHas('content', fn ($content) => $content->where('area_id', $areaId)))
            ->when($this->filtro !== 'todas', fn ($q) => $q->where('status', $this->filtro))
            ->with([
                'content:id,title,slug,area_id',
                'content.area:id,name',
                'child:id,name',
            ])
            ->orderBy('submitted_at', $this->orden === 'desc' ? 'desc' : 'asc');

        $paginadas = $query->paginate(10);

        // Observation se guarda contra (child, content), no contra la
        // evidencia. Se cargan TODAS las observaciones de estos
        // niños/contenidos en UNA query y se indexan por par — evita el
        // N+1 de buscar dentro del map().
        $observaciones = $paginadas->isEmpty()
            ? collect()
            : Observation::query()
                ->whereIn('child_id', $paginadas->pluck('child_id')->unique())
                ->whereIn('content_id', $paginadas->pluck('content_id')->unique())
                ->latest('id')
                ->get()
                ->keyBy(fn ($o) => $o->child_id.'-'.$o->content_id);

        return $paginadas->through(function ($e) use ($observaciones) {
            $observacion = $observaciones->get($e->child_id.'-'.$e->content_id);

            return [
                'id' => $e->id,
                'experiencia' => $e->content->title,
                'area' => $e->content->area?->name,
                'estado' => $e->status,
                'enviada' => $e->submitted_at,
                'observacion' => $observacion?->observation,
            ];
        });
    }

    public function render(): View
    {
        return view('livewire.mi-escuelita.historial');
    }
}
