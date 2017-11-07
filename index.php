<?php
/**
 * Created by PhpStorm.
 * User: Giansalex
 * Date: 06/11/2017
 * Time: 19:00
 */
require 'vendor/autoload.php';

use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Yaml\Yaml;

// a full list of extractors is shown further below
$phpDocExtractor = new PhpDocExtractor();
$reflectionExtractor = new ReflectionExtractor();

// array of PropertyListExtractorInterface
$listExtractors = array($reflectionExtractor);

// array of PropertyTypeExtractorInterface
$typeExtractors = array($phpDocExtractor, $reflectionExtractor);

// array of PropertyDescriptionExtractorInterface
$descriptionExtractors = array($phpDocExtractor);

// array of PropertyAccessExtractorInterface
$accessExtractors = array($reflectionExtractor);

$propertyInfo = new PropertyInfoExtractor(
    $listExtractors,
    $typeExtractors,
    $descriptionExtractors,
    $accessExtractors
);
$all = [];

function getName($class) {
    $path = explode('\\', $class);
    return array_pop($path);
}

function getProperties($class)
{
    global $propertyInfo;

    $props = [];
    $properties = $propertyInfo->getProperties($class);
    foreach ($properties as $property) {
        $types = $propertyInfo->getTypes($class, $property);
        foreach ($types as $type) {
            /**@var $type \Symfony\Component\PropertyInfo\Type*/
            $tipo = $type->getBuiltinType();
            if ($tipo == 'array') {
                $name = registerClass($type->getCollectionValueType()->getClassName());
                $prop = ['type' => 'array', 'items' => ['$ref' => '#/definitions/'.$name]];
            } elseif ($tipo == 'object') {
                if ($type->getClassName() == 'DateTime') {
                    $prop = ['type' => 'string', 'format' => 'date'];
                } else {
                    $name = registerClass($type->getClassName());
                    $prop = ['$ref' => '#/definitions/'.$name];
                }
            } elseif ($tipo == 'float') {
                $prop = ['type' => 'number', 'format' => 'float'];
            } else {
                $prop = ['type' => $tipo];
            }

            $props[$property] = $prop;
        }
    }

    return $props;
}

function registerClass($class)
{
    global $all;

    $name = getName($class);
    if (isset($all[$name])) {
        return $name;
    }

    $parent = get_parent_class($class);
    $props = [];
    if ($parent) {
        $props = getProperties($parent);
    }

    $props = array_merge($props, getProperties($class));
    $all[$name] = [
        'type' => 'object',
        'properties' => $props
    ];

    return $name;
}

// Built class and dependencies
$classes = [
    \Greenter\Model\Sale\Invoice::class,
    \Greenter\Model\Sale\Note::class,
    \Greenter\Model\Sale\Receipt::class,
];

foreach ($classes as $class) {
    registerClass($class);
}

$all = ['definitions' => $all];

$json = json_encode($all, JSON_PRETTY_PRINT);
$yaml = Yaml::dump($all, 8, 2);

file_put_contents('swagger.json', $json);
file_put_contents('swagger.yaml', $yaml);
