<?php

namespace App\Livewire\MiEscuelita;

use App\Http\Controllers\MiEscuelita\Concerns\ResolvesCurrentChild;
use App\Models\Area;
use App\Models\Content;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ExperienciaLista extends Component
{
    use ResolvesCurrentChild, WithPagination;

    public string $search = '';

    public ?int $filtroArea = null;

    public string $orden = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'filtroArea' => ['except' => null],
        'orden' => ['except' => 'desc'],
    ];

    public function mount(int|string|null $filtroArea = null): void
    {
        $this->filtroArea = ($filtroArea === null || $filtroArea === '') ? null : (int) $filtroArea;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroArea(): void
    {
        $this->resetPage();
    }

    public function toggleOrden(): void
    {
        $this->orden = $this->orden === 'desc' ? 'asc' : 'desc';
        $this->resetPage();
    }

    #[Computed]
    public function areas(): Collection
    {
        return Area::query()
            ->where('active', true)
            ->orderBy('order')
            ->get(['id', 'name', 'icon']);
    }

    public function filtrar(?int $areaId = null): void
    {
        $this->filtroArea = $areaId;
    }

    public function experiencias()
    {
        $child = $this->currentChild();

        return Content::query()
            ->published()
            ->where(function ($q) use ($child) {
                $q->whereNull('environment_id');
                if ($child) {
                    $q->orWhere('environment_id', $child->environment_id);
                }
            })
            ->when($this->search !== '', fn ($q) => $q->where('title', 'like', '%'.$this->search.'%'))
            ->when($this->filtroArea, fn ($q, $areaId) => $q->where('area_id', $areaId))
            ->with('area:id,name,icon')
            ->orderBy('published_at', $this->orden === 'desc' ? 'desc' : 'asc')
            ->paginate(10);
    }

    public function render(): View
    {
        return view('livewire.mi-escuelita.experiencia-lista');
    }
}
