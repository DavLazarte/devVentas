<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Models\User as VoyagerUser;

class Local extends Model
{
    use HasFactory;
    protected $table = 'locales';

    protected $fillable = [
    'id_user',
    'nombre',
    'slug',
    'direccion',
    'localidad',
    'provincia',
    'telefono',
    'email',
    'alias_mp',
    'cbu',
    'banco',
    'titular_cuenta',
    'estado',
    'saldo_caja_0_efectivo',
    'saldo_caja_0_transferencia',
    'mostrar_feed',
    'tipo',
    'plan',
    'plan_id',
    'descripcion',
    'horario',
    'latitud',
    'longitud',
    'foto_portada',
    'foto_logo',
    'rating_promedio',
    'destacado',
    'sitio_web',
    'redes_sociales',
    'siempre_abierto',
    'de_turno',
];

    protected $appends = ['foto_portada_url', 'foto_logo_url', 'abierto_ahora', 'estado_horario'];

    protected ?array $cachedScheduleEval = null;

    public function getAbiertoAhoraAttribute(): bool
    {
        return $this->evaluateSchedule()['abierto'];
    }

    public function getEstadoHorarioAttribute(): ?string
    {
        return $this->evaluateSchedule()['estado'];
    }

    public function evaluateSchedule(): array
    {
        if ($this->cachedScheduleEval !== null) {
            return $this->cachedScheduleEval;
        }

        if ($this->siempre_abierto) {
            return $this->cachedScheduleEval = [
                'abierto' => true,
                'estado'  => 'Abierto 24 hs',
            ];
        }

        if ($this->tipo === 'farmacia' && $this->de_turno) {
            return $this->cachedScheduleEval = [
                'abierto' => true,
                'estado'  => 'De turno hoy (08 a 08 hs)',
            ];
        }

        if (empty($this->horario)) {
            return $this->cachedScheduleEval = [
                'abierto' => false,
                'estado'  => null,
            ];
        }

        $parsed = is_string($this->horario) ? json_decode($this->horario, true) : $this->horario;
        if (!is_array($parsed) || empty($parsed)) {
            return $this->cachedScheduleEval = [
                'abierto' => false,
                'estado'  => null,
            ];
        }

        $now = \Carbon\Carbon::now('America/Argentina/Tucuman');
        $diasMap = [
            0 => 'domingo',
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
        ];

        $hoyKey = $diasMap[$now->dayOfWeek];
        $ayerKey = $diasMap[$now->copy()->subDay()->dayOfWeek];
        $nowTime = $now->format('H:i');

        // 1. Revisar si quedó abierto por turno nocturno del día anterior (ej: 20:00 a 02:00)
        $turnosAyer = self::extractTurnosForDay($parsed[$ayerKey] ?? null);
        foreach ($turnosAyer as [$ap, $ci]) {
            if ($ci < $ap && $nowTime < $ci) {
                return $this->cachedScheduleEval = [
                    'abierto' => true,
                    'estado'  => 'Abierto · cierra ' . $ci,
                ];
            }
        }

        // 2. Revisar turnos del día de hoy
        $turnosHoy = self::extractTurnosForDay($parsed[$hoyKey] ?? null);
        $proximaApertura = null;

        foreach ($turnosHoy as [$ap, $ci]) {
            if ($ci > $ap) {
                // Turno normal dentro del mismo día
                if ($nowTime >= $ap && $nowTime < $ci) {
                    return $this->cachedScheduleEval = [
                        'abierto' => true,
                        'estado'  => 'Abierto · cierra ' . $ci,
                    ];
                }
                if ($nowTime < $ap && ($proximaApertura === null || $ap < $proximaApertura)) {
                    $proximaApertura = $ap;
                }
            } else {
                // Turno que cruza medianoche (ej: 20:00 a 02:00)
                if ($nowTime >= $ap) {
                    return $this->cachedScheduleEval = [
                        'abierto' => true,
                        'estado'  => 'Abierto · cierra ' . $ci,
                    ];
                }
                if ($nowTime < $ap && ($proximaApertura === null || $ap < $proximaApertura)) {
                    $proximaApertura = $ap;
                }
            }
        }

        if ($proximaApertura !== null) {
            return $this->cachedScheduleEval = [
                'abierto' => false,
                'estado'  => 'Cerrado · abre ' . $proximaApertura,
            ];
        }

        return $this->cachedScheduleEval = [
            'abierto' => false,
            'estado'  => 'Cerrado ahora',
        ];
    }

