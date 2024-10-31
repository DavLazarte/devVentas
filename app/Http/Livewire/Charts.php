<?php

namespace App\Http\Livewire;

use App\Models\DetalleVenta;
use Livewire\Component;
use App\Models\Venta;
use Carbon\Carbon;

class Charts extends Component
{
    public $ventasPorMes = [];
    public $productosMasVendidos = [];
    public $tiposDePagos = [];

    public function mount()
    {
        // Ventas por mes
        $this->ventasPorMes = Venta::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as mes, COUNT(*) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->pluck('total', 'mes')
            ->toArray();

        // Productos más vendidos
        $this->productosMasVendidos = DetalleVenta::selectRaw('idarticulo, SUM(cantidad) as total')
            ->with('producto') // Cargamos la relación del producto para acceder a su nombre
            ->groupBy('idarticulo')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function ($detalleVenta) {
                return [
                    'producto' => $detalleVenta->producto->nombre,
                    'total' => $detalleVenta->total
                ];
            })->toArray();


        // Tipos de ventas (ejemplo: venta normal y venta reparto)
        $this->tiposDePagos = Venta::selectRaw('forma_de_pago, COUNT(*) as total')
            ->groupBy('forma_de_pago')
            ->pluck('total', 'forma_de_pago')
            ->toArray();
        // dd($this->tiposDePagos);
        // Ejemplo para obtener las ventas por mes
    }

    public function render()
    {
        return view('livewire.charts');
    }
}
