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
    public $idLocal;

    public function mount()
    {
        $this->idLocal = auth()->user()->local->id;

        // Ventas por mes filtradas por local
        $this->ventasPorMes = Venta::where('id_local', $this->idLocal)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as mes, COUNT(*) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->pluck('total', 'mes')
            ->toArray();

        // Productos más vendidos filtrados por local
        $this->productosMasVendidos = DetalleVenta::whereHas('venta', function ($query) {
                $query->where('id_local', $this->idLocal);
            })
            ->selectRaw('idarticulo, SUM(cantidad) as total')
            ->with('producto')
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

        // Tipos de pagos filtrados por local
        $this->tiposDePagos = Venta::where('id_local', $this->idLocal)
            ->selectRaw('forma_de_pago, COUNT(*) as total')
            ->groupBy('forma_de_pago')
            ->pluck('total', 'forma_de_pago')
            ->toArray();
    }

    public function render()
    {
        return view('livewire.charts');
    }
}
