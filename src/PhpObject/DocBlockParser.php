<?php

namespace Giansalex\PhpObject;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\ContextFactory;

class DocBlockParser
{
    /**
     * @var Context[]
     */
    private array $contexts = [];
    private DocBlockFactory $docBlockFactory;
    private ContextFactory $contextFactory;

    public function __construct()
    {
        $this->docBlockFactory = DocBlockFactory::createInstance();
        $this->contextFactory = new ContextFactory();
    }


    public function getDocBlock(string $class, string $property): ?DocBlock
    {
        $propertyHash = sprintf('%s::%s', $class, $property);

        if (isset($this->docBlocks[$propertyHash])) {
            return $this->docBlocks[$propertyHash];
        }

        return $this->getDocBlockFromProperty($class, $property);
    }

    private function getDocBlockFromProperty(string $class, string $property): ?DocBlock
    {
        try {
            $reflectionProperty = new \ReflectionProperty($class, $property);
        } catch (\ReflectionException $e) {
            return null;
        }

        $reflector = $reflectionProperty->getDeclaringClass();

        try {
            return $this->docBlockFactory->create($reflectionProperty, $this->createFromReflector($reflector));
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            return null;
        }
    }

    /**
     * Prevents a lot of redundant calls to ContextFactory::createForNamespace().
     */
    private function createFromReflector(\ReflectionClass $reflector): Context
    {
        $cacheKey = $reflector->getNamespaceName().':'.$reflector->getFileName();

        if (isset($this->contexts[$cacheKey])) {
            return $this->contexts[$cacheKey];
        }

        $this->contexts[$cacheKey] = $this->contextFactory->createFromReflector($reflector);

        return $this->contexts[$cacheKey];
    }
}