<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DbErdCommand extends Command
{
    protected $signature = 'db:erd {--out=storage/app} {--png} {--only=} {--except=}';
    protected $description = 'Erzeugt ein ER-Diagramm der laufenden DB (Graphviz DOT, optional PNG). Keine Daten – nur Struktur/FKs.';

    public function handle(): int
    {
        $db     = DB::getDatabaseName();
        $outDir = rtrim($this->option('out') ?: 'storage/app', '/\\');
        if (!is_dir($outDir)) {
            @mkdir($outDir, 0777, true);
        }

        $only   = array_filter(array_map('trim', explode(',', (string)$this->option('only'))));
        $except = array_filter(array_map('trim', explode(',', (string)$this->option('except'))));

        // Tabellenliste
        $tables = collect(DB::select("
            SELECT TABLE_NAME
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = ?
            ORDER BY TABLE_NAME
        ", [$db]))->pluck('TABLE_NAME');

        if ($only)   $tables = $tables->filter(fn($t) => in_array($t, $only, true))->values();
        if ($except) $tables = $tables->reject(fn($t) => in_array($t, $except, true))->values();

        // Helpers: Columns & FKs
        $colsFor = function (string $t) use ($db) {
            return DB::select("
              SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_KEY, IS_NULLABLE, EXTRA, COLUMN_DEFAULT
              FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
              ORDER BY ORDINAL_POSITION
            ", [$db, $t]);
        };
        $fksFor = function (string $t) use ($db) {
            return DB::select("
              SELECT k.COLUMN_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME
              FROM information_schema.KEY_COLUMN_USAGE k
              WHERE k.CONSTRAINT_SCHEMA = ?
                AND k.TABLE_NAME = ?
                AND k.REFERENCED_TABLE_NAME IS NOT NULL
              ORDER BY k.CONSTRAINT_NAME, k.ORDINAL_POSITION
            ", [$db, $t]);
        };

        // 🔒 Sanitizer für Graphviz-Record-Labels
        $sanitize = function (string $s): string {
            // 1) Angle-Brackets wie <auto_increment> entfernen (würden als "port" interpretiert)
            $s = preg_replace('/<[^>]*>/', '', $s);

            // 2) Problemzeichen für Record-Labels maskieren
            // Reihenfolge beachten: zuerst Backslash, dann die übrigen
            $repl = [
                '\\' => '\\\\',
                '"'  => '\\"',
                '{'  => '\\{',
                '}'  => '\\}',
                '|'  => '\\|',
                // Optional: sehr lange ENUMs oder Defaults mit Pipes vermeiden
            ];
            $s = strtr($s, $repl);

            // 3) Unicode bleibt ok (Graphviz kann UTF-8). Zur Sicherheit CR/LF → Space
            $s = str_replace(["\r", "\n"], ' ', $s);

            return $s;
        };

        // DOT bauen (nur ASCII-Steuerzeichen, UTF-8 Text ok)
        $dot  = "digraph ERD {\n";
        $dot .= "  graph [rankdir=LR, splines=true, overlap=false];\n";
        $dot .= "  node  [shape=record, fontsize=10];\n";
        $dot .= "  edge  [arrowsize=0.8];\n";

        foreach ($tables as $t) {
            $fields = [];
            foreach ($colsFor($t) as $c) {
                $mark = ($c->COLUMN_KEY === 'PRI') ? ' (PK)' :
                        (($c->COLUMN_KEY === 'UNI') ? ' (UQ)' :
                        (($c->COLUMN_KEY === 'MUL') ? ' (IDX)' : ''));
                $null = ($c->IS_NULLABLE === 'NO') ? ' NOT NULL' : '';
                $def  = is_null($c->COLUMN_DEFAULT) ? '' : ' DEFAULT=' . $c->COLUMN_DEFAULT;

                // Rohzeile zusammenstellen
                $lineRaw = "{$c->COLUMN_NAME}: {$c->COLUMN_TYPE}{$null}{$def}{$mark}";

                // Sanitize
                $line = $sanitize($lineRaw);

                // \l = left-justified line break im selben Feld
                $fields[] = $line;
            }

            // Ein Record mit 2 Zellen: Tabellennamen | Zeilenliste
            $label = "{ {$sanitize((string)$t)} | " . implode("\\l", $fields) . "\\l }";
            $dot  .= "  \"{$sanitize((string)$t)}\" [label=\"{$label}\"];\n";
        }

        foreach ($tables as $t) {
            foreach ($fksFor($t) as $fk) {
                // Label "col->refcol", sanitized
                $edgeLabel = $sanitize("{$fk->COLUMN_NAME}->{$fk->REFERENCED_COLUMN_NAME}");
                $from = $sanitize((string)$t);
                $to   = $sanitize((string)$fk->REFERENCED_TABLE_NAME);
                $dot .= "  \"{$from}\" -> \"{$to}\" [label=\"{$edgeLabel}\"];\n";
            }
        }

        $dot .= "}\n";

        $dotPath = $outDir . DIRECTORY_SEPARATOR . 'schema.dot';
        file_put_contents($dotPath, $dot);
        $this->info("DOT geschrieben: {$dotPath}");

        if ($this->option('png')) {
            $pngPath = $outDir . DIRECTORY_SEPARATOR . 'schema.png';
            $cmd = 'dot -Tpng ' . escapeshellarg($dotPath) . ' -o ' . escapeshellarg($pngPath);
            @exec($cmd, $o, $code);
            if ($code === 0 && file_exists($pngPath)) {
                $this->info("PNG gerendert: {$pngPath}");
            } else {
                // gib die dot-Ausgabe aus, wenn vorhanden
                $this->warn("dot konnte nicht rendern (Exit-Code {$code}). Führe manuell aus:\n  {$cmd}");
            }
        }

        return self::SUCCESS;
    }
}
