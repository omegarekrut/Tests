<?php

namespace Tests\Functional\Domain\WeeklyLetter\Command\Handler;

use App\Domain\Record\Common\Collection\RecordCollection;
use App\Domain\Record\Common\Entity\VideosAwareInterface;
use App\Domain\WeeklyLetter\Command\Handler\SendWeeklyLetterHandler;
use App\Domain\WeeklyLetter\Command\SendWeeklyLetterCommand;
use App\Domain\WeeklyLetter\Entity\WeeklyLetter;
use App\Domain\WeeklyLetter\Mail\WeeklyLetterMailFactory;
use App\Domain\WeeklyLetter\Mail\WeeklyLetterRecipientsGeneratorFactory;
use App\Domain\WeeklyLetter\Repository\WeeklyLetterRepository;
use App\Module\BulkMailSender\BulkMailSenderInterface;
use App\Module\BulkMailSender\Mock\BulkMailSenderMock;
use App\Module\VideoInformationLoader\VideoInformation;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\ArrayCache;
use Tests\DataFixtures\ORM\WeeklyLetter\LoadWeeklyLetterCurrent;
use Tests\Functional\TestCase;

/**
 * @group weekly-letter
 */
class SendWeeklyLetterHandlerTest extends TestCase
{
    private const CACHE_KEY_PREFIX = 'video.information';

    /** @var WeeklyLetter */
    private $weeklyLetterForSending;
    /** @var WeeklyLetterMailFactory */
    private $weeklyLetterMailFactory;
    /** @var BulkMailSenderInterface */
    private $bulkMailSender;
    /** @var SendWeeklyLetterHandler */
    private $sendWeeklyLetterHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadWeeklyLetterCurrent::class,
        ])->getReferenceRepository();

        $this->weeklyLetterForSending = $referenceRepository->getReference(LoadWeeklyLetterCurrent::REFERENCE_NAME);

        $this->weeklyLetterMailFactory = $this->getContainer()->get(WeeklyLetterMailFactory::class);
        $this->bulkMailSender = new BulkMailSenderMock();

        $this->sendWeeklyLetterHandler = new SendWeeklyLetterHandler(
            $this->weeklyLetterMailFactory,
            $this->getContainer()->get(WeeklyLetterRepository::class),
            $this->bulkMailSender,
            $this->getContainer()->get(WeeklyLetterRecipientsGeneratorFactory::class)
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->sendWeeklyLetterHandler,
            $this->bulkMailSender,
            $this->weeklyLetterMailFactory,
            $this->weeklyLetterForSending
        );

        parent::tearDown();
    }

    public function testWeeklyLetterIsSent(): void
    {
        $this->cacheRecordsVideoInformation($this->weeklyLetterForSending->getRecords());

        $expectedSentMessage = $this->weeklyLetterMailFactory->buildWeeklyLetterMail($this->weeklyLetterForSending);

        $sendWeeklyLetterCommand = new SendWeeklyLetterCommand($this->weeklyLetterForSending);
        $this->sendWeeklyLetterHandler->handle($sendWeeklyLetterCommand);

        $sentMessage = $this->bulkMailSender->getSentMessage();

        $this->assertEquals($expectedSentMessage, $sentMessage);
        $this->assertNotNull($this->weeklyLetterForSending->getSentAt());
        $this->assertNotNull($this->weeklyLetterForSending->getRecipientsCount());
    }

    private function cacheRecordsVideoInformation(RecordCollection $records): void
    {
        foreach ($records as $record) {
            if (!$record instanceof VideosAwareInterface) {
                continue;
            }

            foreach ($record->getVideoUrls() as $videoUrl) {
                $this->cacheVideoInformation($videoUrl);
            }
        }
    }

    private function cacheVideoInformation(string $url): void
    {
        /** @var CacheInterface $cache */
        $cache = new ArrayCache();

        $cacheKey = $this->generateCacheKey($url);

        if ($cache->has($cacheKey) === true) {
            return;
        }

        $videoInformation = new VideoInformation(
            $url,
            'Ловили на жерлицы Щуку, а поймали...',
            'https://i.ytimg.com/vi/yQfl0dgYUrU/maxresdefault.jpg',
            '<iframe width="200" height="113" src="https://www.youtube.com/embed/yQfl0dgYUrU?feature=oembed" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>'
        );

        $cache->set($cacheKey, $videoInformation, 60);
    }

    private function generateCacheKey(string $url): string
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $url = preg_replace('/^(http:|https:)/', '', $url);
        }

        return self::CACHE_KEY_PREFIX.md5($url);
    }
}
