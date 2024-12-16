<?php

namespace Villeon\Core\ORM\Models;

/**
 *
 */
abstract class ModelFactory
{
    public const ORDER_ASC = 'ASC';
    public const ORDER_DESC = 'DESC';

    public static function OR(array $input): array
    {
        return [];
    }

    public static function AND()
    {

    }

    public static function NOT()
    {

    }

    public static function OR_NOT()
    {

    }

    public static function AND_NOT()
    {

    }

}
