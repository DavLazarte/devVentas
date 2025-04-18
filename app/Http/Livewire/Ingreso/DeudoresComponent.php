<?php

namespace App\Http\Livewire\Ingreso;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Venta;
use App\Models\Articulo;


class DeudoresComponent extends Component
{
    public $tipo_persona, $monto, $ingreso_id, $saldo, $estado, $busqueda, $persona_id, $personaId, $idpersona, $clienteSeleccionado, $nombre_cliente, $totalSaldos, $ventasConSaldos;
    public $isOpen = 0;
    public $isOpenIngreso = 0, $idpersonaPagos = null;
    public $saldos = [];
    public $searchCliente = '';
    public $loading = false;
    public $ventaIdToDelete;
    public $motivoCancelacion = '';

    public $isLoading = false;
    public $idLocal;

    protected $listeners = [
        'openModal' => 'openModal',
        'closeModal' => 'closeModal',
        'verPagos' => 'mostrarPagos',
        'NoVerPagos' => 'ocultarPagos',
        'mostrarNotificacion' => 'mostrarNotificacion',
        'pagoGuardado' => 'handlePagoGuardado',
        'cancelarVentaConDeuda' => 'cancelarVentaConDeuda',
    ];
    public function handlePagoGuardado()
    {
        // Emitir el mensaje
        $this->closeModal();
        $this->clienteSeleccionado = null; // Limpia la variable clienteSeleccionado
        // También puedes limpiar otros campos si es necesario
    }
    public function render()
    {
        return view('livewire.ingreso.deudores-component');
    }
    public function mostrarNotificacion($mensaje)
    {
        session()->flash('message', $mensaje);
    }

    public function crear()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->isOpen = true;
    }
    public function mostrarPagos($idpersona)
    {
        $this->idpersonaPagos = $idpersona;
        $this->emit('agregarCliente', $idpersona); // Asigna el id de la persona para mostrar los pagos
    }

    public function ocultarPagos()
    {
        $this->idpersonaPagos = null;  // Limpia la variable cuando quieras ocultar el componente
    }

    public function guardar()
    {
        $this->emit('guardarPago');
    }

    public function cancelarVentaConDeuda($id, $motivo)
    {
        $this->isLoading = true;

        DB::beginTransaction();

        try {
            $venta = Venta::findOrFail($id);

            foreach ($venta->detalles as $detalle) {
                $articulo = Articulo::find($detalle->idarticulo);
                if ($articulo) {
                    $articulo->stock += $detalle->cantidad;
                    $articulo->save();
                }
            }

            $venta->estado = 'Inactivo';
            $venta->motivo_cancelacion = $motivo;
            $venta->save();

            DB::commit();

            session()->flash('message', 'Venta con deuda cancelada con éxito y stock restaurado.');
            $this->emit('refreshTableVentasConSaldo');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al cancelar la venta: ' . $e->getMessage());
        }

        $this->isLoading = false;
    }

    // public function cancelarVentaConDeuda($id, $motivo)
    // {

    //     $this->isLoading = true;


    //     $venta = Venta::findOrFail($id);

    //     // Cambiar el estado de la venta a "Cancelada" y guardar el motivo
    //     $venta->estado = 'Inactivo';
    //     $venta->motivo_cancelacion = $motivo; // Usar el argumento pasado
    //     $venta->save();

    //     $this->isLoading = false;

    //     session()->flash('message', 'Venta con deuda cancelada con éxito.');
    //     // Actualizamos la lista de ventas
    //     $this->emit('refreshTableVentasConSaldo');
    // }
    public function generarPdf()
    {
        $this->emit('descargarPdf');
    }
    public function closeModal()
    {
        $this->isOpen = false;
    }
    private function resetInputFields()
    {
        $this->tipo_persona = '';
        $this->persona_id = '';
        $this->estado = '';
        $this->estado = '';
        $this->idpersona = '';
        $this->nombre_cliente = '';
        $this->clienteSeleccionado = '';
        $this->totalSaldos = '';
    }
}
