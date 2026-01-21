<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LanguageCode;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use Ibexa\Solr\Query\Common\CriterionVisitor\LanguageCodeIn;
use Ibexa\Tests\Solr\Search\Query\BaseCriterionVisitorTestCase;

class LanguageCodeInTest extends BaseCriterionVisitorTestCase
{
    protected function getVisitor(): CriterionVisitor
    {
        return new LanguageCodeIn();
    }

    protected function getSupportedCriterion(): LanguageCode
    {
        return new LanguageCode('eng-GB');
    }

    public function provideDataForTestVisit(): iterable
    {
        yield 'Single language, match always available' => [
            '(_query_:"{!terms f=content_language_codes_ms}eng-gb" OR content_always_available_b:true)',
            new LanguageCode('eng-GB', true),
        ];

        yield 'Multiple languages, match always available' => [
            '(_query_:"{!terms f=content_language_codes_ms}eng-gb,pol-pl" OR content_always_available_b:true)',
            new LanguageCode(['eng-GB', 'pol-PL'], true),
        ];

        yield 'Multiple languages, do NOT match always available (optimization)' => [
            '_query_:"{!terms f=content_language_codes_ms}eng-gb,pol-pl"',
            new LanguageCode(['eng-GB', 'pol-PL'], false),
        ];

        yield 'Single language, do NOT match always available (optimization)' => [
            '_query_:"{!terms f=content_language_codes_ms}eng-gb"',
            new LanguageCode('eng-GB', false),
        ];
    }
}
