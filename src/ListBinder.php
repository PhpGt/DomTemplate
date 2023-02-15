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
		private TemplateCollection $templateCollection,
		?BindableCache $bindableCache = null
	) {
		$this->bindableCache = $bindableCache ?? new BindableCache();
	}

	/** @param iterable<int|string,mixed> $listData */
	public function bindListData(
		iterable $listData,
		Document|Element $context,
		?string $templateName = null,
		?callable $callback = null,
	):int {
		if($context instanceof Document) {
			$context = $context->documentElement;
		}

		if($this->isEmpty($listData)) {
			$this->clearTemplateParentHTML($context, $templateName);
			return 0;
		}

		$templateItem = $this->templateCollection->get(
			$context,
			$templateName
		);

		$elementBinder = new ElementBinder();
		$nestedCount = 0;
		$i = -1;
		foreach($listData as $listKey => $listItem) {
			$i++;
			$t = $templateItem->insertTemplate();

// If the $listItem's first value is iterable, then treat this as a nested list.
			if($this->isNested($listItem)) {
				$elementBinder->bind(null, $listKey, $t);
				$nestedCount += $this->bindListData(
					$listItem,
					$t,
					$templateName
				);
				continue;
			}

			if(is_object($listItem) && method_exists($listItem, "asArray")) {
				$listItem = $listItem->asArray();
			}
			elseif(is_object($listItem) && !is_iterable($listItem)) {
				if($this->bindableCache->isBindable($listItem)) {
					$listItem = $this->bindableCache->convertToKvp($listItem);
				}
			}

			if($callback) {
				$listItem = call_user_func(
					$callback,
					$t,
					$listItem,
					$listKey,
				);
			}

			if(is_null($listItem)) {
				continue;
			}

			if($this->isKVP($listItem)) {
				$elementBinder->bind(null, $listKey, $t);

				foreach($listItem as $key => $value) {
					$elementBinder->bind($key, $value, $t);

					if($this->isNested($value)) {
						$elementBinder->bind(null, $key, $t);
						$nestedCount += $this->bindListData(
							$value,
							$t,
							$templateName
						);
					}
				}
			}
			else {
				$elementBinder->bind(null, $listItem, $t);
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

	private function clearTemplateParentHTML(
		Element $context,
		?string $templateName
	):void {
		$template = $this->templateCollection->get($context, $templateName);
		$parent = $template->getTemplateParent();
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
		elseif($item instanceof Iterator) {
			return true;
		}

		return false;
	}
}
