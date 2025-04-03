<?php

namespace App\Http\Livewire\Ingreso;

use Livewire\Component;
use App\Models\Venta;
use App\Models\Ingreso;
use App\Models\Persona;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;


class CargaPagos extends Component
{
    public $ventasConSaldos, $descripcion, $monto, $idLocal, $saldo, $totalSaldos, $clienteSeleccionado, $nombre_cliente, $idpersona, $saldos = [], $detallesVisibles = [],  $ingreso_id;

    protected $listeners = ['guardarPago' => 'guardar', 'agregarCliente' => 'cargarVentasConSaldos', 'descargarPdf' => 'generarPdf'];



    public function render()
    {
        return view('livewire.ingreso.carga-pagos');
    }
    public function updated($propertyName)
    {
        // if (in_array($propertyName, ['descuento', 'recargo'])) {
        //     $this->calcularNuevoTotal();
        // }

        if ($propertyName === 'monto') {
            $this->distribuirPago();
        }
    }

    public function cargarVentasConSaldos($idpersona)
    {
        // Encuentra la persona por ID.
        $clienteSe = Persona::find($idpersona);

        if ($clienteSe) {
            $this->clienteSeleccionado = $clienteSe;
            $this->idpersona = $clienteSe->idpersona;
            $this->nombre_cliente = $clienteSe->nombre;
            // dd('bien hasta aqui2');


            // Obtiene las ventas con saldos y precarga las relaciones necesarias.
            $this->ventasConSaldos = Venta::with('detalles.producto', 'persona')
                ->where('idcliente', $this->idpersona)
                ->where('saldo', '>', 0)
                ->where('estado', 'activo') // Filtra solo las ventas activas
                ->orderBy('created_at', 'desc')
                ->get();

            // Calcula el total de los saldos.
            $this->totalSaldos = $this->ventasConSaldos->sum('saldo');
        } else {
            // Opcional: manejo de error si el cliente no se encuentra
            $this->addError('clienteNotFound', 'El cliente no se encuentra en la base de datos.');
        }
    }


    public function distribuirPago()
    {
        if ($this->totalSaldos > 0) {
            $montoPago = $this->monto;

            // Ordenar las ventas por fecha de creación ASC para pagar las más antiguas primero
            $ventasOrdenadas = $this->ventasConSaldos->sortBy('created_at');

            foreach ($ventasOrdenadas as $venta) {
                $montoVenta = min($venta->saldo, $montoPago);

                // Si hay un monto a descontar, actualizar el saldo
                if ($montoVenta > 0) {
                    $nuevoSaldo = round($venta->saldo - $montoVenta, 2);
                } else {
                    // Si no hay cambios, conservar el saldo original
                    $nuevoSaldo = $venta->saldo;
                }


                // Almacenar el nuevo saldo, ya sea modificado o el original
                $this->saldos[$venta->id] = $nuevoSaldo;

                // Actualiza el montoPago
                $montoPago -= $montoVenta;

                // Si ya no queda monto por distribuir, sal del bucle
                if ($montoPago <= 0) {
                    break;
                }
            }

            // Calcular el nuevo saldo total
            $nuevo_saldo = $this->totalSaldos - $this->monto;
            $this->saldo = round($nuevo_saldo, 2);
        }
        // Para ventas que no se actualizaron, asegúrate de rellenar sus saldos también
        foreach ($this->ventasConSaldos as $venta) {
            if (!isset($this->saldos[$venta->id])) {
                // Si la venta no fue procesada en el bucle anterior, conservar el saldo original
                $this->saldos[$venta->id] = $venta->saldo;
            }
        }
    }

