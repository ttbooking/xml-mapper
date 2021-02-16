<?php


namespace Tests\Fixtures\SimpleTestElements;


use XmlMapper\Elements\NodeElement;

class TestNode2 extends NodeElement
{
	protected TestSubNode $sub;

	/**
	 * @return TestSubNode
	 */
	public function getSub() {
		return $this->sub;
	}

	/**
	 * @param TestSubNode $sub
	 */
	public function setSub($sub) {
		$this->sub = $sub;
	}

}