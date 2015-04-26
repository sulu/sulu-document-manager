<?php

/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager;

use PHPCR\NodeInterface;
use Sulu\Component\DocumentManager\Document\UnknownDocument;
use Sulu\Component\DocumentManager\Exception\MetadataNotFoundException;
use Sulu\Component\DocumentManager\DocumentStrategyInterface;

/**
 * Simple metadata factory which uses an array map
 */
class MetadataFactory
{
    /**
     * @var DocumentStrategyInterface
     */
    private $documentStrategy;

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
        array $mapping,
        DocumentStrategyInterface $documentStrategy
    )
    {
        foreach ($mapping as $map) {
            $this->aliasMap[$map['alias']] = $map;
            $this->classMap[$map['class']] = $map;
            $this->phpcrTypeMap[$map['phpcr_type']] = $map;
        }

        $this->documentStrategy = $documentStrategy;
    }

    /**
     * Return metadata for the given alias.
     *
     * @param string $alias
     *
     * @return Metadata
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
     * Return metadata for the given PHPCR type (e.g. sulu:page).
     *
     * @param string $phpcrType
     *
     * @return Metadata
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
     * Return true if there is metadata for the given PHPCR type.
     *
     * @param string $phpcrType
     *
     * @return bool
     */
    public function hasMetadataForPhpcrType($phpcrType)
    {
        return isset($this->phpcrTypeMap[$phpcrType]);
    }

    /**
     * Return metadata for the given NodeInterface or return
     * metadata for the UnknownDocument if the node is not managed.
     *
     * @param NodeInterface $node
     *
     * @return object
     */
    public function getMetadataForPhpcrNode(NodeInterface $node)
    {
        if ($metadata = $this->documentStrategy->resolveMetadataForNode($node)) {
            return $metadata;
        }

        return $this->getUnknownMetadata();
    }

    /**
     * Return metadata for the given class.
     *
     * @param mixed $class
     *
     * @return Metadata
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
     * Return true if the given alias exists.
     *
     * @return bool
     */
    public function hasAlias($alias)
    {
        return isset($this->aliasMap[$alias]);
    }

    /**
     * Return all registered aliases.
     *
     * @return array
     */
    public function getAliases()
    {
        return array_keys($this->aliasMap);
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

    /**
     * @return Metadata
     */
    private function getUnknownMetadata()
    {
        return $this->getMetadata(array(
            'alias' => null,
            'class' => UnknownDocument::class,
            'phpcr_type' => null,
        ));
    }
}
