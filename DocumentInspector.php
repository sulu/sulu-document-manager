<?php

namespace Sulu\Component\DocumentManager;

use Sulu\Component\DocumentManager\DocumentRegistry;
use Sulu\Component\DocumentManager\PathSegmentRegistry;

/**
 * This class infers information about documents, for example
 * the documents locale, webspace, path, etc.
 */
class DocumentInspector
{
    protected $documentRegistry;
    protected $pathSegmentRegistry;
    protected $proxyFactory;

    public function __construct(
        DocumentRegistry $documentRegistry,
        PathSegmentRegistry $pathSegmentregistry,
        ProxyFactory $proxyFactory
    )
    {
        $this->documentRegistry = $documentRegistry;
        $this->pathSegmentRegistry = $pathSegmentregistry;
        $this->proxyFactory = $proxyFactory;
    }

    public function getParent($document)
    {
        $parentNode = $this->getNode($document)->getParent();
        return $this->proxyFactory->createProxyForNode($parentNode);
    }

    public function getNode($document)
    {
        return $this->documentRegistry->getNodeForDocument($document);
    }

    public function getChildren($document)
    {
        return new ChildrenCollection($document);
    }

    /**
     * Return the locale for the given document
     *
     * @param object $document
     *
     * @return string
     */
    public function getLocale($document)
    {
        return $this->documentRegistry->getLocaleForDocument($document);
    }

    /**
     * Return the path for the given document
     *
     * @param object $document
     *
     * @return string
     */
    public function getPath($document)
    {
        return $this->documentRegistry
            ->getNodeForDocument($document)
            ->getPath();
    }
}
