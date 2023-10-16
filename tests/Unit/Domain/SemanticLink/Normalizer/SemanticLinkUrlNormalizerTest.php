<?php

namespace Tests\Unit\Domain\SemanticLink\Normalizer;

use App\Domain\SemanticLink\Normalizer\SemanticLinkUrlNormalizer;
use PHPUnit\Framework\TestCase;

class SemanticLinkUrlNormalizerTest extends TestCase
{
    private $semanticLinkUrlNormalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->semanticLinkUrlNormalizer = new SemanticLinkUrlNormalizer();
    }

    protected function tearDown(): void
    {
        unset($this->semanticLinkUrlNormalizer);

        parent::tearDown();
    }

    public function testNormalizeUrlWithOwnHost(): void
    {
        $ownHost = $this->semanticLinkUrlNormalizer::OWN_HOSTS[0];

        $sourceUrl = sprintf('https://%s/article/view/711', $ownHost);

        $url = $this->semanticLinkUrlNormalizer->normalizeUrl($sourceUrl);

        $this->assertEquals('/article/view/711', $url);
    }

    public function testNormalizeUrlWithoutSchema(): void
    {
        $ownHost = $this->semanticLinkUrlNormalizer::OWN_HOSTS[0];

        $sourceUrl = sprintf('%s/article/view/555', $ownHost);

        $url = $this->semanticLinkUrlNormalizer->normalizeUrl($sourceUrl);

        $this->assertEquals('/article/view/555', $url);
    }

    public function testNormalizeUrlWithoutHost(): void
    {
        $url = $this->semanticLinkUrlNormalizer->normalizeUrl('/article/view/325');

        $this->assertEquals('/article/view/325', $url);
    }

    public function testNormalizeUrlWithoutSlash(): void
    {
        $url = $this->semanticLinkUrlNormalizer->normalizeUrl('article/view/9663');

        $this->assertEquals('/article/view/9663', $url);
    }
}
