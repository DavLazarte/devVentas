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
        // 1️⃣ - Consulta única para las ventas
        $ventasQuery = Venta::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->where('estado', 'activo')
            ->selectRaw("
                SUM(total_venta) as ventas_totales,
                SUM(CASE WHEN forma_de_pago IN ('efectivo', 'transferencia', 'tarjeta') THEN total_venta ELSE 0 END) as ventas_efectivas,
                SUM(CASE WHEN forma_de_pago = 'cuenta_corriente' THEN total_venta ELSE 0 END) as ventas_credito,
                SUM(CASE WHEN forma_de_pago = 'cuenta_corriente' THEN pago ELSE 0 END) as pagos_credito,
                SUM(CASE WHEN forma_de_pago = 'efectivo' THEN pago ELSE 0 END) as total_efectivo,
                SUM(CASE WHEN forma_de_pago = 'transferencia' THEN pago ELSE 0 END) as total_transferencia,
                SUM(CASE WHEN forma_de_pago = 'tarjeta' THEN pago ELSE 0 END) as total_tarjeta,
                SUM(pago) as monto_total_ventas
            ")->first() ?? (object) [
                'ventas_totales' => 0,
                'ventas_efectivas' => 0,
                'ventas_credito' => 0,
                'pagos_credito' => 0,
                'total_efectivo' => 0,
                'total_transferencia' => 0,
                'total_tarjeta' => 0,
                'monto_total_ventas' => 0,
            ];

        // Asignación de valores
        $this->ventas_totales = $ventasQuery->ventas_totales ?? 0;
        $this->ventas_efectivas = $ventasQuery->ventas_efectivas ?? 0;
        $this->ventas_credito = $ventasQuery->ventas_credito ?? 0;
        $this->pagos_credito = $ventasQuery->pagos_credito ?? 0;
        $this->ventas_efectivo = $ventasQuery->total_efectivo ?? 0;
        $this->ventas_transferencia = $ventasQuery->total_transferencia ?? 0;
        $this->ventas_tarjeta = $ventasQuery->total_tarjeta ?? 0;
        $this->monto_total_ventas = $ventasQuery->monto_total_ventas ?? 0;

        // 2️⃣ - Consulta única para las compras
        $compras = Compra::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->where('estado', 'activo')
            ->selectRaw("
                SUM(CASE WHEN tipo_pago = 'efectivo' THEN pago ELSE 0 END) as total_efectivo,
                SUM(CASE WHEN tipo_pago = 'transferencia' THEN pago ELSE 0 END) as total_transferencia,
                SUM(total) as monto_total
            ")->first() ?? (object) [
                'total_efectivo' => 0,
                'total_transferencia' => 0,
                'monto_total' => 0,
            ];

        // Asignación de valores de compras
        $this->compras_efectivo = $compras->total_efectivo ?? 0;
        $this->compras_transferencia = $compras->total_transferencia ?? 0;
        $this->monto_total_compras = $compras->monto_total ?? 0;

        // 3️⃣ - Consultas individuales (ya son eficientes)
        $this->ingresos = Ingreso::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->sum('monto');

        $this->salidas = Salida::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->sum('monto');

        // 4️⃣ - Cálculo de cierre
        $this->monto_cierre = $this->ventas_efectivo - $this->salidas + $this->ingresos + $this->monto_apertura;

        // 5️⃣ - Desglose de pagos (ya calculado en la consulta de ventas)
        $this->desglose_pagos = [
            'efectivo' => $this->ventas_efectivo,
            'transferencia' => $this->ventas_transferencia,
            'tarjeta' => $this->ventas_tarjeta,
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
