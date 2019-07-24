<?php

namespace Codrasil\Closurable;

use Illuminate\Database\Migrations\MigrationCreator as BaseMigrationCreator;

class MigrationCreator extends BaseMigrationCreator
{
    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    public function stubPath()
    {
        return __DIR__.'/Console/stubs';
    }

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param  string      $name
     * @param  string      $stub
     * @param  string|null $table
     * @param  string      $reference
     * @return string
     */
    protected function writeStub($name, $stub, $table, $reference)
    {
        $stub = parent::populateStub($name, $stub, $table);

        $stub = str_replace('DummyReferenceTable', $reference, $stub);

        return $stub;
    }

    /**
     * Create a new migration at the given path.
     *
     * @param  string      $name
     * @param  string      $path
     * @param  string      $reference
     * @param  string|null $table
     * @param  boolean     $create
     * @return string
     *
     * @throws \Exception Throws a generic exception.
     */
    public function make($name, $path, $reference, $table = null, $create = false)
    {
        $this->ensureMigrationDoesntAlreadyExist($name);

        // First we will get the stub file for the migration, which serves as a type
        // of template for the migration. Once we have those we will populate the
        // various place-holders, save the file, and run the post create event.
        $stub = $this->getStub($table, $create);

        $this->files->put(
            $path = $this->getPath($name, $path),
            $this->writeStub($name, $stub, $table, $reference)
        );

        // Next, we will fire any hooks that are supposed to fire after a migration is
        // created. Once that is done we'll be ready to return the full path to the
        // migration file so it can be used however it's needed by the developer.
        $this->firePostCreateHooks($table);

        return $path;
    }
}
