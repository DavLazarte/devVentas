<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla para almacenar múltiples imágenes por artículo (hasta 3) y por variante (hasta 2).
     */
    public function up(): void
    {
        Schema::create('articulo_imagenes', function (Blueprint $table) {
            $table->id();

            // FK al artículo padre
            $table->unsignedBigInteger('idarticulo')
                ->comment('FK a articulos.idarticulo');
            $table->foreign('idarticulo', 'fk_artimgs_articulo')
                ->references('idarticulo')
                ->on('articulos')
                ->onDelete('cascade');

            // FK opcional a la variante (null = imagen del producto principal)
            $table->unsignedBigInteger('variante_id')
                ->nullable()
                ->comment('FK a articulo_variantes.id_variante, null = imagen del artículo base');
            $table->foreign('variante_id', 'fk_artimgs_variante')
                ->references('id_variante')
                ->on('articulo_variantes')
                ->onDelete('cascade');

            // Ruta relativa en storage/public (sin prefijo /storage/)
            $table->string('ruta')->comment('Ruta relativa en storage, ej: articulos/abc123.jpg');

            // Orden de la imagen: 0 = principal, 1 = secundaria, 2 = terciaria
            // Para artículo: máximo 3 (orden 0,1,2)
            // Para variante: máximo 2 (orden 0,1)
            $table->unsignedTinyInteger('orden')->default(0)
                ->comment('Orden/posición de la imagen (0=principal)');

            $table->timestamps();

            // Un artículo no puede tener dos imágenes con el mismo orden y la misma variante
            $table->unique(['idarticulo', 'variante_id', 'orden'], 'uq_artimgs_orden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articulo_imagenes');
    }
};