    protected static function extractTurnosForDay($val): array
    {
        if (empty($val) || $val === 'cerrado') {
            return [];
        }

        $rawList = [];
        if (is_string($val)) {
            $rawList = [$val];
        } elseif (is_array($val)) {
            if (array_key_exists('abierto', $val)) {
                if (empty($val['abierto']) || empty($val['turnos']) || !is_array($val['turnos'])) {
                    return [];
                }
                $rawList = $val['turnos'];
            } else {
                $rawList = $val;
            }
        }

        $turnos = [];
        foreach ($rawList as $item) {
            if (is_string($item) && strpos($item, '-') !== false) {
                [$ap, $ci] = explode('-', $item, 2);
                $ap = trim($ap);
                $ci = trim($ci);
                if ($ap !== '' && $ci !== '') {
                    $turnos[] = [$ap, $ci];
                }
            } elseif (is_array($item) && !empty($item['apertura']) && !empty($item['cierre'])) {
                $turnos[] = [trim((string) $item['apertura']), trim((string) $item['cierre'])];
            }
        }

        return $turnos;
    }

    /**
     * Limpia el caché del turno de farmacias (útil cuando el admin cambia manualmente `de_turno`)
     */
    public static function limpiarCacheFarmaciasTurno(): void
    {
        $now = \Carbon\Carbon::now('America/Argentina/Tucuman');
        $hoy = $now->toDateString();
        $ayer = $now->copy()->subDay()->toDateString();
        \Illuminate\Support\Facades\Cache::forget("farmacias_turno_cofatuc_{$hoy}");
        \Illuminate\Support\Facades\Cache::forget("farmacias_turno_cofatuc_{$ayer}");
    }

