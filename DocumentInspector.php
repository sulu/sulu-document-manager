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

    /**
     * Return the parent document for the given document
     *
     * @param object $document
     *
     * @return object
     */
    public function getParent($document)
    {
        $parentNode = $this->getNode($document)->getParent();
        return $this->proxyFactory->createProxyForNode($parentNode);
    }

    /**
     * Return the PHPCR node for the given document
     *
     * @param object $document
     *
     * @return NodeInterface
     */
    public function getNode($document)
    {
        return $this->documentRegistry->getNodeForDocument($document);
    }

    /**
     * Returns lazy-loading children collection for given document
     *
     * @param object $document
     *
     * @return ChildrenCollection
     */
    public function getChildren($document)
    {
        return $this->proxyFactory->createChildrenCollection($document);
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
     * Return the depth of the given document within the content repository
     *
     * @return integer
     */
    public function getDepth($document)
    {
        return $this->getNode($document)->getDepth();
    }

    /**
     * Return the name of the document
     *
     * @return string
     */
    public function getName($document)
    {
        return $this->getNode($document)->getName();
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
