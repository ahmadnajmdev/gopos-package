<?php

namespace Gopos\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ImportDatabase extends Command
{
    protected $signature = 'db:import';

    protected $description = 'Drop all tables and import database from public/db/inventory.sql';

    public function handle()
    {
        if (! file_exists(public_path('db/inventory.sql'))) {
            $this->error('Database file not found: public/db/inventory.sql');

            return 1;
        }

        // Temporarily disable cache
        Config::set('cache.default', 'array');

        $this->info('Dropping all tables...');

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Get all tables
        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();

        foreach ($tables as $table) {
            $tableName = "Tables_in_$dbName";
            DB::statement("DROP TABLE IF EXISTS `{$table->$tableName}`");
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('All tables dropped successfully.');
        $this->info('Importing database from public/db/inventory.sql...');

        try {
            $sql = file_get_contents(public_path('db/inventory.sql'));
            DB::unprepared($sql);

            $this->info('Database imported successfully!');

            return 0;
        } catch (Exception $e) {
            $this->error('Error importing database: '.$e->getMessage());

            return 1;
        }
    }
}
