<?php


namespace XmlMapper\Elements;


class NodeNamespace
{
	/**
	 * namespace prefix
	 * @var string|null
	 */
    protected ?string $prefix = null;
	/**
	 * namespace uri
	 * @var string|null
	 */
    protected ?string $uri = null;

    /**
     * NodeNamespace constructor.
     * @param string|null $prefix
     * @param string|null $uri
     */
    public function __construct(?string $prefix, ?string $uri) {
        $this->prefix = $prefix;
        $this->uri = $uri;
    }


    /**
     * @return string|null
     */
    public function getPrefix() {
        return $this->prefix;
    }

    /**
     * @param string|null $prefix
     */
    public function setPrefix($prefix) {
        $this->prefix = $prefix;
    }

    /**
     * @return string|null
     */
    public function getUri() {
        return $this->uri;
    }

    /**
     * @param string|null $uri
     */
    public function setUri($uri) {
        $this->uri = $uri;
    }

}
