<?php

namespace App\Http\Livewire\Salida;

use App\Models\Compra;
use App\Models\Persona;
use App\Models\Salida;
use Livewire\Component;

class SalidaComponent extends Component
{
    public $personas, $monto, $tipo_salida = 'pago_servicio', $descripcion, $estado, $busqueda, $clienteSeleccionado, $nombre_cliente, $salida_id, $totalSaldos, $ventasConSaldos;
    public $isOpen = 0;
    public $modoEdit = 0;
    public $searchCliente = '';
    public $idpersona = null;
    public $saldos = [];
    public  $saldo = 0.0;


    public function render()
    {
        $idLocal = auth()->user()->local->id;

        $this->filtrarCliente();

        $query = Salida::where('id_local', $idLocal);

        if ($this->busqueda) {
            $query->where('idpersona', 'like', '%' . $this->busqueda . '%')
                ->orWhere('nombre', 'like', '%' . $this->busqueda . '%')
                ->orWhere('tipo_venta', 'like', '%' . $this->busqueda . '%');
        }

        $salida = $query->with('persona')->paginate(10);



        return view('livewire.salida.salida-component', [
            'salida' => $salida,
            'personas' => $this->personas
        ]);
    }
    public function filtrarCliente()
    {
        $idLocal = auth()->user()->local->id;
        $this->personas = Persona::where('id_local', $idLocal);

        if ($this->searchCliente) {
            $this->personas->where('idpersona', 'like', '%' . $this->searchCliente . '%')
                ->orWhere('nombre', 'like', '%' . $this->searchCliente . '%');
        }

        $this->personas = $this->personas->get();
    }
    public function agregarCliente($id)
    {
        $idLocal = auth()->user()->local->id;
        $clienteSe = Persona::find($id);

        $this->clienteSeleccionado = $clienteSe;

        $this->idpersona = $clienteSe->idpersona;
        $this->nombre_cliente = $clienteSe->nombre;
        $this->searchCliente = '';
        $this->ventasConSaldos = Compra::where('idpersona', $this->idpersona)
            ->where('id_local', $idLocal)
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


    // public function restarSaldo()
    // {
    //     $nuevo_saldo = $this->saldo - $this->monto;
    //     $this->saldo = round($nuevo_saldo, 2);
    // }
    public function guardar()
    {
        // $this->validate([
        //     'monto' => 'required',
        // ]);
        $idLocal = auth()->user()->local->id;

        if ($this->ventasConSaldos && $this->ventasConSaldos->isNotEmpty()) {
            foreach ($this->ventasConSaldos as $venta) {
                $montoVenta = $this->saldos[$venta->id] ?? 0;

                try {
                    $ventaModel = Compra::findOrFail($venta->id);
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
        }

        Salida::updateOrCreate(['idsalida' => $this->salida_id], [
            'tipo_salida' => $this->tipo_salida,
            'idpersona' => $this->idpersona,
            'monto' => $this->monto,
            'descripcion' => $this->descripcion,
            'saldo' => $this->saldo,
            'id_local' => $idLocal,
            // 'estado' => $this->estado,
        ]);

        session()->flash(
            'message',
            $this->salida_id ? 'Salida actualizado exitosamente.' : 'Salida creada exitosamente.'
        );

        $this->closeModal();
        $this->resetInputFields();
    }

    public function editar($id)
    {
        $this->modoEdit = true;
        $salida = Salida::findOrFail($id);
        $this->salida_id = $id;
        $this->tipo_salida = $salida->tipo_salida;
        $this->idpersona = $salida->idpersona;
        $this->monto = $salida->monto;
        $this->descripcion = $salida->descripcion;
        $this->saldo = $salida->saldo;
        // $this->precio_unitario = floatval($ingreso->precio_unitario);
        // $this->estado = $ingreso->estado;

        $this->openModal();
    }

    public function borrar($id)
    {
        Salida::find($id)->delete();
        session()->flash('message', 'Salida eliminada exitosamente.');
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
    private function resetInputFields()
    {
        $this->monto = '';
        $this->descripcion = '';
        $this->estado = '';
        $this->idpersona = null;
        $this->saldo = 0.0;
        $this->nombre_cliente = '';
        $this->clienteSeleccionado = '';
        $this->totalSaldos = '';
    }
}
