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

		$binder = new ElementBinder();
		$i = -1;
		foreach($listData as $i => $listItem) {
			$key = null;
			$value = null;

			if(!is_array($listItem)) {
				$value = $listItem;
			}

			$t = $templateItem->insertTemplate();
			$binder->bind($key, $value, $t);
		}

		return $i + 1;
	}

	private function isEmpty(iterable $listData):bool {
		if(is_array($listData)) {
			return is_null(array_key_first($listData));
		}
		else {
			/** @var Iterator $listData */
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
		$parent->innerHTML = trim($parent->innerHTML);
	}
}
