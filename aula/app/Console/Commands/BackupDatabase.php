<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--path=} {--keep=14}';

    protected $description = 'Crea una copia de seguridad de la base de datos';

    public function handle(): int
    {
        $driver = DB::connection()->getDriverName();

        if ($driver !== 'sqlite' && $driver !== 'mysql') {
            $this->error("Driver '{$driver}' no soportado por este comando.");

            return self::FAILURE;
        }

        $path = $this->option('path') ?: storage_path('app/backups');
        $this->ensureDirectory($path);

        $filename = 'backup-'.now()->format('Y-m-d_H-i-s').".{$driver}";
        $destination = rtrim($path, '/').'/'.$filename;

        if ($driver === 'sqlite') {
            copy(database_path('database.sqlite'), $destination);
        } elseif ($driver === 'mysql') {
            $this->runMysqlDump($destination);
        }

        $this->pruneOld($path, (int) $this->option('keep'));
        $this->info("Copia de seguridad creada: {$destination}");

        return self::SUCCESS;
    }

    private function ensureDirectory(string $path): void
    {
        if (! is_dir($path) && ! mkdir($path, 0755, true) && ! is_dir($path)) {
            $this->error("No se pudo crear el directorio {$path}");
        }
    }

    private function runMysqlDump(string $destination): void
    {
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $db = config('database.connections.mysql.database');

        $cmd = sprintf(
            'mysqldump -h %s -P %s -u %s %s %s > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            $pass ? '-p'.escapeshellarg($pass) : '',
            escapeshellarg($db),
            escapeshellarg($destination),
        );

        exec($cmd.' 2>&1', $output, $code);

        if ($code !== 0) {
            $this->error('mysqldump falló: '.implode("\n", $output));
        }
    }

    private function pruneOld(string $path, int $keep): void
    {
        $files = glob(rtrim($path, '/').'/backup-*');
        if (! $files) {
            return;
        }

        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        foreach (array_slice($files, $keep) as $old) {
            unlink($old);
        }
    }
}
