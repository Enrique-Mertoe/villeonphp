<?php

namespace Villeon\Core\ORM;

/**
 * Enum OrderMode
 *
 * This enum defines the possible order modes for sorting operations.
 * It can be used to specify whether the sorting should be in ascending
 * or descending order.
 *
 * @package    Villeon\Core\ORM
 * @author     Your Name <abutimartin778@gmail.com>
 * @version    1.0.0
 * @since      2024-02-01
 */
enum OrderMode
{
    /**
     * Ascending order.
     *
     * Used for sorting data from smallest to largest, or alphabetically from A to Z.
     */
    case ASC;

    /**
     * Descending order.
     *
     * Used for sorting data from largest to smallest, or alphabetically from Z to A.
     */
    case DESC;
}
