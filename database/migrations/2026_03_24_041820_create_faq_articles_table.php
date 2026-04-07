<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('faq_articles', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion_problema')->nullable();
            $table->text('resolucion');
            $table->text('causa_raiz');
            $table->enum('categoria', ['backend', 'frontend', 'bases_de_datos', 'devops', 'testing', 'seguridad', 'otro'])->default('otro');
            $table->enum('tipo_resolucion', ['workaround', 'solucion_definitiva'])->default('workaround');
            $table->boolean('es_reutilizable')->default(true);
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('ticket_id')->unique()->constrained('tickets')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq_articles');
    }
};
