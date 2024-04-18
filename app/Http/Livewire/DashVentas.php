<?php

namespace App\Http\Livewire;

use App\Models\Ingreso;
use App\Models\Salida;
use App\Models\Venta;
use Livewire\Component;

class DashVentas extends Component
{
    public $cajas, $caja_id, $monto_apertura, $monto_cierre, $fecha_apertura, $fecha_cierre, $ingresos, $salidas, $ventas_efectivo, $ventas_tarjeta, $ventas_transferencia, $monto_total_ventas, $estado, $busqueda;


    public function render()
    {
        // Obtener el ID del local del usuario actual
        $idLocal = auth()->user()->local->id;

        // Filtrar ventas, ingresos y salidas por local
        $this->monto_total_ventas = Venta::where('id_local', $idLocal)
            ->whereDate('created_at', now()->toDateString())
            ->sum('pago');

        $this->ventas_efectivo = Venta::where('id_local', $idLocal)
            ->whereDate('created_at', now()->toDateString())
            ->whereIn('forma_de_pago', ['efectivo'])
            ->sum('pago');

        $this->ventas_transferencia = Venta::where('id_local', $idLocal)
            ->whereDate('created_at', now()->toDateString())
            ->whereIn('forma_de_pago', ['transferencia'])
            ->sum('pago');

        $this->ventas_tarjeta = Venta::where('id_local', $idLocal)
            ->whereDate('created_at', now()->toDateString())
            ->whereIn('forma_de_pago', ['cuenta_corriente'])
            ->sum('pago');

        $this->ingresos = Ingreso::where('id_local', $idLocal)
            ->whereDate('created_at', now()->toDateString())
            ->sum('monto');

        $this->salidas = Salida::where('id_local', $idLocal)
            ->whereDate('created_at', now()->toDateString())
            ->sum('monto');

        // Actualizar la variable monto_cierre sumando total_ventas, pagos, ingresos y salidas
        $this->monto_cierre = $this->ventas_efectivo - $this->salidas + $this->ingresos + $this->monto_apertura;

        return view('livewire.dash-ventas');
    }
}
