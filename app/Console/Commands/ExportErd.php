<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportErd extends Command
{
    protected $signature = 'db:erd {--out=storage/app} {--png} {--only=} {--except=}';
    protected $description = 'Exportiert ER-Diagramm (Graphviz DOT) aus information_schema; optional PNG.';

    public function handle(): int
    {
        $db     = DB::getDatabaseName();
        $outDir = rtrim($this->option('out') ?: 'storage/app', '/');
        @mkdir($outDir, 0777, true);

        $only   = array_filter(array_map('trim', explode(',', (string)$this->option('only'))));
        $except = array_filter(array_map('trim', explode(',', (string)$this->option('except'))));

        $tables = collect(DB::select("
            SELECT TABLE_NAME
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = ?
            ORDER BY TABLE_NAME
        ", [$db]))->pluck('TABLE_NAME')->values();

        if ($only)   $tables = $tables->filter(fn($t)=>in_array($t, $only, true))->values();
        if ($except) $tables = $tables->reject(fn($t)=>in_array($t, $except, true))->values();

        $colsFor = function(string $t) use ($db) {
            return DB::select("
                SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_KEY, IS_NULLABLE, EXTRA
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA=? AND TABLE_NAME=?
                ORDER BY ORDINAL_POSITION
            ", [$db, $t]);
        };

        $fksFor = function(string $t) use ($db) {
            return DB::select("
                SELECT k.CONSTRAINT_NAME, k.COLUMN_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE k
                WHERE k.CONSTRAINT_SCHEMA=? AND k.TABLE_NAME=? AND k.REFERENCED_TABLE_NAME IS NOT NULL
                ORDER BY k.CONSTRAINT_NAME, k.ORDINAL_POSITION
            ", [$db, $t]);
        };

        // Graphviz DOT bauen
        $dot  = "digraph ERD {\n";
        $dot .= "  graph [rankdir=LR, splines=true, overlap=false];\n";
        $dot .= "  node  [shape=record, fontsize=10];\n";
        $dot .= "  edge  [arrowsize=0.8];\n";

        foreach ($tables as $t) {
            $cols = $colsFor($t);
            // Feldlabel: PK/UK/MUL markieren
            $fields = [];
            foreach ($cols as $c) {
                $key = $c->COLUMN_KEY ?: '';
                $mark = $key==='PRI' ? ' (PK)' : ($key==='UNI' ? ' (UQ)' : ($key==='MUL' ? ' (IDX)' : ''));
                $null = $c->IS_NULLABLE === 'NO' ? ' NOT NULL' : '';
                $fields[] = sprintf("%s: %s%s%s",
                    $c->COLUMN_NAME, $c->COLUMN_TYPE, $null, $c->EXTRA ? " <{$c->EXTRA}>" : ""
                ).$mark;
            }
            $label = "{ {$t} | ".implode("\\l", array_map(fn($x)=>addslashes($x), $fields))."\\l }";
            $dot .= "  \"{$t}\" [label=\"{$label}\"];\n";
        }

        foreach ($tables as $t) {
            foreach ($fksFor($t) as $fk) {
                if (!$tables->contains($fk->REFERENCED_TABLE_NAME)) continue; // falls gefiltert
                $dot .= "  \"{$t}\" -> \"{$fk->REFERENCED_TABLE_NAME}\" [label=\"{$fk->COLUMN_NAME}→{$fk->REFERENCED_COLUMN_NAME}\"];\n";
            }
        }

        $dot .= "}\n";

        $dotPath = "{$outDir}/schema.dot";
        file_put_contents($dotPath, $dot);
        $this->info("DOT geschrieben: {$dotPath}");

        if ($this->option('png')) {
            // versucht Graphviz zu benutzen, ansonsten Hinweis
            $pngPath = "{$outDir}/schema.png";
            $cmd = "dot -Tpng ".escapeshellarg($dotPath)." -o ".escapeshellarg($pngPath);
            @exec($cmd, $o, $code);
            if ($code === 0 && is_file($pngPath)) {
                $this->info("PNG gerendert: {$pngPath}");
            } else {
                $this->warn("Graphviz nicht gefunden. Installiere es und rendere manuell:\n  dot -Tpng {$dotPath} -o {$pngPath}");
            }
        }

        return self::SUCCESS;
    }
}
