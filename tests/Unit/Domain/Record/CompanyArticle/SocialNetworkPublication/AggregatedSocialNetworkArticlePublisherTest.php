<?php

namespace Tests\Unit\Domain\Record\CompanyArticle\SocialNetworkPublication;

use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\Record\CompanyArticle\SocialNetworkPublication\AggregatedSocialNetworkArticlePublisher;
use App\Domain\Record\CompanyArticle\SocialNetworkPublication\Mock\SocialNetworkPublisherMock;
use Tests\Unit\TestCase;

/**
 * @group company-article-social-network
 */
final class AggregatedSocialNetworkArticlePublisherTest extends TestCase
{
    public function testHandle(): void
    {
        $publisher = new SocialNetworkPublisherMock();
        $companyArticle = $this->createMock(CompanyArticle::class);

        $publisherHandler = new AggregatedSocialNetworkArticlePublisher($publisher);

        $publisherHandler->publish($companyArticle);

        $this->assertTrue($publisher->isPublished());
    }
}
