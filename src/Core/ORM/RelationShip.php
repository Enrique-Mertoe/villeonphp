<?php

namespace Villeon\Core\ORM;


class RelationShip
{
    public const CASCADE = 0;
    public const SET_NULL = 1;
    public const SET_DEFAULT = 2;
    public const RESTRICT = 3;
    private $tRef;

    public function ref($ref): static
    {
        return $this;
    }

    public function onDel(int $action): static
    {
        return $this;
    }

    public function onUpdate(int $action): static
    {
        return $this;
    }
}
