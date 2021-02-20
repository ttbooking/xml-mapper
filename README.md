# xml-mapper
Base elements to map xml into objects and objects into xml


#Usage


```xml
<?xml version="1.0" encoding="utf-8" ?>
<bro:root xmlns:bro="http://example.com/schema">
    <ChildNode Value="i am attribute">i am child node</ChildNode>
    <item_text>text number 1</item_text>
    <item_text>text number 2</item_text>
</bro:root>
```

```php
use XmlMapper\Elements\NodeArray;
use XmlMapper\Elements\NodeElement;

/**
* Class ChildNodeArray
 */
class ChildNodeArray extends NodeArray {
    protected string $className = TextItem::class;
}

/**
* Class TextItem
 */
class TextItem extends NodeElement {
    protected ?string $_name = 'item_text'; // setup node element name
}

/**
 * Class ChildNode
 * @property string $Value
 */
class ChildNode extends NodeElement {
//if name not set - use class name as is
}

class SimpleStruct extends NodeElement {
    protected ChildNode $childNode;
    protected ChildNodeArray $array;
    
    public function getChildNode() {
        return $this->childNode;
    }
    
    public function getArray() {
        return $this->array;
    }
    
}

$xml = 'xml from top'; //

///...
$root = SimpleStruct::mapFromXml($xml);

echo $root->getNamespace()->getPrefix(); //prefix
echo $root->getNamespace()->getUri(); //uri

echo $root->getChildNode()->Value;// magic properties == attributes 
echo $root->getChildNode(); //to string - get node text
foreach ($root->getArray() as $textItem) { //iterable
    echo $textItem; // "text number 1", than "text number 2"
}

echo $root->toXml();
```
```xml
<?xml version="1.0" encoding="utf-8" ?>
<bro:SimpleStruct xmlns:bro="http://example.com/schema">
    <ChildNode Value="i am attribute">i am child node</ChildNode>
    <item_text>text number 1</item_text>
    <item_text>text number 2</item_text>
</bro:SimpleStruct>
```
because SimpleStruct has not set property _name = 'root'