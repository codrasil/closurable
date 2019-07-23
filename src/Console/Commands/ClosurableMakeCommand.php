<?php

namespace Codrasil\Closurable\Console\Commands;

use Codrasil\Closurable\MigrationCreator;
use Illuminate\Database\Console\Migrations\MigrateMakeCommand as Command;
use Illuminate\Support\Composer;

class ClosurableMakeCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'make:closurable
        {name : The name of the migration}
        {reference : The table to be closure nested that will be referenced from}
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
    protected $description = 'Generate a closure table migration file';

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
     * Write the migration file to disk.
     *
     * @param  string  $name
     * @param  string  $table
     * @param  boolean $create
     * @return void
     */
    protected function writeMigration($name, $table, $create)
    {
        $reference = $this->argument('reference');

        $file = $this->creator->make(
            $name, $this->getMigrationPath(), $reference, $table, $create
        );

        if (! $this->option('fullpath')) {
            $file = pathinfo($file, PATHINFO_FILENAME);
        }

        $this->line("<info>Created Closure Table Migration:</info> {$file}");
    }
}
