<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BeratungsartenSeeder extends Seeder
{
    public function run(): void
    {
        // Три-буквенные коды (у вас Code CHAR(3) UNIQUE)
        $rows = [
            ['Code' => 'EG' , 'Bezeichnung' => 'Erstgespräch'],
            ['Code' => 'BB' , 'Bezeichnung' => 'Bildungs- und Berufsberatung'],
            ['Code' => 'LB' , 'Bezeichnung' => 'Lernberatung'],
            ['Code' => 'KOA', 'Bezeichnung' => 'Kursorganisatorische Angelegenheiten'],
            ['Code' => 'CM' , 'Bezeichnung' => 'Case Management'],
            ['Code' => 'PSY', 'Bezeichnung' => 'Psychische Angelegenheiten'],
            ['Code' => 'MED', 'Bezeichnung' => 'Medizinische Angelegenheiten'],
            ['Code' => 'SOZ', 'Bezeichnung' => 'Soziale Angelegenheiten'],
            ['Code' => 'KON', 'Bezeichnung' => 'Konflikte im Kurs'],
            ['Code' => 'FIN', 'Bezeichnung' => 'Finanzielle Angelegenheiten'],
            ['Code' => 'REC', 'Bezeichnung' => 'Rechtliche Angelegenheiten'],
            ['Code' => 'ABS', 'Bezeichnung' => 'Abschlussgespräch'],
            ['Code' => 'KRI', 'Bezeichnung' => 'Krisenintervention'],
            ['Code' => 'BET', 'Bezeichnung' => 'Betriebskontakte'],
            ['Code' => 'GRU', 'Bezeichnung' => 'Gruppenberatung'],
        ];

        // upsert: не плодим дубликаты, а обновляем Bezeichnung по Code
        DB::table('beratungsarten')->upsert($rows, ['Code'], ['Bezeichnung']);
    }
}
