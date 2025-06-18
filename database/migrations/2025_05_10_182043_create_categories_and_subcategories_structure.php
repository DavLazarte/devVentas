<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesAndSubcategoriesStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       // Tabla 'categorias'
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->integer('orden')->default(0);
            $table->timestamps();
        });

        // Tabla 'subcategorias'
        Schema::create('subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Tabla pivote 'categoria_local'
        Schema::create('category_local', function (Blueprint $table) {
            $table->foreignId('local_id')->constrained('locales')->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->primary(['local_id', 'category_id']);
        });

        // Tabla pivote 'local_subcategoria' (opcional)
        Schema::create('local_subcategory', function (Blueprint $table) {
            $table->foreignId('local_id')->constrained('locales')->onDelete('cascade');
            $table->foreignId('subcategory_id')->constrained()->onDelete('cascade');
            $table->primary(['local_id', 'subcategory_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('local_subcategory');
        Schema::dropIfExists('category_local');
        Schema::dropIfExists('subcategories');
        Schema::dropIfExists('categories');
    }
}
