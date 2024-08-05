<?php

namespace App\Http\Livewire\Caja;

use App\Models\CierreCaja;
use App\Models\Ingreso;
use App\Models\Salida;
use App\Models\Venta;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use DateTime;

class AdminCajas extends Component
{
    public $cajas, $caja_id, $monto_apertura, $monto_cierre, $fecha_apertura, $fecha_cierre, $ingresos, $salidas, $ventas_efectivo, $ventas_tarjeta, $ventas_transferencia, $estado, $busqueda;
    public $isOpen = 0;
    public $isEdit = 0;
    // variables nuevas
    public $monto_total_ventas = 0;
    public $monto_cierre_real = 0;

    protected $listeners = [
        'editarCaja' => 'editar',
        'openModal' => 'openModal'
    ];

    public function mount()
    {
        $this->actualizarCaja();
    }
    public function render()
    {
        return view('livewire.caja.admin-cajas');
    }

    public function crear()
    {
        // Obtener el valor de monto_cierre del último registro
        $ultimoCierre = CierreCaja::latest()->first();

        // Asignar el valor a la propiedad $monto_cierre
        $this->resetInputFields();
        $this->estado = 'abierta';
        // $this->monto_apertura = $ultimoCierre ? $ultimoCierre->monto_cierre : 0;
        $this->emit('refreshDatatableCajas');
        $this->openModal();

    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }



    public function guardar()
    {
        // dd($this->monto_apertura );
        try {
            $this->validate([
                'fecha_apertura' => 'required',
                // 'estado' => 'required|in:abierta,cerrada',
            ]);
            // Convertir la fecha a un objeto DateTime
            $fechaApertura = new DateTime($this->fecha_apertura);
            $idLocal = auth()->user()->local->id;

            // Verificar y asignar null a fecha_cierre si está vacío
            $fechaCierre = empty($this->fecha_cierre) ? null : new DateTime($this->fecha_cierre);
            CierreCaja::updateOrCreate(['id' => $this->caja_id], [
                'fecha_apertura' => $fechaApertura,
                'fecha_cierre' => $fechaCierre,
                'monto_apertura' => $this->monto_apertura,
                'monto_total_ventas' => $this->monto_total_ventas,
                'monto_cierre' => $this->monto_cierre,
                'monto_cierre_real' => $this->monto_cierre_real,
                'ventas_efectivo' => $this->ventas_efectivo,
                'ventas_transferencia' => $this->ventas_transferencia,
                'ventas_tarjeta' => $this->ventas_tarjeta,
                'ingresos' => $this->ingresos,
                'salidas' => $this->salidas,
                'estado' => $this->estado,
                'id_local' => $idLocal,
            ]);

            session()->flash(
                'message',
                $this->caja_id ? 'Caja Cerrada exitosamente.' : 'Caja abierta exitosamente.'
            );
            $this->emit('refreshDatatableCajas');
            $this->closeModal();
            $this->resetInputFields();
        } catch (\Exception $e) {
            // Manejar la excepción (por ejemplo, enviarla al log)
            Log::error('Error al guardar caja: ' . $e->getMessage());
            // Puedes registrar más detalles si lo deseas:
            Log::error($e);

            // Puedes personalizar el manejo de errores según tus necesidades
            session()->flash('error', 'Hubo un error al procesar la solicitud.');
        }


        $this->closeModal();
        $this->resetInputFields();
    }

    public function editar($id)
    {

        $this->isEdit = true;
        $cajaAbierta = CierreCaja::findOrFail($id);
        $this->caja_id = $id;
        $this->fecha_apertura = $cajaAbierta->fecha_apertura;
        $this->fecha_cierre = $cajaAbierta->fecha_cierre;
        $this->monto_apertura = $cajaAbierta->monto_apertura;
        $this->monto_total_ventas = $cajaAbierta->monto_total_ventas;
        $this->monto_cierre = $cajaAbierta->monto_cierre;
        $this->monto_cierre_real = $cajaAbierta->monto_cierre_real;
        $this->ventas_efectivo = $cajaAbierta->ventas_efectivo;
        $this->ventas_transferencia = $cajaAbierta->ventas_transferencia;
        $this->ventas_tarjeta = $cajaAbierta->ventas_tarjeta;
        $this->ingresos = $cajaAbierta->ingresos;
        $this->salidas = $cajaAbierta->salidas;
        $this->estado = $cajaAbierta->estado;

        $this->openModal();
        $this->actualizarCaja();
    }

    public function borrar($id)
    {
        CierreCaja::find($id)->delete();
        session()->flash('message', 'Caja eliminada exitosamente.');
        $this->emit('refreshDatatableCajas');
    }
    public function actualizarCaja()
    {
        $this->monto_total_ventas = Venta::whereDate('created_at', now()->toDateString())
            ->sum('total_venta');
        // dd($this->monto_total_ventas);        // Sumar solo los pagos (ventas en efectivo y transferencia)
        $this->ventas_efectivo = Venta::whereDate('created_at', now()->toDateString())
            ->whereIn('forma_de_pago', ['efectivo'])
            ->sum('pago');
        $this->ventas_transferencia = Venta::whereDate('created_at', now()->toDateString())
            ->whereIn('forma_de_pago', ['transferencia'])
            ->sum('pago');
        $this->ventas_tarjeta = Venta::whereDate('created_at', now()->toDateString())
            ->whereIn('forma_de_pago', ['tarjeta'])
            ->sum('pago');
        $this->ingresos = Ingreso::whereDate('created_at', now()->toDateString())
            ->sum('monto');
        $this->salidas = Salida::whereDate('created_at', now()->toDateString())
            ->sum('monto');

       // Actualizar la variable new_monto_cierre sumando total_ventas, pagos, ingresos y salidas
       $this->monto_cierre = $this->ventas_efectivo - $this->salidas  + $this->ingresos + $this->monto_apertura;
    }
    private function resetInputFields()
    {
        $this->fecha_apertura = '';
        $this->fecha_cierre = '';
        $this->monto_apertura = 0;
        $this->monto_cierre = 0;
        $this->monto_cierre_real = 0;
        $this->ventas_efectivo = 0;
        $this->ventas_transferencia = 0;
        $this->monto_total_ventas = 0;
        $this->ventas_tarjeta = 0;
        $this->ingresos = 0;
        $this->salidas = 0;
        $this->estado = '';
        $this->caja_id = '';
        $this->isEdit = 0;

    }

}
