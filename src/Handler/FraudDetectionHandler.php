<?php

declare(strict_types=1);

namespace App\Handler;

use App\Model\Order;

final class FraudDetectionHandler
{
    /**
     * @param list<Order> $orders
     *
     * @return list<int> ids of the fraudulent orders, in the order they were received
     */
    public function handle(array $orders): array
    {
        $ordersByIdentity = [];
        foreach ($orders as $position => $order) {
            foreach ($order->getIdentities() as $identity) {
                $ordersByIdentity[$identity][$position] = $order;
            }
        }

        $fraudulentOrders = [];
        foreach ($ordersByIdentity as $group) {
            if ($this->hasDifferentCards($group)) {
                $fraudulentOrders += $group;
            }
        }

        ksort($fraudulentOrders);

        return array_values(array_map(static fn (Order $order): int => $order->orderId, $fraudulentOrders));
    }

    /**
     * @param array<int, Order> $group
     */
    private function hasDifferentCards(array $group): bool
    {
        $cards = array_map(static fn (Order $order): string => $order->getNormalizedCreditCard(), $group);

        return \count(array_unique($cards)) > 1;
    }
}
