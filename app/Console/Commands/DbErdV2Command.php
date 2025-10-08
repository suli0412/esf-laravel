<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DbErdV2Command extends Command
{
    protected $signature = 'db:erd:v2
        {--out=storage/app : Ausgabeverzeichnis}
        {--format= : Optional direkt rendern: png|svg|pdf}
        {--only= : Nur diese Tabellen (comma)}
        {--except= : Diese Tabellen ausschließen (comma)}
        {--pivot= : Tabellen hart als Pivot markieren (comma)}
        {--no-pivot-auto : Automatische Pivot-Heuristik ausschalten}
        {--cluster=prefix : Clustering: none|prefix|first2}
        {--color-pivot=#EEEFF1 : Pivot-Füllfarbe}
        {--color-node=#FFFFFF : Normale Node-Füllfarbe}
        {--color-border=#888888 : Node-Rahmenfarbe}
        {--color-cluster=#BBBBBB : Cluster-Rahmenfarbe}
        {--max-lines=24 : Ab dieser Feldanzahl zweispaltiger Umbruch}
        {--legend=on : Legende ein/aus (on|off)}';

    protected $description = 'ERD (Graphviz HTML-Labels) mit Pivot-Färbung, Clustern, FK-Regeln, 2-Spalten & Legend.';

    public function handle(): int
    {
        $db     = DB::getDatabaseName();
        $outDir = rtrim($this->option('out') ?: 'storage/app', '/\\');
        if (!is_dir($outDir)) @mkdir($outDir, 0777, true);

        $only      = $this->csvOpt('only');
        $except    = $this->csvOpt('except');
        $pivotList = $this->csvOpt('pivot');
        $pivotAuto = !$this->option('no-pivot-auto');
        $clusterBy = in_array($this->option('cluster') ?: 'prefix', ['none','prefix','first2'], true)
            ? ($this->option('cluster') ?: 'prefix') : 'prefix';

        $COLOR_PIVOT  = (string)($this->option('color-pivot')  ?: '#EEEFF1');
        $COLOR_NODE   = (string)($this->option('color-node')   ?: '#FFFFFF');
        $COLOR_BORDER = (string)($this->option('color-border') ?: '#888888');
        $COLOR_CLUST  = (string)($this->option('color-cluster')?: '#BBBBBB');
        $MAX_LINES    = max(8, (int)($this->option('max-lines') ?: 24));
        $SHOW_LEGEND  = strtolower((string)($this->option('legend') ?: 'on')) !== 'off';

        // Tabellenliste
        $tables = collect(DB::select("
            SELECT TABLE_NAME
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = ?
            ORDER BY TABLE_NAME
        ", [$db]))->pluck('TABLE_NAME');

        if ($only)   $tables = $tables->filter(fn($t) => in_array($t, $only, true))->values();
        if ($except) $tables = $tables->reject(fn($t) => in_array($t, $except, true))->values();

        // Helper-Queries
        $colsFor = function (string $t) use ($db) {
            return DB::select("
              SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_KEY, IS_NULLABLE, EXTRA, COLUMN_DEFAULT
              FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
              ORDER BY ORDINAL_POSITION
            ", [$db, $t]);
        };
        $pkColsFor = function (string $t) use ($db) {
            return collect(DB::select("
              SELECT COLUMN_NAME
              FROM information_schema.KEY_COLUMN_USAGE
              WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = ?
                AND CONSTRAINT_NAME = 'PRIMARY'
              ORDER BY ORDINAL_POSITION
            ", [$db, $t]))->pluck('COLUMN_NAME')->all();
        };
        $fksFor = function (string $t) use ($db) {
            return DB::select("
              SELECT k.CONSTRAINT_NAME, k.COLUMN_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME,
                     r.DELETE_RULE, r.UPDATE_RULE
              FROM information_schema.KEY_COLUMN_USAGE k
              JOIN information_schema.REFERENTIAL_CONSTRAINTS r
                ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
               AND r.CONSTRAINT_NAME   = k.CONSTRAINT_NAME
              WHERE k.CONSTRAINT_SCHEMA = ?
                AND k.TABLE_NAME = ?
                AND k.REFERENCED_TABLE_NAME IS NOT NULL
              ORDER BY k.CONSTRAINT_NAME, k.ORDINAL_POSITION
            ", [$db, $t]);
        };

        // HTML-Label Sanitizer
        $html = function (string $s): string {
            $s = preg_replace('/<[^>]*>/', '', $s); // <auto_increment> etc. entfernen
            $s = str_replace(["\r\n", "\r", "\n"], ' ', $s);
            return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        };

        // Grundlayout
        $dot  = "digraph ERD {\n";
        $dot .= "  graph [rankdir=LR, splines=true, overlap=false, fontname=\"Arial\"];\n";
        $dot .= "  node  [shape=plain, fontsize=10, fontname=\"Arial\"];\n";
        $dot .= "  edge  [arrowsize=0.8, fontname=\"Arial\", fontsize=9];\n";

        // Metadaten je Tabelle
        $meta = [];
        foreach ($tables as $t) {
            $cols = $colsFor($t);
            $pks  = $pkColsFor($t);
            $fks  = $fksFor($t);

            $isCompositePk = count($pks) >= 2;
            $autoPivot = (count($fks) >= 2) && (count($cols) <= 4 || strpos($t, '_') !== false || $isCompositePk);
            $hardPivot = in_array($t, $pivotList, true);
            $isPivot   = $hardPivot || ($pivotAuto && $autoPivot);

            // Cluster-Strategie
            $cluster = 'misc';
            if ($clusterBy === 'none') {
                $cluster = 'schema';
            } elseif ($clusterBy === 'prefix') {
                $cluster = (strpos($t, '_') !== false) ? substr($t, 0, strpos($t, '_')) : 'misc';
            } else { // first2
                $cluster = substr((string)$t, 0, 2);
            }

            $meta[$t] = compact('cols','pks','fks') + [
                'pivot'   => $isPivot,
                'cluster' => $cluster,
            ];
        }

        // Cluster sammeln
        $clusters = [];
        foreach ($meta as $t => $m) {
            $clusters[$m['cluster']][] = $t;
        }

        // Node-Renderer (HTML-Table; 2 Spalten ab MAX_LINES)
        $renderNode = function (string $t, array $m) use ($html, $COLOR_PIVOT, $COLOR_NODE, $COLOR_BORDER, $MAX_LINES) {
            $cols  = $m['cols'];
            $pivot = $m['pivot'];

            $lines = [];
            foreach ($cols as $c) {
                $mark = ($c->COLUMN_KEY === 'PRI') ? ' (PK)' :
                        (($c->COLUMN_KEY === 'UNI') ? ' (UQ)' :
                        (($c->COLUMN_KEY === 'MUL') ? ' (IDX)' : ''));
                $null = ($c->IS_NULLABLE === 'NO') ? ' NOT NULL' : '';
                $def  = is_null($c->COLUMN_DEFAULT) ? '' : ' DEFAULT=' . (string)$c->COLUMN_DEFAULT;

                $lines[] = $html("{$c->COLUMN_NAME}: {$c->COLUMN_TYPE}{$null}{$def}{$mark}");
            }

            $twoCols = count($lines) > $MAX_LINES;
            if ($twoCols) {
                $half = (int)ceil(count($lines) / 2);
                $left  = implode("<BR ALIGN=\"LEFT\"/>", array_slice($lines, 0, $half));
                $right = implode("<BR ALIGN=\"LEFT\"/>", array_slice($lines, $half));
                $body  = "<TR><TD ALIGN=\"LEFT\" VALIGN=\"TOP\"><FONT POINT-SIZE=\"9\">{$left}</FONT></TD>"
                       . "<TD ALIGN=\"LEFT\" VALIGN=\"TOP\"><FONT POINT-SIZE=\"9\">{$right}</FONT></TD></TR>";
            } else {
                $body  = "<TR><TD ALIGN=\"LEFT\" VALIGN=\"TOP\"><FONT POINT-SIZE=\"9\">"
                       . implode("<BR ALIGN=\"LEFT\"/>", $lines)
                       . "</FONT></TD></TR>";
            }

            $fill   = $pivot ? $COLOR_PIVOT : $COLOR_NODE;
            $border = $COLOR_BORDER;

            $label = '<<TABLE BORDER="1" CELLBORDER="0" CELLSPACING="0" BGCOLOR="' . $fill . '" COLOR="' . $border . '">'
                   . '<TR><TD BGCOLOR="#F6F6F6" ALIGN="LEFT"><B>' . $html($t) . '</B></TD></TR>'
                   . $body
                   . '</TABLE>>';

            return $label;
        };

        // Cluster + Nodes
        $i = 0;
        foreach ($clusters as $prefix => $tbls) {
            $clusterName = "cluster_" . (++$i) . "_" . preg_replace('/[^A-Za-z0-9_]/', '_', (string)$prefix);
            $dot .= "  subgraph $clusterName {\n";
            $dot .= "    label = \"" . $html((string)$prefix) . "\";\n";
            $dot .= "    color = \"{$COLOR_CLUST}\";\n";
            $dot .= "    style = \"rounded\";\n";
            foreach ($tbls as $t) {
                $dot .= "    \"{$t}\" [label=" . $renderNode($t, $meta[$t]) . "];\n";
            }
            $dot .= "  }\n";
        }

        // Edges: Style je nach Regel
        foreach ($meta as $t => $m) {
            foreach ($m['fks'] as $fk) {
                $to = (string)$fk->REFERENCED_TABLE_NAME;
                if (!isset($meta[$to])) continue;

                $edgeLabel = "{$fk->COLUMN_NAME} -> {$fk->REFERENCED_COLUMN_NAME} \\nDEL:{$fk->DELETE_RULE} / UPD:{$fk->UPDATE_RULE}";
                $edgeLabel = htmlspecialchars($edgeLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                $style = 'solid';
                $penwidth = '1.2';
                if (strtoupper($fk->DELETE_RULE) === 'CASCADE' || strtoupper($fk->UPDATE_RULE) === 'CASCADE') {
                    $penwidth = '2.0';
                } elseif (in_array(strtoupper($fk->DELETE_RULE), ['SET NULL']) || in_array(strtoupper($fk->UPDATE_RULE), ['SET NULL'])) {
                    $style = 'dashed';
                }

                $dot .= "  \"{$t}\" -> \"{$to}\" [label=\"{$edgeLabel}\", style=\"{$style}\", penwidth=\"{$penwidth}\"];\n";
            }
        }

        // ---------- Legend (optional) ----------
        if ($SHOW_LEGEND) {
            $dot .= $this->legendSubgraph($COLOR_NODE, $COLOR_PIVOT, $COLOR_BORDER);
        }

        $dot .= "}\n";

        $dotPath = $outDir . DIRECTORY_SEPARATOR . 'schema.dot';
        file_put_contents($dotPath, $dot);
        $this->info("DOT geschrieben: {$dotPath}");

        $format = strtolower((string)$this->option('format'));
        if (in_array($format, ['png','svg','pdf'], true)) {
            $outPath = $outDir . DIRECTORY_SEPARATOR . "schema.{$format}";
            $cmd = 'dot -T' . escapeshellarg($format) . ' ' . escapeshellarg($dotPath) . ' -o ' . escapeshellarg($outPath);
            @exec($cmd, $o, $code);
            if ($code === 0 && file_exists($outPath)) {
                $this->info(strtoupper($format) . " gerendert: {$outPath}");
            } else {
                $this->warn("dot konnte nicht rendern (Exit-Code {$code}). Führe manuell aus:\n  {$cmd}");
            }
        }

        return self::SUCCESS;
    }

    private function legendSubgraph(string $colorNode, string $colorPivot, string $colorBorder): string
    {
        // kleine HTML-Table als Legende
        $nodeLabel = htmlentities('Tabelle (normal)', ENT_QUOTES, 'UTF-8');
        $pivotLabel = htmlentities('Pivot/Junction-Tabelle', ENT_QUOTES, 'UTF-8');
        $pk = htmlentities('PK = Primary Key', ENT_QUOTES, 'UTF-8');
        $uq = htmlentities('UQ = Unique Key', ENT_QUOTES, 'UTF-8');
        $idx = htmlentities('IDX = Index', ENT_QUOTES, 'UTF-8');
        $del = htmlentities('DEL: ON DELETE-Regel', ENT_QUOTES, 'UTF-8');
        $upd = htmlentities('UPD: ON UPDATE-Regel', ENT_QUOTES, 'UTF-8');

        $legend  = "  subgraph cluster_legend {\n";
        $legend .= "    label = \"Legend\";\n";
        $legend .= "    color = \"#999999\";\n";
        $legend .= "    style = \"rounded, dashed\";\n";

        // Beispielnode normal
        $legend .= "    \"legend_node\" [label=<<TABLE BORDER=\"1\" CELLBORDER=\"0\" CELLSPACING=\"0\" BGCOLOR=\"{$colorNode}\" COLOR=\"{$colorBorder}\">"
                 . "<TR><TD BGCOLOR=\"#F6F6F6\" ALIGN=\"LEFT\"><B>{$nodeLabel}</B></TD></TR>"
                 . "<TR><TD ALIGN=\"LEFT\"><FONT POINT-SIZE=\"9\">spalte: typ NOT NULL (PK/UQ/IDX)</FONT></TD></TR>"
                 . "</TABLE>>];\n";

        // Beispielnode pivot
        $legend .= "    \"legend_pivot\" [label=<<TABLE BORDER=\"1\" CELLBORDER=\"0\" CELLSPACING=\"0\" BGCOLOR=\"{$colorPivot}\" COLOR=\"{$colorBorder}\">"
                 . "<TR><TD BGCOLOR=\"#F6F6F6\" ALIGN=\"LEFT\"><B>{$pivotLabel}</B></TD></TR>"
                 . "<TR><TD ALIGN=\"LEFT\"><FONT POINT-SIZE=\"9\">fk_a_id: bigint (IDX)<BR ALIGN=\"LEFT\"/>fk_b_id: bigint (IDX)</FONT></TD></TR>"
                 . "</TABLE>>];\n";

        // Kantenstile
        $legend .= "    \"legend_edge_solid\" [shape=plaintext, label=\"FK (DEL/UPD: normal)\"];\n";
        $legend .= "    \"legend_edge_bold\"   [shape=plaintext, label=\"FK (DEL/UPD: CASCADE)\"];\n";
        $legend .= "    \"legend_edge_dash\"   [shape=plaintext, label=\"FK (DEL/UPD: SET NULL)\"];\n";

        $legend .= "    \"legend_node\" -> \"legend_pivot\" [label=\"col -> refcol \\nDEL:RESTRICT / UPD:NO ACTION\", style=\"solid\", penwidth=\"1.2\"];\n";
        $legend .= "    \"legend_node\" -> \"legend_edge_bold\" [style=\"solid\", penwidth=\"2.0\"];\n";
        $legend .= "    \"legend_node\" -> \"legend_edge_dash\" [style=\"dashed\", penwidth=\"1.2\"];\n";

        $legend .= "    \"legend_explain\" [shape=plaintext, label=<<TABLE BORDER=\"0\" CELLBORDER=\"0\" CELLSPACING=\"0\">"
                 . "<TR><TD ALIGN=\"LEFT\">{$pk}</TD></TR>"
                 . "<TR><TD ALIGN=\"LEFT\">{$uq}</TD></TR>"
                 . "<TR><TD ALIGN=\"LEFT\">{$idx}</TD></TR>"
                 . "<TR><TD ALIGN=\"LEFT\">{$del}</TD></TR>"
                 . "<TR><TD ALIGN=\"LEFT\">{$upd}</TD></TR>"
                 . "</TABLE>>];\n";

        $legend .= "  }\n";
        return $legend;
    }

    private function csvOpt(string $name): array
    {
        $val = (string)$this->option($name);
        if (!$val) return [];
        return array_values(array_filter(array_map('trim', explode(',', $val))));
    }
}
