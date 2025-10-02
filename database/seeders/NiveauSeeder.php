<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NiveauSeeder extends Seeder
{
    public function run(): void
    {

         $this->call([
            NiveauSeeder::class,
        ]);

        $rows = [
            ['code'=>'A0',   'label'=>'A0',   'sort_order'=> 0],
            ['code'=>'A1',   'label'=>'A1',   'sort_order'=>10],
            ['code'=>'A1.1', 'label'=>'A1.1', 'sort_order'=>11],
            ['code'=>'A1.2', 'label'=>'A1.2', 'sort_order'=>12],
            ['code'=>'A2',   'label'=>'A2',   'sort_order'=>20],
            ['code'=>'A2.1', 'label'=>'A2.1', 'sort_order'=>21],
            ['code'=>'A2.2', 'label'=>'A2.2', 'sort_order'=>22],
            ['code'=>'B1',   'label'=>'B1',   'sort_order'=>30],
            ['code'=>'B1.1', 'label'=>'B1.1', 'sort_order'=>31],
            ['code'=>'B1.1+','label'=>'B1.1+','sort_order'=>32],
            ['code'=>'B1.2', 'label'=>'B1.2', 'sort_order'=>33],
            ['code'=>'B2',   'label'=>'B2',   'sort_order'=>40],
            ['code'=>'B2.1', 'label'=>'B2.1', 'sort_order'=>41],
            ['code'=>'B2.2', 'label'=>'B2.2', 'sort_order'=>42],
            ['code'=>'C1',   'label'=>'C1',   'sort_order'=>50],
            ['code'=>'C2',   'label'=>'C2',   'sort_order'=>60],
        ];
        // Reihenfolge = sort_order
        $levels = [
            // elementar
            ['A0',   'A0 – Elementare Vorstufe'],
            ['A1',   'A1 – Elementare Sprachverwendung'],
            ['A1.1', 'A1.1 – Elementare Sprachverwendung (Unterstufe)'],
            ['A1.2', 'A1.2 – Elementare Sprachverwendung (Oberstufe)'],

            ['A2',   'A2 – Elementare Sprachverwendung'],
            ['A2.1', 'A2.1 – Elementare Sprachverwendung (Unterstufe)'],
            ['A2.2', 'A2.2 – Elementare Sprachverwendung (Oberstufe)'],

            // selbstständig
            ['B1',   'B1 – Selbstständige Sprachverwendung'],
            ['B1.1', 'B1.1 – Selbstständige Sprachverwendung (Unterstufe)'],
            ['B1.1+','B1.1+ – Selbstständige Sprachverwendung (erweitert)'],
            ['B1.2', 'B1.2 – Selbstständige Sprachverwendung (Oberstufe)'],

            ['B2',   'B2 – Selbstständige Sprachverwendung'],
            ['B2.1', 'B2.1 – Selbstständige Sprachverwendung (Unterstufe)'],
            ['B2.2', 'B2.2 – Selbstständige Sprachverwendung (Oberstufe)'],

            // kompetent
            ['C1',   'C1 – Kompetente Sprachverwendung'],
            ['C2',   'C2 – Kompetente Sprachverwendung'],
        ];

        $order = 0;
        foreach ($levels as [$code, $label]) {
            DB::table('niveau')->updateOrInsert(
                ['code' => $code],
                ['label' => $label, 'sort_order' => ++$order]
            );
        }
    }

}
