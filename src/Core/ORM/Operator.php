<?php

namespace Villeon\Core\ORM;

enum Operator: string
{
    case EQUALS = '=';
    case NOT = '!=';
    case GREATER_THAN = '>';
    case LESS_THAN = '<';
    case GREATER_THAN_OR_EQUAL = '>=';
    case LESS_THAN_OR_EQUAL = '<=';
    case LIKE = 'LIKE';
}
