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
    public $showVentas = 0;
    public $searchCliente = '';
    public $saldos = [];
    public $idLocal;
    public $ver_venta;

    public function mount()
    {

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
        $query = Persona::where('id_local', $this->idLocal);  // Comienzas construyendo la consulta con la restricción de local.

        if ($this->searchCliente) {
            // Aplica las condiciones de búsqueda dentro de un subgrupo para asegurar el contexto correcto.
            $query->where(function ($q) {
                $q->where('idpersona', 'like', '%' . $this->searchCliente . '%')
                    ->orWhere('nombre', 'like', '%' . $this->searchCliente . '%');
            });
        }

        $this->personas = $query->get();  // Ejecutas la consulta y guardas los resultados en $this->personas.
    }

    public function agregarCliente($id)
    {
        $clienteSe = Persona::find($id);
        if ($clienteSe) {
            $this->clienteSeleccionado = $clienteSe;

            $this->idpersona = $clienteSe->idpersona;
            $this->nombre_cliente = $clienteSe->nombre;
            $this->searchCliente = '';
            // Obtiene las ventas con saldos y precarga las relaciones necesarias.
            $this->ventasConSaldos = Venta::with('detalles.producto', 'persona')
                ->where('idcliente', $this->idpersona)
                ->where('saldo', '>', 0)
                ->get();

            $this->totalSaldos = $this->ventasConSaldos->sum('saldo');
        } else {
            // Opcional: manejo de error si el cliente no se encuentra
            $this->addError('clienteNotFound', 'El cliente no se encuentra en la base de datos.');
        }
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
    public function ver($id)
    {
        $this->ver_venta = Venta::with('detalles.producto', 'persona')->findOrFail($id);
        $this->showVentas = true;
    }
}
