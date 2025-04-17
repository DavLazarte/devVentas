<?php

namespace App\Http\Livewire\Ingreso;

use Livewire\Component;

class DeudoresComponent extends Component
{
    public $nombre, $tipo_persona, $monto, $dni_cuit, $direccion, $descripcion, $ingreso_id, $saldo, $mail, $estado, $telefono, $busqueda, $persona_id, $personaId, $idpersona, $clienteSeleccionado, $nombre_cliente, $totalSaldos, $ventasConSaldos;
    public $isOpen = 0;
    public $isOpenIngreso = 0, $idpersonaPagos = null;
    public $saldos = [];
    public $searchCliente = '';
    public $loading = false;

    protected $listeners = [
        'editarPersona' => 'editar',
        'openModal' => 'openModal',
        'closeModal' => 'closeModal',
        'verPagos' => 'mostrarPagos',
        'NoVerPagos' => 'ocultarPagos',
        'mostrarNotificacion' => 'mostrarNotificacion',
        'pagoGuardado' => 'handlePagoGuardado',
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
        $this->nombre = '';
        $this->tipo_persona = '';
        $this->telefono = '';
        $this->direccion = '';
        $this->persona_id = '';
        $this->mail = '';
        $this->estado = '';
        $this->monto = '';
        $this->descripcion = '';
        $this->estado = '';
        $this->idpersona = '';
        $this->saldo = '';
        $this->nombre_cliente = '';
        $this->clienteSeleccionado = '';
        $this->totalSaldos = '';
        $this->dni_cuit = '';
        // $this->ver_venta = '';

    }
}
