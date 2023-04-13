<?php
namespace App\Factory;

use App\Entity\Order;

class OrderFactory extends AbstractFactory
{

    public function create(): Order
    {
        $order = new Order();
        $order
            ->setStatus(Order::STATUS_CART)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        return $order;
    }

}