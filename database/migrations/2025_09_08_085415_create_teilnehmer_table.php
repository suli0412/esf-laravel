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
        Schema::create('teilnehmer', function (Blueprint $table) {
            $table->id('Teilnehmer_id');
            $table->string('Nachname', 100);
            $table->string('Vorname', 100);
            $table->enum('Geschlecht', ['Mann','Frau','Nicht binär'])->nullable();
            $table->string('SVN', 12)->nullable();
            $table->string('Strasse', 150)->nullable();
            $table->string('Hausnummer', 10)->nullable();
            $table->string('PLZ', 10)->nullable();
            $table->string('Wohnort', 150)->nullable();
            $table->string('Land', 50)->nullable();
            $table->string('Email', 255)->nullable();
            $table->string('Telefonnummer', 25)->nullable();
            $table->date('Geburtsdatum')->nullable();
            $table->string('Geburtsland', 100)->nullable();
            $table->string('Staatszugehörigkeit', 100)->nullable();
            $table->string('Staatszugehörigkeit_Kategorie', 100)->nullable();
            $table->string('Aufenthaltsstatus', 100)->nullable();
            $table->enum('Minderheit', ['Ja','Nein','Keine Angabe'])->default('Keine Angabe');
            $table->enum('Behinderung', ['Ja','Nein','Keine Angabe'])->default('Keine Angabe');
            $table->enum('Obdachlos', ['Ja','Nein','Keine Angabe'])->default('Keine Angabe');
            $table->enum('LändlicheGebiete', ['Ja','Nein','Keine Angabe'])->default('Keine Angabe');
            $table->enum('ElternImAuslandGeboren', ['Ja','Nein','Keine Angabe'])->default('Ja');
            $table->enum('Armutsbetroffen', ['Ja','Nein','Keine Angabe'])->default('Keine Angabe');
            $table->enum('Armutsgefährdet', ['Ja','Nein','Keine Angabe'])->default('Keine Angabe');
            $table->enum('Bildungshintergrund', ['ISCED0','ISCED1','ISCED2','ISCED3','ISCED4','ISCED5-8'])->nullable();
            $table->boolean('IDEA_Stammdatenblatt')->default(false);
            $table->boolean('IDEA_Dokumente')->default(false);
            $table->enum('PAZ', [
                'Arbeitsaufnahme','Lehrstelle','ePSA','Sprachprüfung A2/B1',
                'weitere Deutschkurse','Basisbildung','Sonstige berufsspezifische Weiterbildung','Sonstiges'
            ])->nullable();
            $table->string('Berufserfahrung_als', 100)->nullable();
            $table->string('Bereich_berufserfahrung', 100)->nullable();
            $table->string('Land_berufserfahrung', 30)->nullable();
            $table->string('Firma_berufserfahrung', 150)->nullable();
            $table->string('Zeit_berufserfahrung', 100)->nullable();
            $table->decimal('Stundenumfang_berufserfahrung', 5, 2)->nullable();
            $table->string('Zertifikate', 300)->nullable();
            $table->string('Berufswunsch', 100)->nullable();
            $table->string('Berufswunsch_branche', 100)->nullable();
            $table->string('Berufswunsch_branche2', 100)->nullable();
            $table->boolean('Clearing_gruppe')->default(false);
            $table->unsignedInteger('Unterrichtseinheiten')->nullable();
            $table->text('Anmerkung')->nullable();

            $table->timestamps();

            $table->index(['Nachname','Vorname'], 'idx_teilnehmer_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teilnehmer');
    }
};
