<?php


namespace XmlMapper\Elements;


use ReflectionProperty;
use XmlMapper\Exceptions\XmlMapperException;
use XmlMapper\Support\Helper;
use XMLReader;
use XMLWriter;

/**
 * Class NodeElement
 * @package App\Services\XmlMapper\Elements
 */
abstract class NodeElement
{
    /**
	 * attributes of node element
	 * @var string[]
	 */
    protected array $_attributes = [];

	/**
	 * node name
	 * if is null take a class name as is
	 * @var string|null
	 */
    protected ?string $_name = null;

	/**
	 * node text content
	 * @var string
	 */
    protected string $_nodeText = '';

	/**
	 * namespace of node
	 * @var NodeNamespace|null
	 */
    protected ?NodeNamespace $_namespace = null;

	/**
	 * convert object to xml string
	 * @param XMLWriter|null $xmlWriter
	 * @return string|void if xmlWriter specified than void
	 */
    public function toXml(XMLWriter $xmlWriter = null) {
		/**
		 * if is not base element, output not need
		 */
        $needsReturnOutput = false;
		/**
		 * initialize xmlWriter and start xml document
		 */
        if (!$xmlWriter) {
            $xmlWriter = new XMLWriter();
            $xmlWriter->openMemory();
            $xmlWriter->startDocument('1.0', 'utf-8');
            $needsReturnOutput = true;
        }
		/**
		 * open current element
		 */
        if ($this->_namespace) {
            $xmlWriter->startElementNs($this->_namespace->getPrefix(), $this->_getName(), $this->_namespace->getUri());
        } else {
            $xmlWriter->startElement($this->_getName());
        }
		/**
		 * write attributes into current element
		 */
        foreach ($this->_attributes as $attributeName => $value) {
            $xmlWriter->startAttribute($attributeName);
            $xmlWriter->text($value);
            $xmlWriter->endAttribute();
        }

		/**
		 * write text content into current element
		 */
        if (!empty($this->_nodeText)) {
            $xmlWriter->text($this->_nodeText);
        }


		/**
		 * write child nodes
		 */
        foreach (get_class_vars(static::class) as $attributeName => $defaultValue) {

            $attributeValue = $this->{$attributeName};

            if ($attributeValue !== null) {

                if ($attributeValue instanceof self) {

                    $attributeValue->toXml($xmlWriter);

                } else if ($attributeValue instanceof NodeArray) {

                    foreach ($attributeValue as $child) {
                        if ($child !== null && $child instanceof self) {
                            $child->toXml($xmlWriter);
                        }
                    }

                }
            }
        }

		/**
		 * close current element
		 */
        $xmlWriter->endElement();

        if ($needsReturnOutput) {
            return $xmlWriter->outputMemory();
        }
    }

