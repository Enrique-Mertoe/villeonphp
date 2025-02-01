<?php

namespace Villeon\Core\ORM;

/**
 * Enum Operator
 *
 * This enum defines the operators commonly used in database query conditions
 * and comparisons, such as equality, inequality, greater-than, less-than, and
 * pattern matching (LIKE). It can be used to standardize query construction
 * and improve readability and maintainability in the application.
 *
 * @package    Villeon\Core\ORM
 * @author     Your Name <abutimartin778@gmail.com>
 * @version    1.0.0
 * @since      2024-02-01
 */
enum Operator: string
{
    /**
     * Equals operator ('=').
     *
     * Used to compare if two values are equal.
     */
    case EQUALS = '=';

    /**
     * Not equals operator ('!=').
     *
     * Used to compare if two values are not equal.
     */
    case NOT = '!=';

    /**
     * Greater than operator ('>').
     *
     * Used to compare if a value is greater than another value.
     */
    case GREATER_THAN = '>';

    /**
     * Less than operator ('<').
     *
     * Used to compare if a value is less than another value.
     */
    case LESS_THAN = '<';

    /**
     * Greater than or equal to operator ('>=').
     *
     * Used to compare if a value is greater than or equal to another value.
     */
    case GREATER_THAN_OR_EQUAL = '>=';

    /**
     * Less than or equal to operator ('<=').
     *
     * Used to compare if a value is less than or equal to another value.
     */
    case LESS_THAN_OR_EQUAL = '<=';

    /**
     * LIKE operator ('LIKE').
     *
     * Used for pattern matching in database queries, typically with wildcards.
     *  - '%term%' → matches any value containing "term"
     *  - 'term%' → matches any value starting with "term"
     *  - '%term' → matches any value ending with "term"
     */
    case LIKE = 'LIKE';

    /**
     * ILIKE operator ('ILIKE').
     *
     * Used for case-insensitive pattern matching in some databases like PostgreSQL.
     * Supports the same wildcards as `LIKE`.
     *  - '%term%' → matches any value containing "term"
     *  - 'term%' → matches any value starting with "term"
     *  - '%term' → matches any value ending with "term"
     */
    case ILIKE = 'ILIKE';

    /**
     * NOT LIKE operator ('NOT LIKE').
     *
     * Used for negating the pattern matching in database queries.
     * Matches values that do not meet the specified pattern.
     *  - '%term%' → matches any value containing "term"
     *  - 'term%' → matches any value starting with "term"
     *  - '%term' → matches any value ending with "term"
     */
    case NOT_LIKE = 'NOT LIKE';

    /**
     * NOT ILIKE operator ('NOT ILIKE').
     *
     * Used for case-insensitive negation of pattern matching in some databases.
     * Matches values that do not meet the specified pattern (case-insensitive).
     *  - '%term%' → matches any value containing "term"
     *  - 'term%' → matches any value starting with "term"
     *  - '%term' → matches any value ending with "term"
     */
    case NOT_ILIKE = 'NOT ILIKE';

    /**
     * Contains condition (similar to SQL's 'LIKE').
     *
     * Matches values that contain the specified string. Supports wildcards:
     * - '%term%' → matches any value containing "term"
     * - 'term%' → matches any value starting with "term"
     * - '%term' → matches any value ending with "term"
     */
    case CONTAINS = "contains";
}
