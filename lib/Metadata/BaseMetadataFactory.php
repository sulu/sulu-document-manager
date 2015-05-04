<?php

/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Metadata;

use PHPCR\NodeInterface;
use Sulu\Component\DocumentManager\ClassNameInflector;
use Sulu\Component\DocumentManager\Exception\MetadataNotFoundException;
use Sulu\Component\DocumentManager\Metadata;
use Sulu\Component\DocumentManager\MetadataFactoryInterface;

/**
 * Simple metadata factory which uses an array map.
 *
 * Note that this class does not  implement the getMetadataForPhpcrNode method
 * as that would require a circular dependency.
 */
class BaseMetadataFactory implements MetadataFactoryInterface
{
    /**
     * @var array
     */
    private $aliasMap = array();

    /**
     * @var array
     */
    private $classMap = array();

    /**
     * @var array
     */
    private $phpcrTypeMap = array();

    /**
     * @param array $mapping
     */
    public function __construct(
        array $mapping
    ) {
        foreach ($mapping as $map) {
            $this->aliasMap[$map['alias']] = $map;
            $this->classMap[$map['class']] = $map;
            $this->phpcrTypeMap[$map['phpcr_type']] = $map;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadataForAlias($alias)
    {
        if (!isset($this->aliasMap[$alias])) {
            throw new MetadataNotFoundException(sprintf(
                'Metadata with alias "%s" not found, known aliases: "%s"',
                $alias, implode('", "', array_keys($this->aliasMap))
            ));
        }

        $map = $this->aliasMap[$alias];

        return $this->getMetadata($map);
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadataForPhpcrType($phpcrType)
    {
        if (!isset($this->phpcrTypeMap[$phpcrType])) {
            throw new MetadataNotFoundException(sprintf(
                'Metadata with phpcrType "%s" not found, known phpcrTypes: "%s"',
                $phpcrType, implode('", "', array_keys($this->phpcrTypeMap))
            ));
        }

        $map = $this->phpcrTypeMap[$phpcrType];

        return $this->getMetadata($map);
    }

    /**
     * {@inheritDoc}
     */
    public function hasMetadataForPhpcrType($phpcrType)
    {
        return isset($this->phpcrTypeMap[$phpcrType]);
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadataForClass($class)
    {
        $class = ClassNameInflector::getUserClassName($class);

        if (!isset($this->classMap[$class])) {
            throw new MetadataNotFoundException(sprintf(
                'Metadata with class "%s" not found, known classes: "%s"',
                $class, implode('", "', array_keys($this->classMap))
            ));
        }

        $map = $this->classMap[$class];

        return $this->getMetadata($map);
    }

    /**
     * {@inheritDoc}
     */
    public function hasAlias($alias)
    {
        return isset($this->aliasMap[$alias]);
    }

    /**
     * {@inheritDoc}
     */
    public function getAliases()
    {
        return array_keys($this->aliasMap);
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadataForPhpcrNode(NodeInterface $node)
    {
        throw new \BadMethodCallException(
            'The BaseMetadataFactory does not implement this method'
        );
    }

    /**
     * @param array $mapping
     *
     * @return Metadata
     */
    private function getMetadata($mapping)
    {
        $metadata = new Metadata();
        $metadata->setAlias($mapping['alias']);
        $metadata->setPhpcrType($mapping['phpcr_type']);
        $metadata->setClass($mapping['class']);

        return $metadata;
    }
}
