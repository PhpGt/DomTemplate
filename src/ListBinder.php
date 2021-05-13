<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;
use Gt\Dom\Element;
use Iterator;

class ListBinder {
	public function bindListData(
		iterable $listData,
		Document|Element $context,
		TemplateCollection $templateCollection,
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

		$templateItem = $templateCollection->get(
			$context,
			$templateName
		);

		foreach($listData as $i => $listItem) {
			$t = $templateItem->insertTemplate();
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