    /**
     * Ciclo automático COFATUC de 15 días (corte 08:00 AM a 08:00 AM en Monteros, Tucumán)
     * más cualquier farmacia marcada manualmente con de_turno = true.
     * Se calcula 1 sola vez por turno y queda cacheado hasta las 08:00 AM del día siguiente.
     */
    public static function obtenerFarmaciasDeTurno(?\Carbon\Carbon $momento = null): \Illuminate\Support\Collection
    {
        $now = $momento ? $momento->copy()->setTimezone('America/Argentina/Tucuman') : \Carbon\Carbon::now('America/Argentina/Tucuman');

        // Corte de 08:00 AM a 08:00 AM del día siguiente
        $fechaTurno = $now->hour < 8
            ? $now->copy()->subDay()->startOfDay()
            : $now->copy()->startOfDay();

        $inicioTurno = $fechaTurno->copy()->setTime(8, 0, 0);
        $finTurno = $fechaTurno->copy()->addDay()->setTime(8, 0, 0);
        $cacheKey = 'farmacias_turno_cofatuc_' . $fechaTurno->toDateString();

        $items = \Illuminate\Support\Facades\Cache::remember($cacheKey, $finTurno, function () use ($fechaTurno, $inicioTurno, $finTurno) {
            $anchor = \Carbon\Carbon::create(2026, 8, 10, 0, 0, 0, 'America/Argentina/Tucuman');
            $diffDays = (int) round($anchor->diffInDays($fechaTurno, false));
            $offset = (($diffDays % 15) + 15) % 15;

            $cronograma = [
                0 => [
                    ['key' => 'NUNORCO', 'nombre' => 'Farmacia Ñuñorco', 'telefono' => '03863428681', 'direccion' => 'Colón 635'],
                ],
                1 => [
                    ['key' => 'NICOLAS', 'nombre' => 'Farmacia Nicolás', 'telefono' => '03863-428567', 'direccion' => 'Colón 147'],
                ],
                2 => [
                    ['key' => 'NUEVA SANTA TERESITA', 'nombre' => 'Farmacia Nueva Santa Teresita', 'telefono' => null, 'direccion' => 'Tucumán 138'],
                    ['key' => 'ALTHEA', 'nombre' => 'Farmacia Althea', 'telefono' => null, 'direccion' => '24 de Septiembre 825'],
                ],
                3 => [
                    ['key' => 'LA PROVIDENCIA', 'nombre' => 'Farmacia La Providencia', 'telefono' => '03863426545', 'direccion' => 'J. B. Alberdi 55'],
                ],
                4 => [
                    ['key' => 'MONTEROS', 'nombre' => 'Farmacia Monteros', 'telefono' => null, 'direccion' => 'Mariano Moreno 270'],
                    ['key' => 'SANTA MARIA HOSPITAL', 'nombre' => 'Farmacia Santa María Hospital', 'telefono' => '3863428947', 'direccion' => 'Ignacio Sarroza 193 esq. Sarmiento'],
                ],
                5 => [
                    ['key' => 'COLON', 'nombre' => 'Farmacia Colón', 'telefono' => null, 'direccion' => 'Colón 345'],
                ],
                6 => [
                    ['key' => 'DEL ROSARIO', 'nombre' => 'Farmacia Del Rosario', 'telefono' => '03863-426074', 'direccion' => '25 de Mayo 252'],
                ],
                7 => [
                    ['key' => 'NUEVA GENOVA', 'nombre' => 'Farmacia Nueva Génova', 'telefono' => '03863-427685', 'direccion' => 'Leandro Aráoz 41'],
                ],
                8 => [
                    ['key' => 'NUOVA', 'nombre' => 'Farmacia Nuova', 'telefono' => null, 'direccion' => 'Leandro Aráoz 81'],
                ],
                9 => [
                    ['key' => 'SAN FRANCISCO', 'nombre' => 'Farmacia San Francisco', 'telefono' => '03863-428171', 'direccion' => 'Colón 246'],
                ],
                10 => [
                    ['key' => 'CENTRAL', 'nombre' => 'Farmacia Central', 'telefono' => '03863-426835', 'direccion' => 'Colón 38'],
                ],
                11 => [
                    ['key' => 'PACARA', 'nombre' => 'Farmacia Pacará', 'telefono' => null, 'direccion' => 'Ernesto Padilla 180'],
                    ['key' => 'DEL MILAGRO', 'nombre' => 'Farmacia Del Milagro', 'telefono' => '03865-427943', 'direccion' => 'Alberdi 260'],
                ],
                12 => [
                    ['key' => 'MONZON', 'nombre' => 'Farmacia Monzón', 'telefono' => '3863424911', 'direccion' => 'Monzón 203 esq. Crisóstomo Álvarez'],
                    ['key' => 'YURINA', 'nombre' => 'Farmacia Yurina', 'telefono' => null, 'direccion' => 'San Martín 672'],
                ],
                13 => [
                    ['key' => 'SAN CARLOS', 'nombre' => 'Farmacia San Carlos', 'telefono' => null, 'direccion' => 'Ayacucho 897'],
                    ['key' => 'CRISTO REY', 'nombre' => 'Farmacia Cristo Rey', 'telefono' => null, 'direccion' => '25 de Mayo 361'],
                ],
                14 => [
                    ['key' => 'SANTA MARIA', 'nombre' => 'Farmacia Santa María', 'telefono' => '03863-426538', 'direccion' => 'Belgrano 275'],
                ],
            ];

            $farmaciasDb = self::where('estado', 'activo')
                ->where('tipo', 'farmacia')
                ->get();

            $normalize = function (?string $str): string {
                if (!$str) return '';
                $upper = mb_strtoupper(trim($str), 'UTF-8');
                $upper = strtr($upper, [
                    'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U',
                    'Ñ' => 'N', 'Ã‘' => 'N', 'Ã¡' => 'A',
                ]);
                $upper = preg_replace('/^FARMACIA\s+/u', '', $upper);
                $upper = preg_replace('/\s+/u', ' ', $upper);
                return trim($upper);
            };

            $resultado = [];
            $usedIds = [];

            foreach ($cronograma[$offset] ?? [] as $item) {
                $targetKey = $item['key'];
                $matched = $farmaciasDb->first(function ($f) use ($normalize, $targetKey) {
                    $normDb = $normalize($f->nombre);
                    if ($normDb === $targetKey) {
                        return true;
                    }
                    if ($targetKey === 'SANTA MARIA' && str_contains($normDb, 'HOSPITAL')) {
                        return false;
                    }
                    return str_contains($normDb, $targetKey) || str_contains($targetKey, $normDb);
                });

                if ($matched) {
                    $usedIds[] = $matched->id;
                    $nombreDisplay = $matched->nombre;
                    if (!preg_match('/^farmacia/i', $nombreDisplay)) {
                        $nombreDisplay = 'Farmacia ' . $nombreDisplay;
                    }
                    $resultado[] = [
                        'id'               => $matched->id,
                        'slug'             => $matched->slug,
                        'nombre'           => $nombreDisplay,
                        'direccion'        => $matched->direccion ?: $item['direccion'],
                        'telefono'         => $matched->telefono ?: $item['telefono'],
                        'foto_logo_url'    => $matched->foto_logo_url,
                        'foto_portada_url' => $matched->foto_portada_url,
                        'fecha_turno'      => $fechaTurno->toDateString(),
                        'horario_turno'    => 'Hoy de 08:00 a 08:00 hs',
                        'inicio_turno'     => $inicioTurno->toIso8601String(),
                        'fin_turno'        => $finTurno->toIso8601String(),
                        'origen'           => 'cofatuc',
                    ];
                } else {
                    $resultado[] = [
                        'id'               => null,
                        'slug'             => null,
                        'nombre'           => $item['nombre'],
                        'direccion'        => $item['direccion'],
                        'telefono'         => $item['telefono'],
                        'foto_logo_url'    => null,
                        'foto_portada_url' => null,
                        'fecha_turno'      => $fechaTurno->toDateString(),
                        'horario_turno'    => 'Hoy de 08:00 a 08:00 hs',
                        'inicio_turno'     => $inicioTurno->toIso8601String(),
                        'fin_turno'        => $finTurno->toIso8601String(),
                        'origen'           => 'cofatuc',
                    ];
                }
            }

            // Agregar cualquier otra farmacia que el admin haya puesto manualmente con de_turno = true
            foreach ($farmaciasDb as $f) {
                if ($f->de_turno && !in_array($f->id, $usedIds, true)) {
                    $nombreDisplay = $f->nombre;
                    if (!preg_match('/^farmacia/i', $nombreDisplay)) {
                        $nombreDisplay = 'Farmacia ' . $nombreDisplay;
                    }
                    $resultado[] = [
                        'id'               => $f->id,
                        'slug'             => $f->slug,
                        'nombre'           => $nombreDisplay,
                        'direccion'        => $f->direccion,
                        'telefono'         => $f->telefono,
                        'foto_logo_url'    => $f->foto_logo_url,
                        'foto_portada_url' => $f->foto_portada_url,
                        'fecha_turno'      => $fechaTurno->toDateString(),
                        'horario_turno'    => 'Guardia activa hoy',
                        'inicio_turno'     => $inicioTurno->toIso8601String(),
                        'fin_turno'        => $finTurno->toIso8601String(),
                        'origen'           => 'manual',
                    ];
                }
            }

            return $resultado;
        });

        return collect($items)->values();
    }

