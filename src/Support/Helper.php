<?php


namespace XmlMapper\Support;


use ReflectionClass;

class Helper
{
	/**
	 * getting class basename
	 * @param string $className
	 * @return string
	 */
	public static function getClassBaseName(string $className) {
		return basename(str_replace('\\', '/', $className));
	}


	/**
	 * getting property default value
	 * @param $className
	 * @param $propertyName
	 * @return mixed|null
	 * @throws \ReflectionException
	 */
	public static function getDefaultProperty(string $className, string $propertyName) {
		return (new ReflectionClass($className))->getDefaultProperties()[$propertyName] ?? null;
	}



}