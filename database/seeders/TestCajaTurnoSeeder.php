<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CierreCaja;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Ingreso;
use App\Models\Salida;
use App\Models\Articulo;
use App\Models\Persona;
use App\Models\Local;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TestCajaTurnoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Genera datos de prueba realistas para el turno de caja ACTIVO de hoy:
     * - 10 Ventas de productos (efectivo, transferencia, mixto, cuenta corriente)
     * - 5 Cobros de servicios/turnos (efectivo, transferencia)
     * - 4 Salidas/egresos de mostrador (efectivo, transferencia - SIN tocar Caja 0)
     */
    public function run()
    {
        // 1. Detectar la caja abierta activa
        $caja = CierreCaja::where('estado', 'abierta')->latest('id')->first();

        if (!$caja) {
            $this->command->error("❌ No hay ninguna caja abierta en el sistema.");
            $this->command->warn("👉 Abrí un turno de caja desde el sistema y volvé a correr este seeder.");
            return;
        }

        $localId = $caja->id_local;
        $local = Local::find($localId);
        $startTime = $caja->created_at ? Carbon::parse($caja->created_at) : Carbon::parse($caja->fecha_apertura)->startOfDay();

        $this->command->info("=============================================================");
        $this->command->info("📦 Sembrando datos de prueba para la Caja Abierta #{$caja->id}");
        $this->command->info("📍 Local ID: {$localId}");
        $this->command->info("⏱️ Turno iniciado el: {$startTime->format('d/m/Y H:i:s')}");
        $this->command->info("=============================================================");

        // 2. Clientes de prueba
        $clientes = Persona::where('id_local', $localId)->where('tipo_persona', 'cliente')->take(4)->get();
        if ($clientes->isEmpty()) {
            $cliente1 = Persona::create(['nombre' => 'Lucas Gómez', 'tipo_persona' => 'cliente', 'id_local' => $localId, 'telefono' => '1122334455']);
            $cliente2 = Persona::create(['nombre' => 'Sofía Martínez', 'tipo_persona' => 'cliente', 'id_local' => $localId, 'telefono' => '1133445566']);
            $cliente3 = Persona::create(['nombre' => 'Carlos Benítez', 'tipo_persona' => 'cliente', 'id_local' => $localId, 'telefono' => '1144556677']);
            $clientes = collect([$cliente1, $cliente2, $cliente3]);
        }

        // 3. Artículos de prueba
        $articulos = Articulo::where('id_local', $localId)->take(4)->get();
        if ($articulos->isEmpty()) {
            $art1 = Articulo::create(['nombre' => 'Cera para Pelo / Pomada', 'precio_unitario' => 4500, 'stock' => 50, 'id_local' => $localId]);
            $art2 = Articulo::create(['nombre' => 'Shampoo Profesional 500ml', 'precio_unitario' => 8000, 'stock' => 40, 'id_local' => $localId]);
            $art3 = Articulo::create(['nombre' => 'Aceite para Barba Premium', 'precio_unitario' => 6000, 'stock' => 30, 'id_local' => $localId]);
            $articulos = collect([$art1, $art2, $art3]);
        }

        // Tiempos progresivos dentro del turno activo de hoy (para que pertenezcan a este turno)
        $t = $startTime->copy();

        DB::beginTransaction();
        try {
            // ─────────────────────────────────────────────────────────────
            // 1. GENERAR 10 VENTAS DE PRODUCTOS EN EL TURNO
            // ─────────────────────────────────────────────────────────────
            $ventasData = [
                [
                    'cliente' => $clientes->first(), 'tipo' => 'efectivo', 'total' => 8500, 'pago' => 8500,
                    'efvo' => 8500, 'transf' => 0, 'saldo' => 0, 'fecha' => $t->copy()->addMinutes(3), 'item' => 'Cera Modeladora', 'cant' => 1, 'precio' => 8500
                ],
                [
                    'cliente' => $clientes->skip(1)->first(), 'tipo' => 'transferencia', 'total' => 15000, 'pago' => 15000,
                    'efvo' => 0, 'transf' => 15000, 'saldo' => 0, 'fecha' => $t->copy()->addMinutes(7), 'item' => 'Shampoo Profesional 1L', 'cant' => 1, 'precio' => 15000
                ],
                [
                    'cliente' => null, 'tipo' => 'efectivo', 'total' => 14000, 'pago' => 14000,
                    'efvo' => 14000, 'transf' => 0, 'saldo' => 0, 'fecha' => $t->copy()->addMinutes(12), 'item' => 'Aceite Barba x2', 'cant' => 2, 'precio' => 7000
                ],
                [
                    'cliente' => $clientes->first(), 'tipo' => 'mixto', 'total' => 25000, 'pago' => 25000,
                    'efvo' => 10000, 'transf' => 15000, 'saldo' => 0, 'fecha' => $t->copy()->addMinutes(18), 'item' => 'Kit Cuidado Capilar', 'cant' => 1, 'precio' => 25000
                ],
                [
                    'cliente' => $clientes->skip(2)->first(), 'tipo' => 'transferencia', 'total' => 12000, 'pago' => 12000,
                    'efvo' => 0, 'transf' => 12000, 'saldo' => 0, 'fecha' => $t->copy()->addMinutes(24), 'item' => 'Pomada Mate Fijación', 'cant' => 2, 'precio' => 6000
                ],
                [
                    'cliente' => null, 'tipo' => 'efectivo', 'total' => 7200, 'pago' => 7200,
                    'efvo' => 7200, 'transf' => 0, 'saldo' => 0, 'fecha' => $t->copy()->addMinutes(30), 'item' => 'Peine Térmico Profesional', 'cant' => 1, 'precio' => 7200
                ],
                [
                    'cliente' => $clientes->first(), 'tipo' => 'cuenta_corriente', 'total' => 16000, 'pago' => 0,
                    'efvo' => 0, 'transf' => 0, 'saldo' => 16000, 'fecha' => $t->copy()->addMinutes(35), 'item' => 'Perfume Texturizador (A cuenta)', 'cant' => 1, 'precio' => 16000
                ],
                [
                    'cliente' => $clientes->skip(1)->first(), 'tipo' => 'mixto', 'total' => 30000, 'pago' => 30000,
                    'efvo' => 12000, 'transf' => 18000, 'saldo' => 0, 'fecha' => $t->copy()->addMinutes(42), 'item' => 'Pack Barbero Completo', 'cant' => 1, 'precio' => 30000
                ],
                [
                    'cliente' => null, 'tipo' => 'efectivo', 'total' => 9500, 'pago' => 9500,
                    'efvo' => 9500, 'transf' => 0, 'saldo' => 0, 'fecha' => $t->copy()->addMinutes(48), 'item' => 'Loción Refrescante', 'cant' => 1, 'precio' => 9500
                ],
                [
                    'cliente' => $clientes->skip(2)->first(), 'tipo' => 'transferencia', 'total' => 20000, 'pago' => 20000,
                    'efvo' => 0, 'transf' => 20000, 'saldo' => 0, 'fecha' => $t->copy()->addMinutes(55), 'item' => 'Serum Restaurador', 'cant' => 2, 'precio' => 10000
                ],
            ];

            $totalVentasEfvo = 0;
            $totalVentasTransf = 0;
            $totalVentasFiado = 0;

            foreach ($ventasData as $vd) {
                $venta = Venta::create([
                    'idcliente'           => $vd['cliente']?->idpersona,
                    'tipo_venta'          => 'mostrador',
                    'total_venta'         => $vd['total'],
                    'descuento'           => 0,
                    'recargo'             => 0,
                    'pago'                => $vd['pago'],
                    'monto_efectivo'      => $vd['efvo'],
                    'monto_transferencia' => $vd['transf'],
                    'forma_de_pago'       => $vd['tipo'],
                    'saldo'               => $vd['saldo'],
                    'estado'              => 'Activo',
                    'id_local'            => $localId,
                    'created_at'          => $vd['fecha'],
                    'updated_at'          => $vd['fecha'],
                ]);

                DetalleVenta::create([
                    'idventa'             => $venta->id,
                    'idarticulo'          => $articulos->first()?->idarticulo,
                    'cantidad'            => $vd['cant'],
                    'precio_venta'        => $vd['precio'],
                    'estado'              => 'activo',
                    'descripcion_variante'=> $vd['item'],
                    'created_at'          => $vd['fecha'],
                    'updated_at'          => $vd['fecha'],
                ]);

                $totalVentasEfvo += $vd['efvo'];
                $totalVentasTransf += $vd['transf'];
                $totalVentasFiado += $vd['saldo'];
            }

            // ─────────────────────────────────────────────────────────────
            // 2. GENERAR 5 COBROS DE SERVICIOS / TURNOS (ingresos)
            // ─────────────────────────────────────────────────────────────
            $serviciosData = [
                ['desc' => 'Cobro de turno: Corte y Perfilado de Barba - Lucas Gómez', 'monto' => 6500, 'tipo' => 'efectivo', 'fecha' => $t->copy()->addMinutes(10)],
                ['desc' => 'Cobro de turno: Coloración y Alisado - Sofía Martínez', 'monto' => 22000, 'tipo' => 'transferencia', 'fecha' => $t->copy()->addMinutes(20)],
                ['desc' => 'Cobro de turno: Manicura y Esmaltado - Valentina', 'monto' => 8000, 'tipo' => 'efectivo', 'fecha' => $t->copy()->addMinutes(28)],
                ['desc' => 'Cobro de turno: Corte Infantil - Benicio', 'monto' => 5500, 'tipo' => 'efectivo', 'fecha' => $t->copy()->addMinutes(38)],
                ['desc' => 'Cobro de turno: Masaje Descontracturante - Carlos Benítez', 'monto' => 14000, 'tipo' => 'transferencia', 'fecha' => $t->copy()->addMinutes(50)],
            ];

            $totalServiciosEfvo = 0;
            $totalServiciosTransf = 0;

            foreach ($serviciosData as $sd) {
                Ingreso::create([
                    'idpersona'    => $clientes->first()?->idpersona,
                    'monto'        => $sd['monto'],
                    'tipo_pago'    => $sd['tipo'],
                    'tipo_ingreso' => 'servicio',
                    'descripcion'  => $sd['desc'],
                    'saldo'        => 0,
                    'estado'       => 'activo',
                    'id_local'     => $localId,
                    'created_at'   => $sd['fecha'],
                    'updated_at'   => $sd['fecha'],
                ]);

                if ($sd['tipo'] === 'transferencia') {
                    $totalServiciosTransf += $sd['monto'];
                } else {
                    $totalServiciosEfvo += $sd['monto'];
                }
            }

            // ─────────────────────────────────────────────────────────────
            // 3. GENERAR SALIDAS / EGRESOS (TODOS de mostrador, Cero de Caja 0)
            // ─────────────────────────────────────────────────────────────
            $salidasData = [
                ['desc' => 'Insumos de limpieza y bolsas para empaque', 'monto' => 4500, 'tipo' => 'efectivo', 'origen' => 'mostrador', 'fecha' => $t->copy()->addMinutes(15)],
                ['desc' => 'Cafetería, refrigerios y descartables', 'monto' => 7800, 'tipo' => 'efectivo', 'origen' => 'mostrador', 'fecha' => $t->copy()->addMinutes(25)],
                ['desc' => 'Pago transferencia reposición a distribuidora', 'monto' => 35000, 'tipo' => 'transferencia', 'origen' => 'mostrador', 'fecha' => $t->copy()->addMinutes(40)],
                ['desc' => 'Mantenimiento cerradura mostrador y focos', 'monto' => 5200, 'tipo' => 'efectivo', 'origen' => 'mostrador', 'fecha' => $t->copy()->addMinutes(52)],
            ];

            $totalSalidasEfvo = 0;
            $totalSalidasTransf = 0;

            foreach ($salidasData as $sld) {
                Salida::create([
                    'idpersona'   => null,
                    'tipo_salida' => 'gasto',
                    'origen'      => $sld['origen'],
                    'monto'       => $sld['monto'],
                    'tipo_pago'   => $sld['tipo'],
                    'descripcion' => $sld['desc'],
                    'saldo'       => 0,
                    'estado'      => 'activo',
                    'id_local'    => $localId,
                    'created_at'  => $sld['fecha'],
                    'updated_at'  => $sld['fecha'],
                ]);

                if ($sld['tipo'] === 'transferencia') {
                    $totalSalidasTransf += $sld['monto'];
                } else {
                    $totalSalidasEfvo += $sld['monto'];
                }
            }

            DB::commit();

            // ─────────────────────────────────────────────────────────────
            // REPORTE CON LOS TOTALES MATEMÁTICOS PARA EL DESARROLLADOR
            // ─────────────────────────────────────────────────────────────
            $netoEfvo = $totalVentasEfvo + $totalServiciosEfvo - $totalSalidasEfvo;
            $netoTransf = $totalVentasTransf + $totalServiciosTransf - $totalSalidasTransf;

            $esperadoEfvo = (float) $caja->monto_apertura + $netoEfvo;
            $esperadoTransf = (float) $caja->monto_apertura_transferencia + $netoTransf;

            $c0Ef = (float) ($local->saldo_caja_0_efectivo ?? 0);
            $c0Tr = (float) ($local->saldo_caja_0_transferencia ?? 0);

            $this->command->info("\n✅ ¡Sembrado completado con éxito para el turno de hoy!");
            $this->command->line("-------------------------------------------------------------");
            $this->command->line("🛒 Ventas (10): Total $" . number_format($totalVentasEfvo + $totalVentasTransf + $totalVentasFiado, 0, ',', '.'));
            $this->command->line("   - Efectivo:        $" . number_format($totalVentasEfvo, 0, ',', '.'));
            $this->command->line("   - Transferencia:   $" . number_format($totalVentasTransf, 0, ',', '.'));
            $this->command->line("   - Fiado (A cuenta):$" . number_format($totalVentasFiado, 0, ',', '.'));
            $this->command->line("✂️ Servicios (5): Total $" . number_format($totalServiciosEfvo + $totalServiciosTransf, 0, ',', '.'));
            $this->command->line("   - Efectivo:        $" . number_format($totalServiciosEfvo, 0, ',', '.'));
            $this->command->line("   - Transferencia:   $" . number_format($totalServiciosTransf, 0, ',', '.'));
            $this->command->line("💸 Salidas de Mostrador (4): Total $" . number_format($totalSalidasEfvo + $totalSalidasTransf, 0, ',', '.'));
            $this->command->line("   - Efectivo:        $" . number_format($totalSalidasEfvo, 0, ',', '.'));
            $this->command->line("   - Transferencia:   $" . number_format($totalSalidasTransf, 0, ',', '.'));
            $this->command->line("-------------------------------------------------------------");
            $this->command->info("🎯 RESULTADOS ESPERADOS EN EL ARQUEO:");
            $this->command->info("💵 Efectivo en cajón esperado: $" . number_format($esperadoEfvo, 0, ',', '.') . " (Base inicial $" . number_format($caja->monto_apertura, 0, ',', '.') . " + $" . number_format($netoEfvo, 0, ',', '.') . ")");
            $this->command->info("🏦 Banco / MP esperado:       $" . number_format($esperadoTransf, 0, ',', '.') . " (Base inicial $" . number_format($caja->monto_apertura_transferencia, 0, ',', '.') . " + $" . number_format($netoTransf, 0, ',', '.') . ")");
            $this->command->info("📋 Fiado en cuenta corriente:  $" . number_format($totalVentasFiado, 0, ',', '.'));
            $this->command->line("-------------------------------------------------------------");
            $this->command->comment("🛡️ Saldo en Caja 0 (Tesorería intacto):");
            $this->command->comment("   - Efectivo en caja fuerte:    $" . number_format($c0Ef, 0, ',', '.'));
            $this->command->comment("   - Reserva digital en banco:  $" . number_format($c0Tr, 0, ',', '.'));
            $this->command->comment("   👉 No se sembraron salidas de Caja 0 para que puedas probarlas manualmente.");
            $this->command->info("=============================================================");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Error al sembrar datos: " . $e->getMessage());
        }
    }
}
