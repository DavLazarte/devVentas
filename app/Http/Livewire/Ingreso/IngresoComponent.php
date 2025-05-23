<?php

namespace App\Http\Livewire\Ingreso;

use App\Models\Ingreso;
use App\Models\Persona;
use App\Models\Venta;
use Livewire\Component;


class IngresoComponent extends Component
{
    public $personas, $monto, $ventasConSaldos, $saldo, $descripcion, $estado, $busqueda, $idpersona, $clienteSeleccionado, $nombre_cliente, $ingreso_id, $totalSaldos;
    public $isOpen = 0;
    public $modoEdit = 0;
    public $searchCliente = '';
    public $saldos = [];
    public $idLocal;

    public function mount(){

        $this->idLocal = auth()->user()->local->id;
    }


    public function render()
    {
        

        $this->filtrarCliente();

        $query = Ingreso::where('id_local', $this->idLocal);

        if ($this->busqueda) {
            $query->where('idpersona', 'like', '%' . $this->busqueda . '%')
                ->orWhere('nombre', 'like', '%' . $this->busqueda . '%');
        }

        $ingreso = $query->with('cliente')->paginate(10);



        return view('livewire.ingreso.ingreso-component', [
            'ingreso' => $ingreso,
            'personas' => $this->personas
        ]);
    }
    public function filtrarCliente()
    {
        $this->personas = Persona::where('id_local', $this->idLocal);

        if ($this->searchCliente) {
            $this->personas->where('idpersona', 'like', '%' . $this->searchCliente . '%')
                ->orWhere('nombre', 'like', '%' . $this->searchCliente . '%');
        }

        $this->personas = $this->personas->get();
    }
    public function agregarCliente($id)
    {
        $clienteSe = Persona::find($id);

        $this->clienteSeleccionado = $clienteSe;

        $this->idpersona = $clienteSe->idpersona;
        $this->nombre_cliente = $clienteSe->nombre;
        $this->searchCliente = '';
        $this->ventasConSaldos = Venta::where('idcliente', $this->idpersona)
            ->where('id_local', $this->idLocal)
            ->where('saldo', '>', 0)
            ->get();

        $this->totalSaldos = $this->ventasConSaldos->sum('saldo');
    }
    public function crear()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->modoEdit = false;
    }

    private function resetInputFields()
    {
        $this->monto = '';
        $this->descripcion = '';
        $this->estado = '';
        $this->idpersona = '';
        $this->saldo = '';
        $this->nombre_cliente = '';
        $this->clienteSeleccionado = '';
        $this->totalSaldos = '';
    }
    public function guardar()
    {

        $this->validate([
            'idpersona' => 'required',
            'monto' => 'required',
            'saldo' => 'required',
        ]);

        foreach ($this->ventasConSaldos as $venta) {
            $montoVenta = $this->saldos[$venta->id] ?? 0;

            try {
                $ventaModel = Venta::findOrFail($venta->id);
                if ($montoVenta !== $ventaModel->saldo) {
                    $montoRedondeado = round($montoVenta, 2);
                    $ventaModel->saldo = $montoRedondeado;
                    $ventaModel->save();
                }
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                // Maneja el caso donde no se encuentra el modelo
                session()->flash('error', 'Error al encontrar la venta con ID ' . $venta->id);
            } catch (\Exception $e) {
                // Maneja otros tipos de excepciones
                session()->flash('error', 'Error al guardar la venta: ' . $e->getMessage());
            }
        }

        Ingreso::updateOrCreate(['id_ingreso' => $this->ingreso_id], [
            'idpersona' => $this->idpersona,
            'monto' => $this->monto,
            'descripcion' => $this->descripcion,
            'saldo' => $this->saldo,
            'id_local' => $this->idLocal,
            // 'estado' => $this->estado,
        ]);

        session()->flash(
            'message',
            $this->ingreso_id ? 'Ingreso actualizado exitosamente.' : 'Pago creado exitosamente.'
        );

        $this->closeModal();
        $this->resetInputFields();
    }


    public function editar($id)
    {
        $this->modoEdit = true;
        $ingreso = Ingreso::findOrFail($id);
        $this->ingreso_id = $id;
        $this->idpersona = $ingreso->idpersona;
        $this->monto = $ingreso->monto;
        $this->descripcion = $ingreso->descripcion;
        $this->saldo = $ingreso->saldo;

        $this->openModal();
    }

    public function borrar($id)
    {
        Ingreso::find($id)->delete();
        session()->flash('message', 'ingreso eliminado exitosamente.');
    }
    public function distribuirPago()
    {
        if ($this->totalSaldos > 0) {
            $montoPago = $this->monto;

            foreach ($this->ventasConSaldos as $venta) {
                $montoVenta = min($venta->saldo, $montoPago);

                // Almacena el resto (lo que falta para cubrir el total) dinámicamente en el array $saldos
                $this->saldos[$venta->id] = $venta->saldo - $montoVenta;

                // Actualiza el montoPago
                $montoPago -= $montoVenta;

                // Si ya no queda monto por distribuir, sal del bucle
                // if ($montoPago <= 0) {
                //     break;
                // }
            }
            $nuevo_saldo = $this->totalSaldos - $this->monto;
            $this->saldo = round($nuevo_saldo, 2);

            // Limpia el campo monto después de distribuir el pago
            // $this->monto = 0;
        }
    }
}
