<?php

namespace Codrasil\Closurable;

trait WithClosures
{
    /**
     * Retrieve the related closure table.
     *
     * @param  string $key
     * @return mixed
     */
    public function root($key)
    {
        return $this->{$key}()->roots();
    }
}
