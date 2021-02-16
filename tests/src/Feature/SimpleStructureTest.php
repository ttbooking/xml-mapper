<?php

namespace Tests\src\Feature;

use PHPUnit\Framework\TestCase;
use Tests\Fixtures\SimpleTestElements\SimpleTest;
use Tests\Fixtures\SimpleTestElements\TestNode1;

class SimpleStructureTest extends TestCase
{


	public function testMapFromXml() {
		$xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<nsr:root xmlns:some="http://www.w3.org/2001/XMLSchema" xmlns:nsr="http://schemas.xmlsoap.org/soap/envelope/">
    <nsr:TestNode1>
        <sub>1230</sub>
    </nsr:TestNode1>
    <nsr:TestNode1>
        <sub>1231</sub>
    </nsr:TestNode1>
    <nsr:TestNode1>
        <sub>1232</sub>
    </nsr:TestNode1>
    <TestNode2>
        <some:sub attr="123">456</some:sub>
    </TestNode2>
</nsr:root>
XML;
		$object = SimpleTest::mapFromXml($xml);
		$object->

		$nodes = $object->getTestNodes1();
		$this->assertCount(3, $nodes);

		/** @var TestNode1 $node */
		foreach ($nodes as $index => $node) {
			$this->assertTrue($node->getSub()->_getNodeText() === '123' . $index);
			$this->assertTrue($node->_getNamespace()->getPrefix() === 'nsr');
		}
		$this->assertTrue($object->getTestNode2()->getSub()->_getNodeText() === '456');
		$this->assertTrue($object->getTestNode2()->getSub()->_getAttribute('attr') === '123');
		$this->assertTrue($object->getTestNode2()->getSub()->_getNamespace()->getPrefix() === 'some');

		$xmlForCompare1 = preg_replace('~\s+~', '', $xml);
		$xmlForCompare2 = preg_replace('~\s+~', '', $object->toXml());
		$this->assertEquals($xmlForCompare1, $xmlForCompare2);
	}

}
