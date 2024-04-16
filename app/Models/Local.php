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
        'direccion',
        'telefono',
        'email',
        'estado',
    ];

    public function user()
    {
        return $this->belongsTo(VoyagerUser::class, 'id_user');
    }
}
