<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use Ibexa\Solr\Query\Common\CriterionVisitor\IsUserEnabled;
use Ibexa\Tests\Solr\Search\Query\BaseCriterionVisitorTestCase;

/**
 * @covers \Ibexa\Solr\Query\Common\CriterionVisitor\IsUserEnabled
 */
final class IsUserEnabledTest extends BaseCriterionVisitorTestCase
{
    private CriterionVisitor $criterionVisitor;

    protected function setUp(): void
    {
        $this->criterionVisitor = new IsUserEnabled();
    }

    protected function getVisitor(): CriterionVisitor
    {
        return $this->criterionVisitor;
    }

    protected function getSupportedCriterion(): Criterion
    {
        return new Criterion\IsUserEnabled();
    }

    /**
     * @return iterable<string, array{
     *     0: string,
     *     1: \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\IsUserEnabled
     * }>
     */
    protected function provideDataForTestVisit(): iterable
    {
        yield 'Query for enabled user' => [
            'user_is_enabled_b:true',
            new Criterion\IsUserEnabled(),
        ];

        yield 'Query for disabled user' => [
            'user_is_enabled_b:false',
            new Criterion\IsUserEnabled(false),
        ];
    }
}
