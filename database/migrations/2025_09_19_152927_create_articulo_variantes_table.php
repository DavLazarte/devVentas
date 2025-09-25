<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticuloVariantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articulo_variantes', function (Blueprint $table) {
            $table->id('id_variante');
            $table->unsignedBigInteger('idarticulo');
            $table->string('sku', 100)->unique();
            $table->decimal('precio_unitario', 10, 2);
            $table->integer('stock')->default(0);
            $table->string('imagen', 255)->nullable();
            $table->text('descripcion_variante')->nullable(); // Ej: "Color rojo, talla M"
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->boolean('es_variante_principal')->default(false); // Para mostrar por defecto
            $table->timestamps();

            // Indices
            $table->index(['idarticulo', 'estado']);
            $table->index('sku');
            $table->index('stock');
            $table->index('es_variante_principal');
            
            // Foreign key
            $table->foreign('idarticulo')->references('idarticulo')->on('articulos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articulo_variantes');
    }
}