	/**
	 * @param XMLReader $xmlReader
	 * @param array $classMap
	 * @throws XmlMapperException
	 * @throws \ReflectionException
	 */
    private function _mapFromXml(XMLReader $xmlReader, $classMap = []) {

        ///Считываем аттрибуты
        $attributes = [];
		$expanded = $xmlReader->expand();

        while ($xmlReader->moveToNextAttribute()) {
        	$matches = [];
            if (preg_match('~^xmlns:?(?<namespace>'.$expanded->prefix.')$~', $xmlReader->name, $matches)) {
				$this->_namespace = new NodeNamespace($expanded->prefix ?: null, $xmlReader->value ?: null);
            } else {
				$attributes[$xmlReader->name] = $xmlReader->value;
			}
        }
        if (!empty($attributes)) {
            $this->_setAttributes($attributes);
        }



        if ($expanded->prefix && !$this->_namespace) {
			$this->_namespace = new NodeNamespace($expanded->prefix, null);
		}

		$xmlReader->moveToElement();
        // считываем дочерние узлы

        while($xmlReader->read()) {
            if ($xmlReader->nodeType === $xmlReader::END_ELEMENT) {
                //Тэг закрылся...мэп закрылся
                return;
            } else if ($xmlReader->nodeType == $xmlReader::TEXT) {
                $this->_setNodeText($xmlReader->readString());
            } else {

                $classAttributes = get_class_vars(static::class);

                $classChildrenAssociation = [];
                $classChildrenArrayAssociation = [];

                foreach ($classAttributes as $classAttribute => $defaultValue) {

                    // Ключ ассоциаций классмэпа
                    $classMapKey = static::getClassMapKey($classAttribute);

                    // сначала смотрим задан ли в классмэпе ключ
                    if (!empty($classMap[$classMapKey])) {
                        $typeClassName = $classMap[$classMapKey];
                    } else {
                        //ну если нет, то пробуем выяснить через рефлексию
                        $reflectionProperty = new ReflectionProperty($this, $classAttribute);
                        $typeClassName = $reflectionProperty->getType()->getName();
                    }


                    if (is_a($typeClassName, self::class, true)) {
                        $name = $this->getClassTagName($typeClassName);
                        $classChildrenAssociation[$name] = [
                            'property' => $classAttribute,
                            'class' => $typeClassName,
                        ];

                    } else if(is_a($typeClassName, NodeArray::class, true)) {
                        /** @var NodeArray $nodeArray */
                        $name = $this->getClassTagName(Helper::getDefaultProperty($typeClassName, 'className'));
                        $classChildrenArrayAssociation[$name] = [
                            'property' => $classAttribute,
                            'class' => $typeClassName,
                        ];
                    }
                }


                if ($childAssociation = $classChildrenAssociation[$xmlReader->localName] ?? false) {

                    /** @var NodeElement $object */
                    $object = new $childAssociation['class'];
                    $object->_mapFromXml($xmlReader, $classMap);
                    $this->{$childAssociation['property']} = $object;

                } else if ($childAssociation = $classChildrenArrayAssociation[$xmlReader->localName] ?? false) {

                    if (empty($this->{$childAssociation['property']})) {
                        $this->{$childAssociation['property']} = new $childAssociation['class'];
                    }

                    /** @var NodeArray $arrayObject */
                    $arrayObject = $this->{$childAssociation['property']};

                    $objectClassName = $arrayObject->getClassName();
                    $object = new $objectClassName;
                    $object->_mapFromXml($xmlReader, $classMap);

                    $arrayObject->addItem($object);
                }
            }
        }

    }

	/**
	 * @param $classname
	 * @return mixed
	 * @throws \ReflectionException
	 */
    private function getClassTagName($classname) {
        return Helper::getDefaultProperty($classname, '_name') ?: Helper::getClassBaseName($classname);
    }

	/**
	 * unique key to search in classmap
	 * @param $attribute
	 * @return string
	 */
	public static function getClassMapKey($attribute): string {
		return implode(':', [static::class, $attribute]);
	}

	/**
	 * For dynamic properties need use classMap option,
	 * with key - static::getClassMapKey($attribute) and value classname of will map object
	 * @param string|null $xml
	 * @param string[] $classMap
	 * @param XMLReader|null $xmlReader
	 * @return static
	 * @throws XmlMapperException
	 * @throws \ReflectionException
	 */
    public static function mapFromXml(?string $xml = null, array $classMap = [], ?XMLReader $xmlReader = null): NodeElement {

        if (!$xml && !$xmlReader) {
            throw new XmlMapperException('You must specify xml or xmlReader param');
        }

        if (!$xmlReader) {
            $xmlReader = new XMLReader();
            $xmlReader->XML($xml);
            $xmlReader->read();
        }

        $result = new static();
        $result->_mapFromXml($xmlReader, $classMap);

        $xmlReader->close();
        return $result;
    }

	/**
	 * @param string $attribute
	 * @return string|null
	 */
    public function _getAttribute(string $attribute): ?string {
    	return $this->_attributes[$attribute] ?? null;
	}

    /**
     * @return string[]
     */
    public function _getAttributes(): array {
        return $this->_attributes;
    }

	/**
	 * @param string[] $attributes
	 */
    public function _setAttributes(array $attributes) {
        $this->_attributes = $attributes;
    }

    /**
     * @return string|null
     */
    public function _getName(): ?string {
        return $this->_name ?: Helper::getClassBaseName(static::class);
    }

    /**
     * @param string|null $name
     */
    public function _setName($name) {
        $this->_name = $name;
    }

    /**
     * @return string
     */
    public function _getNodeText(): string {
        return $this->_nodeText;
    }

    /**
     * @param string $nodeText
     */
    public function _setNodeText($nodeText) {
        $this->_nodeText = $nodeText;
    }

	/**
	 * @return NodeNamespace
	 */
    public function _getNamespace(): NodeNamespace {
    	return $this->_namespace ?? new NodeNamespace(null, null);
	}

	/**
	 * @param NodeNamespace $namespace
	 */
	public function _setNamespace(NodeNamespace $namespace) {
    	$this->_namespace = $namespace;
	}
}
