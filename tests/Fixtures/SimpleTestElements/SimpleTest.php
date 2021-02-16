<?php

namespace Tests\Fixtures\SimpleTestElements;


use XmlMapper\Elements\NodeElement;

class SimpleTest extends NodeElement
{
	protected ?string $_name = 'root';

	protected TestNodes1 $testNodes1;
	protected TestNode2 $testNode2;

	/**
	 * @return TestNodes1
	 */
	public function getTestNodes1() {
		return $this->testNodes1;
	}

	/**
	 * @param TestNodes1 $testNodes1
	 */
	public function setTestNodes1($testNodes1) {
		$this->testNodes1 = $testNodes1;
	}

	/**
	 * @return TestNode2
	 */
	public function getTestNode2() {
		return $this->testNode2;
	}

	/**
	 * @param TestNode2 $testNode2
	 */
	public function setTestNode2($testNode2) {
		$this->testNode2 = $testNode2;
	}

}