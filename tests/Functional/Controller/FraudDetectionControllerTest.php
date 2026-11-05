<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Model\FraudDetectionRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FraudDetectionControllerTest extends WebTestCase
{
    private const string URL = '/api/fraud-detection';
    private const array JSON = ['CONTENT_TYPE' => 'application/json'];

    public function testSampleFromTheStatement(): void
    {
        $client = static::createClient();
        $client->request('POST', self::URL, [], [], self::JSON, json_encode([
            'orders' => [
                $this->order(1, 1, 'bugs@bunny.com', '12345689010'),
                $this->order(2, 1, 'elmer@fudd.com', '10987654321'),
                $this->order(3, 2, 'bugs@bunny.com', '12345689010'),
            ],
        ], \JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(200);
        self::assertJsonStringEqualsJsonString('{"fraudulentOrders":[1,2]}', $client->getResponse()->getContent());
    }

    public function testMalformedJsonIsABadRequest(): void
    {
        $client = static::createClient();
        $client->request('POST', self::URL, [], [], self::JSON, '{not json');

        self::assertResponseStatusCodeSame(400);
    }

    public function testMissingOrdersIsUnprocessable(): void
    {
        $client = static::createClient();
        $client->request('POST', self::URL, [], [], self::JSON, '{"foo":[]}');

        self::assertResponseStatusCodeSame(422);
    }

    public function testInvalidOrderIsUnprocessable(): void
    {
        $client = static::createClient();
        $client->request('POST', self::URL, [], [], self::JSON, '{"orders":[{"orderId":1}]}');

        self::assertResponseStatusCodeSame(422);
    }

    public function testExactlyTheMaximumNumberOfOrdersIsAccepted(): void
    {
        $client = static::createClient();
        $client->request('POST', self::URL, [], [], self::JSON, $this->bodyWithOrders(FraudDetectionRequest::MAX_ORDERS));

        self::assertResponseStatusCodeSame(200);
        self::assertJsonStringEqualsJsonString('{"fraudulentOrders":[]}', $client->getResponse()->getContent());
    }

    public function testMoreThanTheMaximumNumberOfOrdersIsUnprocessable(): void
    {
        $client = static::createClient();
        $client->request('POST', self::URL, [], [], self::JSON, $this->bodyWithOrders(FraudDetectionRequest::MAX_ORDERS + 1));

        self::assertResponseStatusCodeSame(422);
    }

    #[DataProvider('invalidFields')]
    public function testOrderBreakingAnAssertIsUnprocessable(string $field, int|string $value): void
    {
        $client = static::createClient();
        $client->request('POST', self::URL, [], [], self::JSON, json_encode([
            'orders' => [[...$this->order(1, 1, 'bugs@bunny.com', '12345689010'), $field => $value]],
        ], \JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(422);
    }

    public static function invalidFields(): iterable
    {
        yield 'email without domain' => ['email', 'not-an-email'];
        yield 'email blank' => ['email', ''];
        yield 'street blank' => ['streetAddress', ''];
        yield 'city blank' => ['city', ''];
        yield 'state blank' => ['state', ''];
        yield 'zip blank' => ['zipCode', ''];
        yield 'card blank' => ['creditCard', ''];
    }

    private function bodyWithOrders(int $count): string
    {
        $orders = [];
        for ($i = 1; $i <= $count; ++$i) {
            $orders[] = $this->order($i, $i, "user$i@example.com", '4111 1111 '.str_pad((string) $i, 8, '0', \STR_PAD_LEFT));
        }

        return json_encode(['orders' => $orders], \JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, int|string>
     */
    private function order(int $id, int $deal, string $email, string $card): array
    {
        return [
            'orderId' => $id,
            'dealId' => $deal,
            'email' => $email,
            'streetAddress' => '123 Sesame St.',
            'city' => 'New York',
            'state' => 'NY',
            'zipCode' => '10011',
            'creditCard' => $card,
        ];
    }
}
