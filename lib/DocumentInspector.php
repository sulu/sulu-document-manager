<?php

namespace Sulu\Component\DocumentManager;

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
    ) {
        $this->documentRegistry = $documentRegistry;
        $this->pathSegmentRegistry = $pathSegmentregistry;
        $this->proxyFactory = $proxyFactory;
    }

    /**
     * Return the parent document for the given document.
     *
     * @param object $document
     *
     * @return object
     */
    public function getParent($document)
    {
        $parentNode = $this->getNode($document)->getParent();

        if (!$parentNode) {
            return;
        }

        return $this->proxyFactory->createProxyForNode($document, $parentNode);
    }

    /**
     * Get referrers for the document.
     *
     * @return ReferrerCollection
     */
    public function getReferrers($document)
    {
        return $this->proxyFactory->createReferrerCollection($document);
    }

    /**
     * Return the PHPCR node for the given document.
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
     * Returns lazy-loading children collection for given document.
     *
     * @param object $document
     *
     * @return ChildrenCollection
     */
    public function getChildren($document, array $options = array())
    {
        return $this->proxyFactory->createChildrenCollection($document, $options);
    }

    /**
     * Return the locale for the given document.
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
     * Reutrn the original (requested) locale for the given document.
     *
     * @param object $document
     *
     * @return string
     */
    public function getOriginalLocale($document)
    {
        return $this->documentRegistry->getOriginalLocaleForDocument($document);
    }

    /**
     * Return the depth of the given document within the content repository.
     *
     * @return int
     */
    public function getDepth($document)
    {
        return $this->getNode($document)->getDepth();
    }

    /**
     * Return the name of the document.
     *
     * @return string
     */
    public function getName($document)
    {
        return $this->getNode($document)->getName();
    }

    /**
     * Return the path for the given document.
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

    /**
     * Return the UUID of the given document.
     *
     * @param object $document
     *
     * @return string
     */
    public function getUuid($document)
    {
        return $this->documentRegistry
            ->getNodeForDocument($document)
            ->getIdentifier();
    }
}
