<?php

namespace Codrasil\Nestable;

use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    use Nestable;

    /**
     * The table adjacently associated with the model.
     *
     * @var string
     */
    protected $adjacentTable;
}