    public function guardar()
    {
        $this->validate([
            'idpersona' => 'required',
            'monto' => 'required',
            'saldo' => 'required',
            'descripcion' => 'required|string|min:3|max:255', // Validación de descripción
        ]);

        try {
            DB::beginTransaction(); // Iniciar la transacción

            $this->idLocal = auth()->user()->local->id;

            // 1️⃣ Crear o actualizar el ingreso
            $ingreso = Ingreso::updateOrCreate(
                ['id_ingreso' => $this->ingreso_id],
                [
                    'idpersona' => $this->idpersona,
                    'monto' => $this->monto,
                    'descripcion' => $this->descripcion,
                    'saldo' => $this->saldo,
                    'id_local' => $this->idLocal,
                ]
            );

            if (!$ingreso) {
                throw new \Exception('Error al registrar el ingreso.');
            }

            // 2️⃣ Actualizar las ventas con saldo si el ingreso fue exitoso
            foreach ($this->ventasConSaldos as $venta) {
                $montoVenta = $this->saldos[$venta->id] ?? 0;

                $ventaModel = Venta::find($venta->id);
                if (!$ventaModel) {
                    throw new \Exception('Venta con ID ' . $venta->id . ' no encontrada.');
                }

                $montoRedondeado = round($montoVenta, 2);
                if ($montoRedondeado !== $ventaModel->saldo) {
                    $ventaModel->saldo = $montoRedondeado;
                    $ventaModel->save();
                }
            }

            DB::commit(); // Confirmar los cambios en la base de datos

            // Emitir mensaje de éxito con Livewire
            $this->emit('pagoGuardado');
            $this->emit('refreshDatatableIngresos');
            $this->emit('mostrarNotificacion', $this->ingreso_id ? 'Ingreso actualizado exitosamente.' : 'Pago creado exitosamente.');

            // Limpiar campos
            $this->resetInputFields();
        } catch (\Exception $e) {
            DB::rollBack(); // Deshacer cambios si hay error
            $this->emit('mostrarNotificacion', 'Error: ' . $e->getMessage());
        }
    }
    private function resetInputFields()
    {
        $this->monto = '';
        $this->descripcion = '';
        $this->idpersona = '';
        $this->saldo = '';
        $this->totalSaldos = '';
    }

    public function toggleDetail($ventaId)
    {
        if (in_array($ventaId, $this->detallesVisibles)) {
            $this->detallesVisibles = array_diff($this->detallesVisibles, [$ventaId]);
        } else {
            $this->detallesVisibles[] = $ventaId;
        }
    }
    public function generarPdf(Venta $venta)
    {
        try {
            Log::info('Iniciando la generación del PDF para la venta ID: ' . $venta->id);

            if (!$venta) {
                Log::error('Venta no encontrada o es null.');
                session()->flash('error', 'No se encontró la venta.');
                return back();
            }

            // Crear la carpeta si no existe
            $directory = storage_path('app/public/ventas');
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true); // 0755 = permisos de escritura/lectura
                Log::info('Carpeta creada: ' . $directory);
            }

            Log::info('Cargando la vista para el PDF...');
            $pdf = Pdf::loadView('livewire.venta.detalles_pdf', ['venta' => $venta]);

            $tempPath = $directory . '/temp_detalleventa_' . $venta->id . '.pdf';
            Log::info('Guardando temporalmente el PDF en: ' . $tempPath);

            $pdf->save($tempPath);

            if (file_exists($tempPath)) {
                $fileSize = filesize($tempPath);
                Log::info('Tamaño del archivo PDF: ' . $fileSize . ' bytes.');

                if ($fileSize > 0) {
                    Log::info('El PDF fue generado correctamente.');
                    return response()->download($tempPath, 'detalleventa_' . $venta->id . '.pdf')->deleteFileAfterSend(true);
                } else {
                    Log::error('El archivo PDF tiene un tamaño de 0 bytes.');
                    session()->flash('error', 'El PDF no se generó correctamente.');
                }
            } else {
                Log::error('No se pudo generar el PDF.');
                session()->flash('error', 'No se pudo generar el PDF.');
            }
        } catch (\Exception $e) {
            Log::error('Error al generar el PDF: ' . $e->getMessage());
            session()->flash('error', 'Hubo un error al generar el PDF.');
        }
    }
}
