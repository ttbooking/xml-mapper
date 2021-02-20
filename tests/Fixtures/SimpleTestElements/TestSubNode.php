<?php


namespace Tests\Fixtures\SimpleTestElements;


use XmlMapper\Elements\NodeElement;

/**
 * Class TestSubNode
 * @package Tests\Fixtures\SimpleTestElements
 * @property string $attr
 */
class TestSubNode extends NodeElement
{
	protected ?string $_name = 'sub';

}