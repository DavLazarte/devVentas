<?php

namespace App\Http\Livewire\Persona;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\Persona;
use Illuminate\Database\Eloquent\Builder;

class PersonasTable extends DataTableComponent
{
    public $mostrarPagos;
    protected $model = Persona::class;

    protected $listeners = ['refreshTablePersonas' => '$refresh', 'pagoGuardado' => 'handlePagoGuardado'];

    public function builder(): Builder
    {
        $idLocal = auth()->user()->local->id;
        return Persona::query()->where('id_local', $idLocal);
    }

    public function configure(): void
    {
        $this->setPrimaryKey('idpersona');
        $this->setSearchEnabled(); // Habilitar la búsqueda
    }

    public function columns(): array
    {
        return [
            Column::make("Id", "idpersona")
                ->sortable(),
            Column::make("Tipo", "tipo_persona")
                ->sortable(),
            Column::make("Nombre", "nombre")
                ->sortable()
                ->searchable(),
            Column::make("Direccion", "direccion")
                ->sortable(),
            Column::make("Telefono", "telefono")
                ->sortable(),
            Column::make("Mail", "mail")
                ->sortable(),
            Column::make("Estado", "estado")
                ->sortable(),
            Column::make("Acciones")->label(
                fn($row, Column $column) => view('livewire.persona.actions', ['row' => $row])
            ),
        ];
    }

    public function editar($id)
    {
        $persona = Persona::findOrFail($id);
        $this->emit('editarPersona', $persona->idpersona);
    }

    public function borrar($id)
    {
        Persona::find($id)->delete();
        session()->flash('message', 'Persona eliminada exitosamente.');
    }
    public function mostrarPagos($idpersona)
    {
        $this->emit('verPagos', $idpersona);  // Emite el evento 'verPagos' con el idpersona
    }

    public function handlePagoGuardado()
    {
        $this->emit('NoVerPagos');
    }
}
