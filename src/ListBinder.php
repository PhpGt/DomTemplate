<?php
namespace Gt\DomTemplate;

use DateTimeInterface;
use Gt\Dom\Document;
use Gt\Dom\Element;
use Iterator;
use Stringable;

class ListBinder {
	private BindableCache $bindableCache;

	/** @noinspection PhpPropertyCanBeReadonlyInspection */
	public function __construct(
		private ListElementCollection $listElementCollection,
		?BindableCache $bindableCache = null
	) {
		$this->bindableCache = $bindableCache ?? new BindableCache();
	}

	/** @param iterable<int|string,mixed> $listData */
	public function bindListData(
		iterable $listData,
		Document|Element $context,
		?string $listItemName = null,
		?callable $callback = null,
	):int {
		if($context instanceof Document) {
			$context = $context->documentElement;
		}

		if($listData instanceof \IteratorAggregate) {
			$listData = $listData->getIterator();
		}

		if($this->isEmpty($listData)) {
			$this->clearListItemParentHTML($context, $listItemName);
			return 0;
		}

		$listItem = $this->listElementCollection->get(
			$context,
			$listItemName
		);

		$elementBinder = new ElementBinder();
		$nestedCount = 0;
		$i = -1;
		foreach($listData as $listKey => $listValue) {
			$i++;
			$t = $listItem->insertListItem();

// If the $listValue's first value is iterable, then treat this as a nested list.
			if($this->isNested($listValue)) {
				$elementBinder->bind(null, $listKey, $t);
				$nestedCount += $this->bindListData(
					$listValue,
					$t,
					$listItemName
				);
				continue;
			}

			if(is_object($listValue) && method_exists($listValue, "asArray")) {
				$listValue = $listValue->asArray();
			}
			elseif(is_object($listValue) && !is_iterable($listValue)) {
				if($this->bindableCache->isBindable($listValue)) {
					$listValue = $this->bindableCache->convertToKvp($listValue);
				}
			}

			if($callback) {
				$listValue = call_user_func(
					$callback,
					$t,
					$listValue,
					$listKey,
				);
			}

			if(is_null($listValue)) {
				continue;
			}

			if($this->isKVP($listValue)) {
				$elementBinder->bind(null, $listKey, $t);

				foreach($listValue as $key => $value) {
					$elementBinder->bind($key, $value, $t);

					if($this->isNested($value)) {
						$elementBinder->bind(null, $key, $t);
						$nestedCount += $this->bindListData(
							$value,
							$t,
							$listItemName
						);
					}
				}
			}
			else {
				$elementBinder->bind(null, $listValue, $t);
			}
		}

		return $nestedCount + $i + 1;
	}

	/** @param iterable<int|string,mixed> $listData */
	private function isEmpty(iterable $listData):bool {
		if(is_array($listData)) {
			return is_null(array_key_first($listData));
		}
		else {
			/** @var Iterator<mixed> $listData */
			$listData->rewind();
			return !$listData->valid();
		}
	}

	private function clearListItemParentHTML(
		Element $context,
		?string $listName
	):void {
		$listElement = $this->listElementCollection->get($context, $listName);
		$parent = $listElement->getListItemParent();
		$parent->innerHTML = trim($parent->innerHTML ?? "");
	}

	private function isKVP(mixed $item):bool {
		if(is_scalar($item)) {
			return false;
		}

		if($item instanceof DateTimeInterface) {
			return false;
		}
		if($item instanceof Stringable) {
			return false;
		}

		return true;
	}

	private function isNested(mixed $item):bool {
		if(is_array($item)) {
			$key = array_key_first($item);
			return is_int($key) || is_iterable($item[$key]);
		}
		elseif($item instanceof Iterator || $item instanceof \Traversable) {
			return true;
		}

		return false;
	}
}
