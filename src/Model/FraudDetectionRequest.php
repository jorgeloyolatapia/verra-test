<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class FraudDetectionRequest
{
    public const int MAX_ORDERS = 10000;

    /**
     * @param list<Order> $orders
     */
    public function __construct(
        #[Assert\Count(max: self::MAX_ORDERS)]
        #[Assert\Valid]
        public readonly array $orders,
    ) {
    }
}
