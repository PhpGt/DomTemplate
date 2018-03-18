<?php
namespace Gt\DomTemplate\Test;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase {
	protected function rrmdir($dir):void {
		if(is_dir($dir)) {
			foreach(scandir($dir) as $file) {
				if($file != "." && $file != "..") {
					if(is_dir($dir . "/" . $file)) {
						$this->rrmdir($dir . "/" . $file);
					}
					else {
						unlink($dir . "/" . $file);
					}
				}
			}

			rmdir($dir);
		}
	}
}