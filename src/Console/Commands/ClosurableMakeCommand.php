<?php

namespace Codrasil\Closurable\Console\Commands;

use Codrasil\Closurable\MigrationCreator;
use Illuminate\Database\Console\Migrations\MigrateMakeCommand as Command;
use Illuminate\Database\Console\Migrations\TableGuesser;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;

class ClosurableMakeCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'make:closurable
        {reference : The name of the table that will be referenced to be closure nested}
        {--create= : The table to be created}
        {--table= : The table to migrate}
        {--path= : The location where the migration file should be created}
        {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
        {--fullpath : Output the full path of the migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new closure migration file';

    /**
     * Create a new migration install command instance.
     *
     * @param  \Codrasil\Closurable\MigrationCreator $creator
     * @param  \Illuminate\Support\Composer          $composer
     * @return void
     */
    public function __construct(MigrationCreator $creator, Composer $composer)
    {
        parent::__construct($creator, $composer);

        $this->creator = $creator;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // It's possible for the developer to specify the tables to modify in this
        // schema operation. The developer may also specify if this table needs
        // to be freshly created so we can create the appropriate migrations.
        $name = sprintf(
            'create_%s%s_table',
            Str::snake(trim($this->input->getArgument('reference'))),
            config('closurable.suffix', 'tree')
        );

        $table = $this->input->getOption('table');

        $create = $this->input->getOption('create') ?: false;

        if ($table) {
            $name = Str::snake(trim($table));
        }

        if ($create) {
            $name = sprintf(
                'create_%s_table',
                Str::snake(trim($create))
            );
        }

        // If no table was given as an option but a create option is given then we
        // will use the "create" option as the table name. This allows the devs
        // to pass a table name into this option as a short-cut for creating.
        if (! $table && is_string($create)) {
            $table = $create;

            $create = true;
        }

        // Next, we will attempt to guess the table name if this the migration has
        // "create" in the name. This will allow us to provide a convenient way
        // of creating migrations that create new tables for the application.
        if (! $table) {
            [$table, $create] = TableGuesser::guess($name);
        }

        // Now we are ready to write the migration out to disk. Once we've written
        // the migration out, we will dump-autoload for the entire framework to
        // make sure that the migrations are registered by the class loaders.
        $this->writeMigration($name, $table, $create);

        $this->composer->dumpAutoloads();
    }

    /**
     * Write the migration file to disk.
     *
     * @param  string  $name
     * @param  string  $table
     * @param  boolean $create
     * @return void
     */
    protected function writeMigration($name, $table, $create)
    {
        $reference = Str::snake(trim($this->input->getArgument('reference')));

        $file = $this->creator->make(
            $name, $this->getMigrationPath(), $reference, $table, $create
        );

        if (! $this->option('fullpath')) {
            $file = pathinfo($file, PATHINFO_FILENAME);
        }

        $this->line("<info>Created Closure Table Migration:</info> {$file}");
    }
}
