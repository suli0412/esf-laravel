<?php

return [

    // CEFR-ähnliche Stufen inkl. Alpha/A0 und Feinabstufungen .1/.2
    // (bereinigt von Tippfehlern wie "A1,2", "A2.1.", "B1.1." etc.)
    'deutsch' => [
        'Alpha',
        'A0',
        'A1', 'A1.1', 'A1.2',
        'A2', 'A2.1', 'A2.2',
        'B1', 'B1.1', 'B1.2',
        'B2', 'B2.1', 'B2.2',
        'C1',
    ],

    // Englisch – gleiche Skala, zusätzlich kam einmal "Muttersprache" vor
    'englisch' => [
        'Alpha',
        'A0',
        'A1', 'A1.1', 'A1.2',
        'A2', 'A2.1', 'A2.2',
        'B1', 'B1.1', 'B1.2',
        'B2', 'B2.1', 'B2.2',
        'C1',
        'Muttersprache',
    ],

    // Mathematik – konsistent M0..M3 (im Rohsheet stand auch "A0", das
    // wirkt wie ein Erfassungsfehler; fachlich sauberer ist M0–M3)
    'mathe' => ['M0', 'M1', 'M2', 'M3'],
];
