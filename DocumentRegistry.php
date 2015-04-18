<?php

namespace Sulu\Component\DocumentManager;

use PHPCR\NodeInterface;
use ProxyManager\Proxy\LazyLoadingInterface;
use Sulu\Component\DocumentManager\Exception\DocumentManagerException;

/**
 * Handles the mapping between managed documents and nodes
 *
 * TODO: There is currently no rollback support -- i.e. if a document
 *       is deregistered but the PHPCR session fails to save, then the document
 *       will remain deregistered here and we will have inconsistent state.
 */
class DocumentRegistry
{
    /**
     * @var array
     */
    private $documentMap;

    /**
     * @var array
     */
    private $documentNodeMap;

    /**
     * @var array
     */
    private $nodeDocumentMap;

    /**
     * @var array
     */
    private $documentLocaleMap;

    /**
     * @var array
     */
    private $originalLocaleMap;

    /**
     * Register a document
     *
     * @param mixed $document
     * @param NodeInterface $node
     */
    public function registerDocument($document, NodeInterface $node, $locale)
    {
        $oid = $this->getObjectIdentifier($document);
        $uuid = $node->getIdentifier();

        $this->validateDocumentRegistration($document, $node, $oid, $uuid);

        $this->documentMap[$oid] = $document;
        $this->documentNodeMap[$oid] = $uuid;
        $this->nodeMap[$node->getIdentifier()] = $node;
        $this->nodeDocumentMap[$node->getIdentifier()] = $document;
        $this->documentLocaleMap[$oid] = $locale;

    }

    public function updateLocale($document, $locale, $originalLocale = null)
    {
        $oid = $this->getObjectIdentifier($document);
        $this->originalLocaleMap[$oid] = $originalLocale;
        $this->documentLocaleMap[$oid] = $locale;
    }

    /**
     * Return true if the document is managed
     *
     * @param object $document
     *
     * @return boolean
     */
    public function hasDocument($document)
    {
        $oid = $this->getObjectIdentifier($document);

        return isset($this->documentMap[$oid]);
    }

    /**
     * Return true if the node is managed
     *
     * @param NodeInterface $node
     Id* @return boolean
     */
    public function hasNode(NodeInterface $node)
    {
        return isset($this->nodeDocumentMap[$node->getIdentifier()]);
    }

    /**
     * Clear the registry (detach all documents)
     */
    public function clear()
    {
        $this->documentMap = array();
        $this->documentNodeMap = array();
        $this->nodeMap = array();
        $this->nodeDocumentMap = array();
        $this->documentLocaleMap = array();
        $this->originalLocaleMap = array();
    }

    /**
     * Remove all references to the given document and its
     * associated node.
     *
     * @param object $document
     */
    public function deregisterDocument($document)
    {
        $oid = $this->getObjectIdentifier($document);

        $this->assertDocumentExists($oid);

        $nodeIdentifier = $this->documentNodeMap[$oid];

        unset($this->nodeMap[$nodeIdentifier]);
        unset($this->nodeDocumentMap[$nodeIdentifier]);
        unset($this->documentMap[$oid]);
        unset($this->documentNodeMap[$oid]);
        unset($this->documentLocaleMap[$oid]);
        unset($this->originalLocaleMap[$oid]);
    }

    /**
     * Return the node for the given managed document
     *
     * @param object $document
     * @throws \RuntimeException If the node is not managed
     * @return NodeInterface
     */
    public function getNodeForDocument($document)
    {
        $oid = $this->getObjectIdentifier($document);
        $this->assertDocumentExists($oid);

        return $this->nodeMap[$this->documentNodeMap[$oid]];
    }

    /**
    /**
     * Return the current locale for the given document
     *
     * @param object $document
     *
     * @return string
     */
    public function getLocaleForDocument($document)
    {
        $oid = $this->getObjectIdentifier($document);
        $this->assertDocumentExists($oid);

        return $this->documentLocaleMap[$oid];
    }

    /**
     * Return the original locale for the document
     *
     * @param object $document
     *
     * @return string
     */
    public function getOriginalLocaleForDocument($document)
    {
        $oid = $this->getObjectIdentifier($document);
        $this->assertDocumentExists($oid);

        if (isset($this->originalLocaleMap[$oid])) {
            return $this->originalLocaleMap[$oid];
        }

        return $this->getLocaleForDocument($document);
    }


    /**
     * Return the document for the given managed node
     *
     * @param NodeInterface $node
     * @throws \RuntimeException If the node is not managed
     */
    public function getDocumentForNode(NodeInterface $node)
    {
        $identifier = $node->getIdentifier();
        $this->assertNodeExists($identifier);

        return $this->nodeDocumentMap[$identifier];
    }

    /**
     * @param mixed $oid
     */
    private function assertDocumentExists($oid)
    {
        if (!isset($this->documentMap[$oid])) {
            throw new \RuntimeException(sprintf(
                'Document with OID "%s" is not managed, there are "%s" managed objects,',
                $oid, count($this->documentMap)
            ));
        }
    }

    /**
     * @param mixed $identifier
     */
    private function assertNodeExists($identifier)
    {
        if (!isset($this->nodeDocumentMap[$identifier])) {
            throw new \RuntimeException(sprintf(
                'Node with identifier "%s" is not managed, there are "%s" managed objects,',
                $identifier, count($this->documentMap)
            ));
        }
    }

    /**
     * Get the spl object hash for the given object
     *
     * @param object $document
     *
     * @return string
     */
    private function getObjectIdentifier($document)
    {
        return spl_object_hash($document);
    }

    /**
     * Ensure that the document is not already registered and that the node
     * has a UUID.
     *
     * @param string $oid Object ID
     * @param string $uuid Node UUID
     */
    private function validateDocumentRegistration($document, $node, $oid, $uuid)
    {
        if (null === $uuid) {
            throw new DocumentManagerException(sprintf(
                'Node "%s" of type "%s" has no UUID. Only referencable nodes can be registered by the document manager',
                $node->getPath(), $node->getPrimaryNodeType()->getName()
            ));
        }

        if (isset($this->nodeMap[$uuid])) {
            $registeredDocument = $this->nodeDocumentMap[$uuid];
            throw new \RuntimeException(sprintf(
                'Document "%s" (%s) is already registered for node "%s" (%s) when trying to register document "%s" (%s)',
                spl_object_hash($registeredDocument),
                get_class($registeredDocument),
                $uuid,
                $node->getPath(),
                $oid,
                get_class($document)
            ));
        }
    }
}
