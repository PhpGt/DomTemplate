<?php
namespace Gt\DomTemplate\Test\TestHelper\Model;

class Customer {
	/** @param array<Order> $orderList */
	public function __construct(
		public readonly int $id,
		public readonly string $name,
		public readonly Address $address,
		public array $orderList = [],
	) {}

	public function addOrder(Order $order):void {
		array_push($this->orderList, $order);
	}
}
