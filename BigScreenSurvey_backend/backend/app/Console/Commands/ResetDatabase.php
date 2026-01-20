<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetDatabase extends Command
{
    protected $signature = 'db:reset';
    protected $description = 'Reset and clean database';

    public function handle()
    {
        if (!app()->environment('local')) {
            $this->error('This command can only be run in local environment');
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();

        foreach ($tables as $table) {
            $tableName = $table->{'Tables_in_' . $dbName};
            DB::statement('DROP TABLE IF EXISTS ' . $tableName);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('All tables dropped successfully');

        $this->call('migrate');
    }
}
