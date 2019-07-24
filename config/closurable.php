<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Closure Table Suffix
    |--------------------------------------------------------------------------
    |
    | This value is the suffix name of the table. It is used when generating
    | a closure migration file. By default it will build the database table name
    | in the format:
    |     <referenced table><suffix>
    |
    | For example:
    |     commentstree
    |
    | Where "comments" is the database table to be closure nested, and "tree"
    | is the suffix value as declared below.
    |
    */

    'suffix' => env('DB_SUFFIX', 'tree'),
];
