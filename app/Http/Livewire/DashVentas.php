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
        return view('livewire.dash-ventas');
    }
}
