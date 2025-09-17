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
    Schema::create('mitarbeiter_gruppe', function (Blueprint $table) {
        $table->unsignedBigInteger('mitarbeiter_id');
        $table->unsignedBigInteger('gruppe_id');
        $table->primary(['mitarbeiter_id','gruppe_id']);

        $table->foreign('mitarbeiter_id')
              ->references('Mitarbeiter_id')->on('mitarbeiter')
              ->onDelete('cascade');

        $table->foreign('gruppe_id')
              ->references('gruppe_id')->on('mitarbeiter_gruppen')
              ->onDelete('cascade');

        // $table->timestamps(); // раскомментируйте, если нужны метки времени в пивоте
    });
}
public function down(): void
{
    Schema::dropIfExists('mitarbeiter_gruppe');
}

};
