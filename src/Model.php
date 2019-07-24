<?php

namespace Codrasil\Closurable;

use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    use Closurable;

    /**
     * The closure table associated with the model.
     *
     * @var string
     */
    protected $closureTable;
}
