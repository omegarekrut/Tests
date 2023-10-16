<?php

namespace Functional\Domain\Record\CompanyArticle\Command\SocialNetwork\Handler;

use App\Domain\Record\CompanyArticle\Command\SocialNetwork\PublishCompanyArticleInSocialNetworkCommand;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\Record\CompanyArticle\SocialNetworkPublication\AggregatedSocialNetworkArticlePublisher;
use App\Domain\Record\CompanyArticle\SocialNetworkPublication\Mock\SocialNetworkPublisherMock;
use League\Tactician\CommandBus;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadCompanyArticleWithAuthor;
use Tests\Functional\TestCase;

/**
 * @group company-article-social-network
 */
final class PublishCompanyArticleInSocialNetworkHandlerTest extends TestCase
{
    private CommandBus $commandBus;

    private AggregatedSocialNetworkArticlePublisher $publisher;

    public function setUp(): void
    {
        parent::setUp();

        $this->clearDatabase();

        $this->commandBus = $this->getCommandBus();
        $publisher = $this->getContainer()->get(AggregatedSocialNetworkArticlePublisher::class);

        assert($publisher instanceof AggregatedSocialNetworkArticlePublisher);

        $this->publisher = $publisher;
    }

    public function testHandle(): void
    {
        $companyArticle = $this->loadCompanyArticle();

        $command = new PublishCompanyArticleInSocialNetworkCommand($companyArticle->getId());

        $this->commandBus->handle($command);

        $publishers = $this->publisher->getPublishers();
        assert(count($publishers) > 0);

        foreach ($publishers as $publisher) {
            assert($publisher instanceof SocialNetworkPublisherMock);

            $this->assertTrue($publisher->isPublished());
        }
    }

    private function loadCompanyArticle(): CompanyArticle
    {
        return $this->loadFixture(LoadCompanyArticleWithAuthor::class, CompanyArticle::class);
    }
}
