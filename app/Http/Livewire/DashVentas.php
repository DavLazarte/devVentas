<?php

namespace App\Http\Livewire;

use App\Models\Ingreso;
use App\Models\Compra;
use App\Models\Salida;
use App\Models\Venta;
use Livewire\Component;

class DashVentas extends Component
{
    public $cajas, $caja_id, $monto_apertura, $compras_efectivo, $compras_transferencia, $monto_total_compras, $monto_cierre, $fecha_apertura, $fecha_cierre, $ingresos, $salidas, $ventas_efectivo, $ventas_tarjeta, $ventas_transferencia, $monto_total_ventas, $estado, $busqueda;
    public $fecha;
    public $isOpenVenta = 0;
    public $isOpenCompra = 0;
    public $idLocal;
    public $selectedDate;
    public $ventas_totales; // Ventas con pago real (efectivo, transferencia, tarjeta)
    public $ventas_efectivas; // Ventas con pago real (efectivo, transferencia, tarjeta)
    public $ventas_credito;   // Ventas a cuenta corriente
    public $pagos_credito;    // Pagos recibidos de cuentas corrientes
    public $desglose_pagos;

    public function mount()
    {
        $this->idLocal = auth()->user()->local->id;
        $this->selectedDate = now()->toDateString();
    }
    // Nuevo método para actualizar la fecha
    public function updatedSelectedDate($value)
    {
        $this->selectedDate = $value;
    }


    public function render()
    {
        // Modificar todas las consultas para usar $this->selectedDate en lugar de now()->toDateString()
        $this->monto_total_ventas = Venta::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->where('estado', 'activo')
            ->sum('pago');


        $ventas = Venta::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->where('estado', 'activo')
            ->selectRaw("
        SUM(CASE WHEN forma_de_pago = 'efectivo' THEN pago ELSE 0 END) as total_efectivo,
        SUM(CASE WHEN forma_de_pago = 'transferencia' THEN pago ELSE 0 END) as total_transferencia,
        SUM(CASE WHEN forma_de_pago = 'tarjeta' THEN pago ELSE 0 END) as total_tarjeta,
        SUM(CASE WHEN forma_de_pago = 'cuenta_corriente' THEN pago ELSE 0 END) as total_credito
    ")
            ->first() ?? (object) ['total_efectivo' => 0, 'total_transferencia' => 0, 'total_tarjeta' => 0, 'total_credito' => 0];

        $this->ventas_efectivo = $ventas->total_efectivo ?? 0;
        $this->ventas_transferencia = $ventas->total_transferencia ?? 0;
        $this->ventas_tarjeta = $ventas->total_tarjeta ?? 0;
        $this->ventas_credito = $ventas->total_credito ?? 0;

        $compras = Compra::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->where('estado', 'activo')
            ->selectRaw("
            SUM(CASE WHEN tipo_pago = 'efectivo' THEN pago ELSE 0 END) as total_efectivo,
            SUM(CASE WHEN tipo_pago = 'transferencia' THEN pago ELSE 0 END) as total_transferencia,
            SUM(total) as monto_total
        ")
            ->first() ?? (object) ['total_efectivo' => 0, 'total_transferencia' => 0, 'monto_total_compras' => 0];

        $this->compras_efectivo = $compras->total_efectivo ?? 0;
        $this->compras_transferencia = $compras->total_transferencia ?? 0;
        $this->monto_total_compras = $compras->monto_total ?? 0;


        $this->ingresos = Ingreso::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->sum('monto');

        $this->salidas = Salida::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->sum('monto');

        $this->monto_cierre = $this->ventas_efectivo - $this->salidas + $this->ingresos + $this->monto_apertura;


        // Filtros base
        $ventasQuery = Venta::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->where('estado', 'activo');

        // 1. Total general de ventas
        $this->ventas_totales = $ventasQuery->sum('total_venta');

        // 2. Ventas efectivas (excluye cuentas corrientes)
        $this->ventas_efectivas = $ventasQuery->whereIn('forma_de_pago', ['efectivo', 'transferencia', 'tarjeta'])->sum('total_venta');

        // 3. Ventas a crédito (cuenta corriente)
        $this->ventas_credito = $ventasQuery->where('forma_de_pago', 'cuenta_corriente')->sum('total_venta');

        // 4. Pagos recibidos de cuentas corrientes
        $this->pagos_credito = $ventasQuery->where('forma_de_pago', 'cuenta_corriente')->sum('pago');

        // 5. Desglose detallado por método de pago
        $this->desglose_pagos = [
            'efectivo' => $ventasQuery->where('forma_de_pago', 'efectivo')->sum('pago'),
            'transferencia' => $ventasQuery->where('forma_de_pago', 'transferencia')->sum('pago'),
            'tarjeta' => $ventasQuery->where('forma_de_pago', 'tarjeta')->sum('pago'),
        ];



        return view('livewire.dash-ventas');
    }

    public function masVentas()
    {
        $this->openModalVenta();
        return view('livewire.list.list-ventas');
    }

    public function masCompras()
    {

        $query = Compra::where('id_local', $this->idLocal);

        if ($this->busqueda) {
            $query->where('id', 'like', '%' . $this->busqueda . '%')
                ->orWhere('created_at', 'like', '%' . $this->busqueda . '%')
                ->orWhereHas('proveedor', function ($queryPersona) {
                    $queryPersona->where('nombre', 'like', '%' . $this->busqueda . '%');
                })
                ->orWhere('tipo_compra', 'like', '%' . $this->busqueda . '%')
                ->orWhere('tipo_pago', 'like', '%' . $this->busqueda . '%');
        }
        if ($this->fecha) {
            $query->whereDate('created_at', $this->fecha);
        }

        // $compras = $query->with('proveedor')->orderBy('created_at', 'desc')->paginate(10);
        $compras = $query->select('id', 'created_at', 'tipo_compra', 'tipo_pago')
            ->with(['proveedor:id,nombre']) // Evita cargar datos innecesarios
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $this->openModalCompra();
        return view('livewire.list.list-compras', compact('compras'));
    }

    public function openModalVenta()
    {
        $this->isOpenVenta = true;
    }
    public function openModalCompra()
    {
        $this->isOpenCompra = true;
    }

    public function closeModalVenta()
    {
        $this->isOpenVenta = false;
    }
    public function closeModalCompra()
    {
        $this->isOpenCompra = false;
    }
}
