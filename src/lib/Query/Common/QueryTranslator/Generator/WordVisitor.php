<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Common\QueryTranslator\Generator;

use QueryTranslator\Languages\Galach\Generators\Common\Visitor;
use QueryTranslator\Languages\Galach\Generators\Lucene\Common\WordBase;
use QueryTranslator\Values\Node;

/**
 * Word Node Visitor implementation.
 */
class WordVisitor extends WordBase
{
    /**
     * @param array<string, mixed>|null $options
     */
    #[\Override]
    public function visit(Node $node, ?Visitor $subVisitor = null, $options = null): string
    {
        $word = parent::visit($node, $subVisitor, $options);

        if (isset($options['fuzziness'])) {
            $fuzziness = sprintf('~%.1f', $options['fuzziness']);
            $word .= $fuzziness;
        }

        return $word;
    }

    /**
     * @see http://lucene.apache.org/core/5_0_0/queryparser/org/apache/lucene/queryparser/classic/package-summary.html#Escaping_Special_Characters
     *
     * Note: additionally to what is defined above we also escape blank space,
     * and we don't escape an asterisk.
     *
     * @param string $string
     */
    protected function escapeWord($string): ?string
    {
        return preg_replace(
            '/(\\+|-|&&|\\|\\||!|\\(|\\)|\\{|}|\\[|]|\\^|"|~|\\?|:|\\/|\\\\| )/',
            '\\\\$1',
            $string
        );
    }
}
