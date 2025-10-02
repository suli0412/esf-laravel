<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teilnehmer;
use Illuminate\Support\Carbon;

class TeilnehmerSeeder extends Seeder
{
    public function run(): void
    {
        // Feste Datensätze – laufen gefahrlos mehrfach
        $fixed = [
            [
                'Nachname' => 'Mustermann1',
                'Vorname'  => 'Max',
                'Email'    => 'test1@example.com',
                'Geburtsdatum' => '2004-09-24',
                'SVN'      => '009373746776',
                'de_lesen_in'     => 'B2.1',
                'de_hoeren_in'    => 'A2',
                'de_schreiben_in' => 'Alpha',
                'de_sprechen_in'  => 'B2',
                'en_in'           => 'A2.2',
                'ma_in'           => 'M3',
                'de_lesen_out'     => 'A0',
                'de_hoeren_out'    => 'A1.2',
                'de_schreiben_out' => 'B2.2',
                'de_sprechen_out'  => '?',       // wird in deiner App-Logik später ggf. normalisiert
                'en_out'           => 'A1.2',
                'ma_out'           => 'M0',
            ],
            // weitere feste Datensätze hier …
        ];

        foreach ($fixed as $row) {
            // verhindert Duplikate anhand der Email
            Teilnehmer::updateOrCreate(
                ['Email' => $row['Email']],
                $row + ['updated_at' => now(), 'created_at' => now()]
            );
        }

        // Optional: zusätzliche Dummy-Daten mit einzigartigen E-Mails
        $faker = \Faker\Factory::create('de_AT');
        for ($i = 0; $i < 20; $i++) {
            $email = $faker->unique()->safeEmail();
            Teilnehmer::updateOrCreate(
                ['Email' => $email],
                [
                    'Nachname' => $faker->lastName(),
                    'Vorname'  => $faker->firstName(),
                    'Email'    => $email,
                    'Geburtsdatum' => Carbon::now()->subYears(rand(18, 40))->format('Y-m-d'),
                    'SVN'      => (string) rand(100000000000, 999999999999),
                    'de_lesen_in'     => 'A1',
                    'de_hoeren_in'    => 'A1',
                    'de_schreiben_in' => 'A1',
                    'de_sprechen_in'  => 'A1',
                    'en_in'           => 'A1',
                    'ma_in'           => 'M1',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]
            );
        }

        // Reset für Faker-Unique (falls Seeder mehrfach läuft)
        $faker->unique(true);
    }
}
