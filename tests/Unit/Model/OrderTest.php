<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model;

use App\Model\Order;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OrderTest extends TestCase
{
    #[DataProvider('emails')]
    public function testNormalizedEmail(string $email, string $expected): void
    {
        self::assertSame($expected, self::order(email: $email)->getNormalizedEmail());
    }

    public static function emails(): iterable
    {
        yield 'already normalized' => ['bugs@bunny.com', 'bugs@bunny.com'];
        yield 'case is ignored' => ['BuGS@BuNNy.COM', 'bugs@bunny.com'];
        yield 'dots in the username are ignored' => ['bugs.1@bunny.com', 'bugs1@bunny.com'];
        yield 'the +tag is ignored' => ['bugs+10@bunny.com', 'bugs@bunny.com'];
        yield 'dots in the domain are kept' => ['bugs@mail.bunny.com', 'bugs@mail.bunny.com'];
        yield 'everything at once' => ['BuGS.1+promo@Bunny.com', 'bugs1@bunny.com'];
    }

    /**
     * @param array{string, string, string, string} $a street, city, state, zip
     * @param array{string, string, string, string} $b street, city, state, zip
     */
    #[DataProvider('addresses')]
    public function testNormalizedAddress(array $a, array $b, bool $isTheSame): void
    {
        $addressA = self::order(street: $a[0], city: $a[1], state: $a[2], zip: $a[3])->getNormalizedAddress();
        $addressB = self::order(street: $b[0], city: $b[1], state: $b[2], zip: $b[3])->getNormalizedAddress();

        self::assertSame($isTheSame, $addressA === $addressB);
    }

    public static function addresses(): iterable
    {
        $base = ['1 First St', 'Chicago', 'IL', '60601'];

        yield 'identical' => [$base, $base, true];
        yield 'case is ignored' => [$base, ['1 FIRST st', 'CHICAGO', 'il', '60601'], true];
        yield 'St. = Street' => [['123 Sesame St.', 'Chicago', 'IL', '60601'], ['123 SeSAME street', 'Chicago', 'IL', '60601'], true];
        yield 'Rd = Road' => [['2 Main Rd', 'Chicago', 'IL', '60601'], ['2 main road', 'Chicago', 'IL', '60601'], true];
        yield 'extra spaces and commas' => [$base, [' 1  First St, ', ' chicago ', 'IL', '60601'], true];
        yield 'state name = abbreviation (IL)' => [$base, ['1 First St', 'Chicago', 'Illinois', '60601'], true];
        yield 'state name = abbreviation (CA)' => [['1 First St', 'Chicago', 'CA', '60601'], ['1 First St', 'Chicago', 'California', '60601'], true];
        yield 'state name = abbreviation (NY)' => [['1 First St', 'Chicago', 'NY', '60601'], ['1 First St', 'Chicago', 'New York', '60601'], true];
        yield 'other street number' => [$base, ['2 First St', 'Chicago', 'IL', '60601'], false];
        yield 'other city' => [$base, ['1 First St', 'Peoria', 'IL', '60601'], false];
        yield 'other state' => [$base, ['1 First St', 'Chicago', 'CA', '60601'], false];
        yield 'other zip' => [$base, ['1 First St', 'Chicago', 'IL', '60602'], false];
    }

    #[DataProvider('creditCards')]
    public function testNormalizedCreditCard(string $card, string $expected): void
    {
        self::assertSame($expected, self::order(card: $card)->getNormalizedCreditCard());
    }

    public static function creditCards(): iterable
    {
        yield 'already normalized' => ['12345678', '12345678'];
        yield 'spaces are ignored' => ['1234 5678', '12345678'];
        yield 'dashes are ignored' => ['1234-5678', '12345678'];
    }

    private static function order(
        string $email = 'bugs@bunny.com',
        string $street = '1 First St',
        string $city = 'Chicago',
        string $state = 'IL',
        string $zip = '60601',
        string $card = '12345678',
    ): Order {
        return new Order(1, 1, $email, $street, $city, $state, $zip, $card);
    }
}
