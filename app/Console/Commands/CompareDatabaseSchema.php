<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class CompareDatabaseSchema extends Command
{
    protected $signature = 'db:compare-schema
                            {--remote : Compare default (local) DB against REMOTE_DB_* connection}
                            {--local= : Local connection name (default: app default)}
                            {--against=remote : Remote connection name}
                            {--export= : Save report to a text file}';

    protected $description = 'Compare table/column structure between local and live (remote) databases';

    public function handle(): int
    {
        $localConnection = $this->option('local') ?: config('database.default');
        $remoteConnection = $this->option('against');
        $wantsRemoteCompare = (bool) $this->option('remote');

        if ($wantsRemoteCompare && ! env('REMOTE_DB_DATABASE')) {
            $this->error('Remote database is not configured.');
            $this->line('');
            $this->line('Add REMOTE_DB_* to your .env (see .env.example), then run:');
            $this->line('  php artisan db:compare-schema --remote');

            return self::FAILURE;
        }

        if (! $wantsRemoteCompare && ! $this->option('export')) {
            $this->error('Choose one mode:');
            $this->line('  php artisan db:compare-schema --remote');
            $this->line('  php artisan db:compare-schema --export=storage/app/local-schema.txt');

            return self::FAILURE;
        }

        try {
            $localSchema = $this->loadSchema($localConnection, 'LOCAL');
            $remoteSchema = $wantsRemoteCompare
                ? $this->loadSchema($remoteConnection, 'REMOTE')
                : null;
        } catch (Throwable $e) {
            $this->error('Could not read database schema: '.$e->getMessage());

            return self::FAILURE;
        }

        if ($remoteSchema === null) {
            $exportPath = $this->option('export') ?: 'storage/app/schema-'.config('database.connections.'.$localConnection.'.database', 'local').'.txt';
            $this->exportSchemaSnapshot($localSchema, $localConnection, $exportPath);
            $this->info('Schema snapshot saved: '.$exportPath);
            $this->line('Run the same on live, then diff the two files.');

            return self::SUCCESS;
        }

        $report = $this->buildDiffReport($localSchema, $remoteSchema);
        $this->outputReport($report);

        if ($path = $this->option('export')) {
            file_put_contents($path, $report['text']);
            $this->info('Report saved: '.$path);
        }

        return $report['has_mismatches'] ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return array{database: string, tables: array<string, array<string, array<string, mixed>>>}
     */
    private function loadSchema(string $connection, string $label): array
    {
        $database = (string) config("database.connections.{$connection}.database");
        if ($database === '') {
            throw new \RuntimeException("{$label}: database name is empty for connection [{$connection}].");
        }

        DB::connection($connection)->getPdo();

        $rows = DB::connection($connection)->select(
            'SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, COLUMN_DEFAULT, EXTRA
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ?
             ORDER BY TABLE_NAME, ORDINAL_POSITION',
            [$database]
        );

        $tables = [];
        foreach ($rows as $row) {
            $table = (string) $row->TABLE_NAME;
            $column = (string) $row->COLUMN_NAME;
            $tables[$table][$column] = [
                'type' => strtolower((string) $row->COLUMN_TYPE),
                'nullable' => (string) $row->IS_NULLABLE,
                'key' => (string) $row->COLUMN_KEY,
                'default' => $row->COLUMN_DEFAULT,
                'extra' => strtolower((string) $row->EXTRA),
            ];
        }

        ksort($tables);

        return [
            'database' => $database,
            'connection' => $connection,
            'tables' => $tables,
        ];
    }

    /**
     * @param  array{database: string, tables: array<string, array<string, array<string, mixed>>>}  $local
     * @param  array{database: string, tables: array<string, array<string, array<string, mixed>>>}  $remote
     * @return array{has_mismatches: bool, text: string, sections: array<string, array<int, string>>}
     */
    private function buildDiffReport(array $local, array $remote): array
    {
        $lines = [];
        $sections = [
            'tables_only_local' => [],
            'tables_only_remote' => [],
            'columns_only_local' => [],
            'columns_only_remote' => [],
            'column_mismatches' => [],
        ];

        $lines[] = 'DATABASE SCHEMA COMPARISON';
        $lines[] = '==========================';
        $lines[] = 'LOCAL  ('.$local['connection'].'): '.$local['database'];
        $lines[] = 'REMOTE ('.$remote['connection'].'): '.$remote['database'];
        $lines[] = 'Generated: '.now()->toDateTimeString();
        $lines[] = '';

        $localTables = array_keys($local['tables']);
        $remoteTables = array_keys($remote['tables']);

        $onlyLocalTables = array_values(array_diff($localTables, $remoteTables));
        $onlyRemoteTables = array_values(array_diff($remoteTables, $localTables));
        $commonTables = array_values(array_intersect($localTables, $remoteTables));

        if ($onlyLocalTables !== []) {
            $sections['tables_only_local'] = $onlyLocalTables;
            $lines[] = 'TABLES ONLY ON LOCAL ('.count($onlyLocalTables).')';
            foreach ($onlyLocalTables as $table) {
                $lines[] = '  + '.$table;
            }
            $lines[] = '';
        }

        if ($onlyRemoteTables !== []) {
            $sections['tables_only_remote'] = $onlyRemoteTables;
            $lines[] = 'TABLES ONLY ON REMOTE / LIVE ('.count($onlyRemoteTables).')';
            foreach ($onlyRemoteTables as $table) {
                $lines[] = '  - '.$table;
            }
            $lines[] = '';
        }

        foreach ($commonTables as $table) {
            $localCols = $local['tables'][$table];
            $remoteCols = $remote['tables'][$table];

            $onlyLocalCols = array_diff(array_keys($localCols), array_keys($remoteCols));
            $onlyRemoteCols = array_diff(array_keys($remoteCols), array_keys($localCols));

            foreach ($onlyLocalCols as $col) {
                $msg = "{$table}.{$col} — on LOCAL only ({$localCols[$col]['type']})";
                $sections['columns_only_local'][] = $msg;
                $lines[] = 'COLUMN LOCAL ONLY: '.$msg;
            }

            foreach ($onlyRemoteCols as $col) {
                $msg = "{$table}.{$col} — on REMOTE only ({$remoteCols[$col]['type']})";
                $sections['columns_only_remote'][] = $msg;
                $lines[] = 'COLUMN REMOTE ONLY: '.$msg;
            }

            foreach (array_intersect(array_keys($localCols), array_keys($remoteCols)) as $col) {
                $diffs = $this->columnDiffs($localCols[$col], $remoteCols[$col]);
                if ($diffs === []) {
                    continue;
                }

                $msg = "{$table}.{$col}: ".implode('; ', $diffs);
                $sections['column_mismatches'][] = $msg;
                $lines[] = 'COLUMN MISMATCH: '.$msg;
            }
        }

        $hasMismatches = $onlyLocalTables !== []
            || $onlyRemoteTables !== []
            || $sections['columns_only_local'] !== []
            || $sections['columns_only_remote'] !== []
            || $sections['column_mismatches'] !== [];

        if (! $hasMismatches) {
            $lines[] = 'OK — No table/column mismatches found.';
        } else {
            $lines[] = '';
            $lines[] = 'SUMMARY';
            $lines[] = '-------';
            $lines[] = 'Tables only local:  '.count($sections['tables_only_local']);
            $lines[] = 'Tables only remote: '.count($sections['tables_only_remote']);
            $lines[] = 'Columns only local: '.count($sections['columns_only_local']);
            $lines[] = 'Columns only remote: '.count($sections['columns_only_remote']);
            $lines[] = 'Column mismatches:  '.count($sections['column_mismatches']);
        }

        return [
            'has_mismatches' => $hasMismatches,
            'text' => implode(PHP_EOL, $lines),
            'sections' => $sections,
        ];
    }

    /**
     * @param  array{type: string, nullable: string, key: string, default: mixed, extra: string}  $a
     * @param  array{type: string, nullable: string, key: string, default: mixed, extra: string}  $b
     * @return list<string>
     */
    private function columnDiffs(array $a, array $b): array
    {
        $diffs = [];

        if ($a['type'] !== $b['type']) {
            $diffs[] = "type local={$a['type']} remote={$b['type']}";
        }
        if ($a['nullable'] !== $b['nullable']) {
            $diffs[] = "nullable local={$a['nullable']} remote={$b['nullable']}";
        }
        if ($a['key'] !== $b['key']) {
            $diffs[] = "key local={$a['key']} remote={$b['key']}";
        }
        if ((string) $a['default'] !== (string) $b['default']) {
            $diffs[] = 'default differs';
        }
        if ($a['extra'] !== $b['extra']) {
            $diffs[] = "extra local={$a['extra']} remote={$b['extra']}";
        }

        return $diffs;
    }

    /**
     * @param  array{has_mismatches: bool, text: string, sections: array<string, array<int, string>>}  $report
     */
    private function outputReport(array $report): void
    {
        if (! $report['has_mismatches']) {
            $this->info('No mismatches — local and remote schemas match.');

            return;
        }

        $this->warn('Schema mismatches found:');
        $this->line('');

        foreach ($report['sections'] as $key => $items) {
            if ($items === []) {
                continue;
            }

            $title = match ($key) {
                'tables_only_local' => 'Tables only on LOCAL',
                'tables_only_remote' => 'Tables only on REMOTE (live)',
                'columns_only_local' => 'Columns only on LOCAL',
                'columns_only_remote' => 'Columns only on REMOTE (live)',
                'column_mismatches' => 'Column definition mismatches',
                default => $key,
            };

            $this->line("<fg=yellow>{$title}</> (".count($items).')');
            foreach ($items as $item) {
                $this->line('  • '.$item);
            }
            $this->line('');
        }
    }

    /**
     * @param  array{database: string, tables: array<string, array<string, array<string, mixed>>>}  $schema
     */
    private function exportSchemaSnapshot(array $schema, string $connection, string $path): void
    {
        $lines = [];
        $lines[] = 'SCHEMA SNAPSHOT: '.$schema['database'].' ('.$connection.')';
        $lines[] = 'Generated: '.now()->toDateTimeString();
        $lines[] = '';

        foreach ($schema['tables'] as $table => $columns) {
            $lines[] = 'TABLE '.$table;
            foreach ($columns as $name => $meta) {
                $lines[] = sprintf(
                    '  %s | %s | nullable=%s | key=%s | default=%s | %s',
                    $name,
                    $meta['type'],
                    $meta['nullable'],
                    $meta['key'] ?: '-',
                    $meta['default'] === null ? 'NULL' : (string) $meta['default'],
                    $meta['extra'] ?: '-'
                );
            }
            $lines[] = '';
        }

        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, implode(PHP_EOL, $lines));
    }
}
