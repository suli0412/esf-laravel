<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BeratungsthemenSeeder extends Seeder
{
    public function run(): void
    {
        // Короткое имя в Bezeichnung (<=120), подробности в Beschreibung
        $rows = [
            [
                'Bezeichnung'  => 'Kennenlernen / Biographie',
                'Beschreibung' => 'Kennenlernen, Beziehungsaufbau, Datenerhebung, Biographie',
            ],
            [
                'Bezeichnung'  => 'Bewerbung & Beruf',
                'Beschreibung' => 'Lebenslauf und Bewerbungsunterlagen, Berufsorientierung, Zielfindung, Lehrstellen und Praktikumssuche, Vorbereitung Betriebserkundung, Bewerbungstraining, Üben für Einstufungstest',
            ],
            [
                'Bezeichnung'  => 'Lernen / Lerntechniken',
                'Beschreibung' => 'Lernmethoden, Lerntechniken, Nachhilfe',
            ],
            [
                'Bezeichnung'  => 'Kursorganisation',
                'Beschreibung' => 'Anmeldung, Unterricht, Gruppenwechsel, Bestätigungen',
            ],
            [
                'Bezeichnung'  => 'Alltag & Behörden / Netzwerke',
                'Beschreibung' => 'Kinderbetreuung, Wohnungssuche und -angelegenheiten, Formulare für Ämter, Vernetzungen mit anderen Helfersystemen',
            ],
            [
                'Bezeichnung'  => 'Psychische Gesundheit',
                'Beschreibung' => 'Depressionen, Schlafstörungen, Gereiztheit, PTBS, schwere Lebenskrisen',
            ],
            [
                'Bezeichnung'  => 'Medizinische Themen',
                'Beschreibung' => 'Arzttermine, Gesundheitsthemen',
            ],
            [
                'Bezeichnung'  => 'Soziales / Familie / Freizeit',
                'Beschreibung' => 'Beziehungsaufbau, Freizeitgestaltung (Sport), Familiäre Themen, Konflikte im sozialen Miteinander',
            ],
            [
                'Bezeichnung'  => 'Konflikte im Kurs',
                'Beschreibung' => 'Konflikte zwischen TeilnehmerInnen und/oder TrainerInnen',
            ],
            [
                'Bezeichnung'  => 'Finanzen',
                'Beschreibung' => 'Mindestsicherung, AMS Geld, Bankangelegenheiten, Strafen, Mietrückstände',
            ],
            [
                'Bezeichnung'  => 'Recht',
                'Beschreibung' => 'Arbeitsrecht, Asylverfahren, Vorbereitung Interview, Kontakte zu Anwälten, Polizei- und Gerichtkontakte',
            ],
            [
                'Bezeichnung'  => 'Abschluss / Zukunftsplanung',
                'Beschreibung' => 'Reflexion über die Zeit, Plan für die Zukunft, Abschlussbericht',
            ],
            [
                'Bezeichnung'  => 'Krisen',
                'Beschreibung' => 'Ehekrisen, persönliche Krisen, ausufernde Konflikte im Kurs',
            ],
            [
                'Bezeichnung'  => 'Betriebskontakte',
                'Beschreibung' => 'Vernetzung mit Dienstgebern und Praktikumsstellen',
            ],
            [
                'Bezeichnung'  => 'Gruppenberatung',
                'Beschreibung' => 'Beratung mit mehreren TeilnehmerInnen',
            ],
        ];

        DB::table('beratungsthemen')->upsert($rows, ['Bezeichnung'], ['Beschreibung']);
    }
}
