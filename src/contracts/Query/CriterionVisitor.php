<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\Solr\Query;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;

/**
 * Visits the criterion tree into a Solr query.
 */
abstract class CriterionVisitor
{
    /**
     * CHeck if visitor is applicable to current criterion.
     */
    abstract public function canVisit(CriterionInterface $criterion);

    /**
     * Map field value to a proper Solr representation.
     */
    abstract public function visit(CriterionInterface $criterion, ?self $subVisitor = null);

    /**
     * Get Solr range.
     *
     * Start and end are optional, depending on the respective operator. Pass
     * null in this case. The operator may be one of:
     *
     * - case Operator::GT:
     * - case Operator::GTE:
     * - case Operator::LT:
     * - case Operator::LTE:
     * - case Operator::BETWEEN:
     */
    protected function getRange(string $operator, mixed $start, mixed $end): string
    {
        $startBrace = '[';
        $startValue = '*';
        $endValue = '*';
        $endBrace = ']';

        $start = '"' . $this->escapeQuote($this->toString($start), true) . '"';
        $end = '"' . $this->escapeQuote($this->toString($end), true) . '"';

        switch ($operator) {
            case Operator::GT:
                $startBrace = '{';
                $endBrace = '}';
                // Intentionally omitted break

            case Operator::GTE:
                $startValue = $start;
                break;

            case Operator::LT:
                $startBrace = '{';
                $endBrace = '}';
                // Intentionally omitted break

            case Operator::LTE:
                $endValue = $end;
                break;

            case Operator::BETWEEN:
                $startValue = $start;
                $endValue = $end;
                break;

            default:
                throw new \RuntimeException("Unknown operator: $operator");
        }

        return "$startBrace$startValue TO $endValue$endBrace";
    }

    /**
     * Converts given $value to the appropriate Solr string representation.
     */
    protected function toString(mixed $value): string
    {
        return match (\gettype($value)) {
            'boolean' => $value ? 'true' : 'false',
            'double' => sprintf('%F', $value),
            default => (string)$value,
        };
    }

    /**
     * Escapes given $string for wrapping inside single or double quotes.
     *
     * Does not include quotes in the returned string, this needs to be done by the consumer code.
     */
    protected function escapeQuote(string $string, bool $doubleQuote = false): string
    {
        $pattern = ($doubleQuote ? '/("|\\\)/' : '/(\'|\\\)/');

        return preg_replace($pattern, '\\\$1', $string);
    }

    /**
     * Escapes value for use in expressions.
     *
     * @param bool $allowWildcard Allow "*" in expression.
     */
    protected function escapeExpressions(string $string, bool $allowWildcard = false): ?string
    {
        if ($allowWildcard) {
            $reservedCharacters = preg_quote('+-&|!(){}[]^"~?:\\ ');
        } else {
            $reservedCharacters = preg_quote('+-&|!(){}[]^"~*?:\\ ');
        }

        return preg_replace_callback(
            '/([' . $reservedCharacters . '])/',
            static fn (array $matches): string => '\\' . $matches[0],
            $string
        );
    }
}