    public function getFotoPortadaUrlAttribute()
    {
        if (!$this->foto_portada) return null;
        if (filter_var($this->foto_portada, FILTER_VALIDATE_URL)) return $this->foto_portada;
        
        $path = str_replace('\\', '/', $this->foto_portada);
        return asset('storage/' . $path);
    }

    public function getFotoLogoUrlAttribute()
    {
        if (!$this->foto_logo) return null;
        if (filter_var($this->foto_logo, FILTER_VALIDATE_URL)) return $this->foto_logo;
        
        $path = str_replace('\\', '/', $this->foto_logo);
        return asset('storage/' . $path);
    }

    public function user()
    {
        return $this->belongsTo(VoyagerUser::class, 'id_user');
    }

    public function categories() {
        return $this->belongsToMany(Category::class, 'category_local');
    }

    public function subcategories() {
        return $this->belongsToMany(Subcategory::class, 'local_subcategory');
    }

    public function planInfo() {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function anuncios() {
        return $this->hasMany(Anuncio::class, 'local_id');
    }

    /**
     * Check if the local's plan allows a specific feature
     */
    public function planAllows(string $feature): bool
    {
        $plan = $this->planInfo;
        if (!$plan) return false;
        return $plan->allows($feature);
    }

    /**
     * Check if the local can add more products
     */
    public function canAddProduct(): bool
    {
        $plan = $this->planInfo;
        $currentCount = \App\Models\Articulo::where('id_local', $this->id)->count();
        
        if (!$plan) {
            // Fallback for older locales that only have string 'plan' instead of 'plan_id'
            $planString = $this->plan ?? 'free';
            $maxLimit = match($planString) {
                'free' => 15,
                'basic' => 2000, // example
                'premium' => 999999, // example
                default => 15,
            };
            return $currentCount < $maxLimit;
        }

        return $plan->canAddProduct($currentCount);
    }
}
