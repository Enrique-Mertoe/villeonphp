<?php
function is_primitive($param): bool
{
    return in_array(gettype($param), [
        "string", "integer", "boolean"
    ]);
}