<?php

namespace Villeon\Core\ORM\Models;

class Model
{
    private string $ref;


    /**
     * @return ModelFactory<static>
     */

    public function build(): void
    {
        print_r($this->ref);
    }

}