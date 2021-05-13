<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;
use Gt\Dom\Element;
use Iterator;

class ListBinder {
	public function __construct(
		private TemplateCollection $templateCollection
	) {
	}

	/**
	 * @param iterable $listData
	 * @param Document|Element $context
	 * @param string|null $templateName
	 * @return int
	 */
	public function bindListData(
		iterable $listData,
		Document|Element $context,
		?string $templateName = null
	):int {
		if($this->isEmpty($listData)) {
			return 0;
		}

		if($context instanceof Document) {
			$context = $context->documentElement;
		}

		$attributeNameValue = "data-template";
		if($templateName) {
			$attributeNameValue .= "='$templateName'";
		}

		$templateItem = $this->templateCollection->get(
			$context,
			$templateName
		);

		$binder = new ElementBinder();
		$i = null;
		foreach($listData as $i => $listItem) {
			$key = null;
			$value = null;

			if(!is_array($listItem)) {
				$value = $listItem;
			}

			$t = $templateItem->insertTemplate();
			$binder->bind($key, $value, $t);
		}

		if(is_null($i)) {
			return 0;
		}

		return $i + 1;
	}

	private function isEmpty(iterable $listData):bool {
		foreach($listData as $item) {
			if(is_array($listData)) {
				reset($listData);
			}
			elseif($listData instanceof Iterator) {
				$listData->rewind();
			}

			return false;
		}

		return true;
	}
}
