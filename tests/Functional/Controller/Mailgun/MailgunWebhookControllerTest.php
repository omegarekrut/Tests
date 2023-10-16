<?php

namespace Tests\Functional\Controller\Mailgun;

use App\Controller\Mailgun\WebhookController;
use App\Domain\User\Entity\User;
use App\Module\Mailgun\WebhookEvent\Signature\SignatureGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group mailgun
 */
class MailgunWebhookControllerTest extends TestCase
{
    /** @var WebhookController */
    private $webhookController;
    /** @var SignatureGenerator */
    private $signatureGenerator;
    /** @var User */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        $this->webhookController = $this->getContainer()->get(WebhookController::class);
        $this->signatureGenerator = $this->getContainer()->get(SignatureGenerator::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->webhookController,
            $this->signatureGenerator
        );

        parent::tearDown();
    }

    public function testAfterHandlingDeliveryPermanentFailedEventUserEmailShouldMarkedAsBounced(): void
    {
        $this->assertFalse($this->user->getEmail()->isBounced());

        $deliveryPermanentFailedEventRequestData = $this->signWebhookRequestData([
            'event-data' => [
                'event' => 'failed',
                'severity' => 'permanent',
                'recipient' => $this->user->getEmailAddress(),
            ],
        ]);

        $request = $this->createRequestWithContent($deliveryPermanentFailedEventRequestData);
        $response = $this->webhookController->bounceUserEmail($request);
        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertEquals(['status' => Response::HTTP_OK], $jsonResponse);
        $this->assertTrue($this->user->getEmail()->isBounced());
    }

    public function testAfterHandlingComplainedAboutSpamEventUserMustHaveDoNotDisturbStatus(): void
    {
        $this->assertTrue($this->user->canBeDisturbedByEmail());

        $complainedAboutSpamEventRequestData = $this->signWebhookRequestData([
            'event-data' => [
                'event' => 'complained',
                'recipient' => $this->user->getEmailAddress(),
            ],
        ]);

        $request = $this->createRequestWithContent($complainedAboutSpamEventRequestData);
        $response = $this->webhookController->doNotDisturbUser($request);
        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertEquals(['status' => Response::HTTP_OK], $jsonResponse);
        $this->assertFalse($this->user->canBeDisturbedByEmail());
    }

    public function testAfterHandlingUnsubscribedEventUserMustHaveDoNotDisturbStatus(): void
    {
        $this->assertTrue($this->user->canBeDisturbedByEmail());

        $unsubscribedEventRequestData = $this->signWebhookRequestData([
            'event-data' => [
                'event' => 'unsubscribed',
                'recipient' => $this->user->getEmailAddress(),
            ],
        ]);

        $request = $this->createRequestWithContent($unsubscribedEventRequestData);
        $response = $this->webhookController->doNotDisturbUser($request);
        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertEquals(['status' => Response::HTTP_OK], $jsonResponse);
        $this->assertFalse($this->user->canBeDisturbedByEmail());
    }

    public function testUserEmailCantBeBouncedByUnexpectedRequest(): void
    {
        $this->assertFalse($this->user->getEmail()->isBounced());

        $unexpectedRequestData = $this->signWebhookRequestData([
            'event-data' => [
                'event' => 'complained',
                'recipient' => $this->user->getEmailAddress(),
            ],
        ]);

        $request = $this->createRequestWithContent($unexpectedRequestData);
        $this->webhookController->bounceUserEmail($request);

        $this->assertFalse($this->user->getEmail()->isBounced());
    }

    public function testUserCantBeUnsibscribedByUnexpectedRequest(): void
    {
        $this->assertTrue($this->user->canBeDisturbedByEmail());

        $unexpectedRequestData = $this->signWebhookRequestData([
            'event-data' => [
                'event' => 'failed',
                'severity' => 'permanent',
                'recipient' => $this->user->getEmailAddress(),
            ],
        ]);

        $request = $this->createRequestWithContent($unexpectedRequestData);
        $this->webhookController->doNotDisturbUser($request);

        $this->assertTrue($this->user->canBeDisturbedByEmail());
    }

    private function signWebhookRequestData(array $requestData): array
    {
        $eventTimestamp = time();
        $eventToken = 'some-token';

        $requestData['signature'] = [
            'timestamp' => $eventTimestamp,
            'token' => $eventToken,
            'signature' => $this->signatureGenerator->generate($eventToken, $eventTimestamp),
        ];

        return $requestData;
    }

    private function createRequestWithContent(array $content): Request
    {
        return new Request([], [], [], [], [], [], json_encode($content));
    }
}
