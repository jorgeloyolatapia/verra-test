<?php

declare(strict_types=1);

namespace App\Tests\Unit\Handler;

use App\Handler\FraudDetectionHandler;
use App\Model\Order;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FraudDetectionHandlerTest extends TestCase
{
    /**
     * @param list<Order> $orders
     * @param list<int>   $expectedFraudIds
     */
    #[DataProvider('cases')]
    public function testHandle(array $orders, array $expectedFraudIds): void
    {
        self::assertSame($expectedFraudIds, new FraudDetectionHandler()->handle($orders));
    }

    public static function cases(): iterable
    {
        $email = 'bugs@bunny.com';
        $address = ['street' => '1 First St', 'city' => 'Chicago', 'state' => 'IL', 'zip' => '60601'];

        yield 'no orders' => [[], []];
        yield 'same deal, same email, other card' => [[
            self::order(1, deal: 1, email: $email),
            self::order(2, deal: 1, email: $email),
        ], [1, 2]];
        yield 'same deal, same address, other card' => [[
            self::order(1, ...$address, deal: 1),
            self::order(2, ...$address, deal: 1),
        ], [1, 2]];
        yield 'same deal, same email, same address, same card' => [[
            self::order(1, ...$address, deal: 1, email: $email, card: '111'),
            self::order(2, ...$address, deal: 1, email: $email, card: '111'),
        ], []];
        yield 'other deal, same email, same address, other card' => [[
            self::order(1, ...$address, deal: 1, email: $email),
            self::order(2, ...$address, deal: 2, email: $email),
        ], []];
        yield 'same deal, other card, nothing else in common' => [[
            self::order(1, deal: 1),
            self::order(2, deal: 1),
        ], []];
        yield 'the whole group is flagged, even orders sharing a card' => [[
            self::order(1, deal: 1, email: $email, card: '111'),
            self::order(2, deal: 1, email: $email, card: '111'),
            self::order(3, deal: 1, email: $email, card: '222'),
        ], [1, 2, 3]];
        yield 'an order caught by both rules is listed once' => [[
            self::order(1, ...$address, deal: 1, email: $email),
            self::order(2, ...$address, deal: 1, email: $email),
        ], [1, 2]];
        yield 'ids are returned in the order received, whichever rule caught them' => [[
            self::order(10, ...[...$address, 'street' => '1 First St'], deal: 1, email: 'e@x.com'),
            self::order(20, ...[...$address, 'street' => '2 Second St'], deal: 1, email: 'f@x.com'),
            self::order(30, ...[...$address, 'street' => '3 Third St'], deal: 1, email: 'e@x.com'),
            self::order(40, ...[...$address, 'street' => '2 Second St'], deal: 1, email: 'g@x.com'),
        ], [10, 20, 30, 40]];
    }

    private static function order(
        int $id,
        ?int $deal = null,
        ?string $email = null,
        ?string $street = null,
        ?string $city = null,
        ?string $state = null,
        ?string $zip = null,
        ?string $card = null,
    ): Order {
        static $unique = 0;
        ++$unique;

        return new Order(
            $id,
            $deal ?? 1000 + $unique,
            $email ?? "user$unique@x.com",
            $street ?? "$unique Unique St",
            $city ?? "City$unique",
            $state ?? "State$unique",
            $zip ?? (string) (10000 + $unique),
            $card ?? (string) (4000000 + $unique),
        );
    }
}
