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

    // public function render()
    // {

    //     // Filtrar ventas, ingresos y salidas por local
    //     $this->monto_total_ventas = Venta::where('id_local', $this->idLocal)
    //         ->whereDate('created_at', now()->toDateString())
    //         ->where('estado', 'activo')
    //         ->sum('pago');

    //     $this->ventas_efectivo = Venta::where('id_local', $this->idLocal)
    //         ->whereDate('created_at', now()->toDateString())
    //         ->whereIn('forma_de_pago', ['efectivo'])
    //         ->where('estado', 'activo')
    //         ->sum('pago');

    //     $this->ventas_transferencia = Venta::where('id_local', $this->idLocal)
    //         ->whereDate('created_at', now()->toDateString())
    //         ->whereIn('forma_de_pago', ['transferencia'])
    //         ->where('estado', 'activo')
    //         ->sum('pago');

    //     // cuentas y no tarjetas corregir nombre despues

    //     $this->ventas_tarjeta = Venta::where('id_local', $this->idLocal)
    //         ->whereDate('created_at', now()->toDateString())
    //         ->whereIn('forma_de_pago', ['cuenta_corriente'])
    //         ->where('estado', 'activo')
    //         ->sum('pago');

    //     // compras
    //     $this->monto_total_compras = Compra::where('id_local', $this->idLocal)
    //         ->whereDate('created_at', now()->toDateString())
    //         ->where('estado', 'activo')
    //         ->sum('total');

    //     $this->compras_efectivo = Compra::where('id_local', $this->idLocal)
    //         ->whereDate('created_at', now()->toDateString())
    //         ->whereIn('tipo_pago', ['efectivo'])
    //         ->where('estado', 'activo')
    //         ->sum('pago');

    //     $this->compras_transferencia = Compra::where('id_local', $this->idLocal)
    //         ->whereDate('created_at', now()->toDateString())
    //         ->whereIn('tipo_pago', ['transferencia'])
    //         ->where('estado', 'activo')
    //         ->sum('pago');

    //     $this->ingresos = Ingreso::where('id_local', $this->idLocal)
    //         ->whereDate('created_at', now()->toDateString())
    //         ->sum('monto');

    //     $this->salidas = Salida::where('id_local', $this->idLocal)
    //         ->whereDate('created_at', now()->toDateString())
    //         ->sum('monto');

    //     // Actualizar la variable monto_cierre sumando total_ventas, pagos, ingresos y salidas
    //     $this->monto_cierre = $this->ventas_efectivo - $this->salidas + $this->ingresos + $this->monto_apertura;

    //     return view('livewire.dash-ventas');
    // }

    public function render()
    {
        // Modificar todas las consultas para usar $this->selectedDate en lugar de now()->toDateString()
        $this->monto_total_ventas = Venta::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->where('estado', 'activo')
            ->sum('pago');

        $this->ventas_efectivo = Venta::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->whereIn('forma_de_pago', ['efectivo'])
            ->where('estado', 'activo')
            ->sum('pago');

        $this->ventas_transferencia = Venta::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->whereIn('forma_de_pago', ['transferencia'])
            ->where('estado', 'activo')
            ->sum('pago');

        $this->ventas_tarjeta = Venta::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->whereIn('forma_de_pago', ['cuenta_corriente','tarjeta'])
            ->where('estado', 'activo')
            ->sum('total_venta');

        $this->monto_total_compras = Compra::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->where('estado', 'activo')
            ->sum('total');

        $this->compras_efectivo = Compra::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->whereIn('tipo_pago', ['efectivo'])
            ->where('estado', 'activo')
            ->sum('pago');

        $this->compras_transferencia = Compra::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->whereIn('tipo_pago', ['transferencia'])
            ->where('estado', 'activo')
            ->sum('pago');

        $this->ingresos = Ingreso::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->sum('monto');

        $this->salidas = Salida::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->sum('monto');
        $this->monto_cierre = $this->ventas_efectivo - $this->salidas + $this->ingresos + $this->monto_apertura;


        // 1. Calcular total general de ventas (independiente de la forma de pago)
        $this->ventas_totales = Venta::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->where('estado', 'activo')
            ->sum('total_venta');

        // 2. Ventas efectivas (excluye cuentas corrientes)
        $this->ventas_efectivas = Venta::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->whereIn('forma_de_pago', ['efectivo', 'transferencia', 'tarjeta'])
            ->where('estado', 'activo')
            ->sum('total_venta');

        // 3. Ventas a crédito (cuenta corriente)
        $this->ventas_credito = Venta::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->where('forma_de_pago', 'cuenta_corriente')
            ->where('estado', 'activo')
            ->sum('total_venta');

        // 4. Pagos recibidos de cuentas corrientes
        $this->pagos_credito = Venta::where('id_local', $this->idLocal)
            ->whereDate('created_at', $this->selectedDate)
            ->where('forma_de_pago', 'cuenta_corriente')
            ->where('estado', 'activo')
            ->sum('pago');

        // 5. Desglose detallado por método de pago
        $this->desglose_pagos = [
            'efectivo' => Venta::where('id_local', $this->idLocal)
                ->whereDate('created_at', $this->selectedDate)
                ->where('forma_de_pago', 'efectivo')
                ->where('estado', 'activo')
                ->sum('pago'),

            'transferencia' => Venta::where('id_local', $this->idLocal)
                ->whereDate('created_at', $this->selectedDate)
                ->where('forma_de_pago', 'transferencia')
                ->where('estado', 'activo')
                ->sum('pago'),

            'tarjeta' => Venta::where('id_local', $this->idLocal)
                ->whereDate('created_at', $this->selectedDate)
                ->where('forma_de_pago', 'tarjeta')
                ->where('estado', 'activo')
                ->sum('pago'),
        ];


        return view('livewire.dash-ventas');
    }

    public function masVentas()
    {
        $query = Venta::where('id_local', $this->idLocal);

        if ($this->busqueda) {
            $query->where('id', 'like', '%' . $this->busqueda . '%')
                ->orWhere('created_at', 'like', '%' . $this->busqueda . '%')
                ->orWhereHas('persona', function ($queryPersona) {
                    $queryPersona->where('nombre', 'like', '%' . $this->busqueda . '%');
                })
                ->orWhere('tipo_venta', 'like', '%' . $this->busqueda . '%')
                ->orWhere('forma_de_pago', 'like', '%' . $this->busqueda . '%');
        }
        if ($this->fecha) {
            $query->whereDate('created_at', $this->fecha);
        }


        $ventas = $query->with('persona')->orderBy('created_at', 'desc')->paginate(10);
        $this->openModalVenta();
        return view('livewire.list.list-ventas', compact('ventas'));
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

        $compras = $query->with('proveedor')->orderBy('created_at', 'desc')->paginate(10);
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
