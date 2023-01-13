<?php
/**
 * Created by PhpStorm.
 * User: Administrador
 * Date: 31/01/2018
 * Time: 10:39 AM
 */

namespace Giansalex\PhpObject;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Type;

/**
 * Class Swagger
 */
class Swagger
{
    /**
     * @var array
     */
    private $all;

    /**
     * @var array
     */
    private $mapTypes = ['int' => 'integer', 'bool' => 'boolean'];

    private PropertyInfoExtractor $extractor;
    private DocBlockParser $docParser;

    /**
     * Swagger constructor.
     */
    public function __construct()
    {
        $this->extractor = $this->getPropertyExtractor();
        $this->docParser = new DocBlockParser();
    }

    /**
     * @param string $class
     * @return array
     */
    public function fromClass($class)
    {
        return $this->fromClasses([$class]);
    }

    /**
     * @param array $classes
     * @return array
     */
    public function fromClasses(array $classes)
    {
        $this->all = [];
        foreach ($classes as $class) {
            $this->registerClass($class);
        }

        return ['definitions' => $this->all];
    }

    /**
     * @return PropertyInfoExtractor
     */
    private function getPropertyExtractor()
    {
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

        $this->extractor = new PropertyInfoExtractor(
            $listExtractors,
            $typeExtractors,
            $descriptionExtractors,
            $accessExtractors
        );

        return $this->extractor;
    }

    /**
     * @param string $class
     * @return array
     */
    function getProperties($class)
    {
        $props = [];
        $properties = $this->extractor->getProperties($class);
        foreach ($properties as $property) {
            $types = $this->extractor->getTypes($class, $property);
            if ($types == null) {
                exit();
            }

            $doc = $this->docParser->getDocBlock($class, $property);
            foreach ($types as $type) {
                /**@var $type Type*/
                $tipo = $type->getBuiltinType();
                if ($tipo == 'array') {

                    $prop = ['type' => 'array', 'items' => $this->getItemArray($type, $class)];
                } elseif ($tipo == 'object') {
                    $className = $type->getClassName();
                    if ($this->isDateTime($className)) {
                        $prop = ['type' => 'string', 'format' => 'date-time'];
                    } else {
                        $name = $this->registerClass($className);
                        $prop = ['$ref' => '#/definitions/'.$name];
                    }
                } elseif ($tipo == 'float') {
                    $prop = ['type' => 'number', 'format' => 'float'];
                } else {
                    $prop = ['type' => $this->getValidType($tipo)];
                }

                if ($doc->hasTag('deprecated')) {
                    $prop['deprecated'] = true;
                }

                $props[$property] = $prop;
            }
        }

        return $props;
    }

    /**
     * @param string $class
     * @return string
     */
    private function registerClass($class)
    {
        $name = $this->getNameClass($class);
        if (isset($this->all[$name])) {
            return $name;
        }

        $parent = get_parent_class($class);
        $props = [];
        if ($parent) {
            $props = $this->getProperties($parent);
        }

        $props = array_merge($props, $this->getProperties($class));
        $this->all[$name] = [
            'type' => 'object',
            'properties' => $props
        ];

        return $name;
    }

    /**
     * @param string $class
     * @return string
     */
    private function getNameClass($class) {
        $path = explode('\\', $class);

        return array_pop($path);
    }

    private function getItemArray(Type $type, string $class)
    {
        $typeCollection = $type->getCollectionValueType();
        if (empty($typeCollection)) {
            return ['type' => 'string'];
        }

        $className = $typeCollection->getClassName();
        if ($className) {
            $name = $className === $class ? $class : $this->registerClass($className);
            $itemType = ['$ref' => '#/definitions/'.$name];
        } else {
            $type = $typeCollection->getBuiltinType();
            $itemType = ['type' => $this->getValidType($type)];
        }

        return $itemType;
    }

    private function getValidType($type)
    {
        if (isset($this->mapTypes[$type])) {
            return $this->mapTypes[$type];
        }

        return $type;
    }

    /**
     * @param $className
     * @return bool
     */
    private function isDateTime($className)
    {
        return
            $className == 'DateTimeInterface' ||
            $className == 'DateTime' ||
            $className == 'DateTimeImmutable';
    }
}