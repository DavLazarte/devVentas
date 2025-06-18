<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    use HasFactory;
    protected $table = 'subcategories';
     protected $fillable = ['name', 'slug', 'category_id'];

    // Relación inversa con categoría
    public function category() { // ✅ Nombre en inglés
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function locales() {
        return $this->belongsToMany(Local::class, 'local_subcategory');
    }
}
