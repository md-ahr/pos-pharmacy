<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'pharmacy:backup-database';

    protected $description = 'Create a PostgreSQL database backup in storage/app/backups';

    public function handle(): int
    {
        if (config('database.default') !== 'pgsql') {
            $this->warn('Database backup command currently supports PostgreSQL only.');

            return self::FAILURE;
        }

        $directory = storage_path('app/backups');

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = 'backup-'.now()->format('Y-m-d-His').'.sql';
        $path = $directory.DIRECTORY_SEPARATOR.$filename;

        $connection = config('database.connections.pgsql');
        $command = [
            'pg_dump',
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--username='.$connection['username'],
            '--dbname='.$connection['database'],
            '--no-password',
            '--format=plain',
            '--file='.$path,
        ];

        $process = new Process($command, null, [
            'PGPASSWORD' => (string) $connection['password'],
        ]);

        $process->run();

        if (! $process->isSuccessful()) {
            $this->error(trim($process->getErrorOutput()) ?: 'Database backup failed.');

            return self::FAILURE;
        }

        $this->info("Database backup created: {$path}");

        return self::SUCCESS;
    }
}
