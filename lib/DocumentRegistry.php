<?php

namespace Sulu\Component\DocumentManager;

use PHPCR\NodeInterface;
use Psr\Log\LoggerInterface;
use Sulu\Component\DocumentManager\Exception\DocumentManagerException;
use Sulu\Component\DocumentManager\DocumentHelper;

/**
 * Handles the mapping between managed documents and nodes.
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
     * @var string
     */
    private $defaultLocale;

    /**
     * @var array
     */
    private $hydrationState = array();

    /**
     * @var LoggerInterface
     */
    private $logger = null;

    /**
     * @param $defaultLocale
     */
    public function __construct($defaultLocale, LoggerInterface $logger = null)
    {
        $this->defaultLocale = $defaultLocale;
        $this->logger = $logger;
    }

    /**
     * Register a document.
     *
     * @param mixed $document
     * @param NodeInterface $node
     */
    public function registerDocument($document, NodeInterface $node, $locale = null)
    {
        if (null === $locale) {
            $locale = $this->defaultLocale;
        }

        $oid = $this->getObjectIdentifier($document);
        $uuid = $node->getIdentifier();

        $this->logDocument($document, sprintf(
            'node: "%s" (%s), locale: "%s"',
            $node->getPath(), $uuid, $locale
        ));

        // do not allow nodes wihout UUIDs or reregistration of documents
        $this->validateDocumentRegistration($document, $node, $oid, $uuid);

        $this->documentMap[$oid] = $document;
        $this->documentNodeMap[$oid] = $uuid;
        $this->nodeMap[$node->getIdentifier()] = $node;
        $this->nodeDocumentMap[$node->getIdentifier()] = $document;
        $this->documentLocaleMap[$oid] = $locale;
    }

    /**
     * Update the locale of the given document and store the originally
     * requested locale.
     *
     * The originally requested locale should be reset when a HYDRATE event
     * is caused by the user (and not internally when loading dependencies).
     */
    public function updateLocale($document, $locale, $originalLocale = null)
    {
        $oid = $this->getObjectIdentifier($document);
        $this->originalLocaleMap[$oid] = $originalLocale;
        $this->documentLocaleMap[$oid] = $locale;

        $this->logDocument($document, sprintf(
            'locale: "%s", original locale: "%s"',
            $locale, $originalLocale
        ));
    }

    /**
     * Return true if the document is managed.
     *
     * @param object $document
     *
     * @return bool
     */
    public function hasDocument($document)
    {
        $oid = $this->getObjectIdentifier($document);

        return isset($this->documentMap[$oid]);
    }

    /**
     * Return true if the node is managed.
     *
     * @param NodeInterface $node
     *
     Id* @return bool
     */
    public function hasNode(NodeInterface $node)
    {
        return isset($this->nodeDocumentMap[$node->getIdentifier()]);
    }

    /**
     * Clear the registry (detach all documents).
     */
    public function clear()
    {
        $this->documentMap = array();
        $this->documentNodeMap = array();
        $this->nodeMap = array();
        $this->nodeDocumentMap = array();
        $this->documentLocaleMap = array();
        $this->originalLocaleMap = array();
        $this->hydrationState = array();

        if ($this->logger) {
            $this->logger->debug('clear');
        }
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

        $this->assertDocumentExists($document);

        $nodeIdentifier = $this->documentNodeMap[$oid];

        unset($this->nodeMap[$nodeIdentifier]);
        unset($this->nodeDocumentMap[$nodeIdentifier]);
        unset($this->documentMap[$oid]);
        unset($this->documentNodeMap[$oid]);
        unset($this->documentLocaleMap[$oid]);
        unset($this->originalLocaleMap[$oid]);
        unset($this->hydrationState[$oid]);

        $this->logDocument($document, '');
    }

    /**
     * Return the node for the given managed document.
     *
     * @param object $document
     *
     * @throws \RuntimeException If the node is not managed
     *
     * @return NodeInterface
     */
    public function getNodeForDocument($document)
    {
        $oid = $this->getObjectIdentifier($document);
        $this->assertDocumentExists($document);

        return $this->nodeMap[$this->documentNodeMap[$oid]];
    }

    /**
    /**
     * Return the current locale for the given document.
     *
     * @param object $document
     *
     * @return string
     */
    public function getLocaleForDocument($document)
    {
        $oid = $this->getObjectIdentifier($document);
        $this->assertDocumentExists($document);

        return $this->documentLocaleMap[$oid];
    }

    /**
     * Return the original locale for the document.
     *
     * @param object $document
     *
     * @return string
     */
    public function getOriginalLocaleForDocument($document)
    {
        $oid = $this->getObjectIdentifier($document);
        $this->assertDocumentExists($document);

        if (isset($this->originalLocaleMap[$oid])) {
            return $this->originalLocaleMap[$oid];
        }

        return $this->getLocaleForDocument($document);
    }

    /**
     * Return the document for the given managed node.
     *
     * @param NodeInterface $node
     *
     * @throws \RuntimeException If the node is not managed
     */
    public function getDocumentForNode(NodeInterface $node)
    {
        $identifier = $node->getIdentifier();
        $this->assertNodeExists($identifier);

        return $this->nodeDocumentMap[$identifier];
    }

    /**
     * Return the default locale.
     *
     * @return string
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * @param mixed $oid
     */
    private function assertDocumentExists($document)
    {
        $oid = spl_object_hash($document);

        if (!isset($this->documentMap[$oid])) {
            throw new \RuntimeException(sprintf(
                'Document "%s" with OID "%s" is not managed, there are "%s" managed objects,',
                get_class($document), $oid, count($this->documentMap)
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
     * Get the spl object hash for the given object.
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

    /**
     * Register that the document has been hydrated and that it should
     * not be hydrated again.
     *
     * @param object $document
     */
    public function markDocumentAsHydrated($document)
    {
        $oid = spl_object_hash($document);
        $this->hydrationState[$oid] = true;

        $this->logDocument($document, '');
    }

    /**
     * Unmark the document as being hydrated. It will then be
     * rehydrated the next time a HYDRATE event is fired for ot.
     *
     * @param object $document
     */
    public function unmarkDocumentAsHydrated($document)
    {
        $oid = spl_object_hash($document);
        unset($this->hydrationState[$oid]);

        $this->logDocument($document);
    }

    /**
     * Return true if the document is a candidate for hydration/re-hydration.
     *
     * @param object $document
     *
     * @return bool
     */
    public function isHydrated($document)
    {
        $oid = spl_object_hash($document);

        if (isset($this->hydrationState[$oid])) {
            return true;
        }

        return false;
    }

    private function logDocument($document, $message = '')
    {
        if (!$this->logger) {
            return;
        }
        $callers = debug_backtrace();
        $fromMethod = $callers[1]['function'];
        $caller = $callers[2]['class'];
        $callerFunction = $callers[2]['function'];

        $message = sprintf(
            '%-24s: %s %s. Caller: %s#%s)',
            $fromMethod,
            $this->hasDocument($document) ? DocumentHelper::getDebugTitle($document) : spl_object_hash($document) . ' (unmanaged)',
            $message,
            $caller,
            $callerFunction
        );

        $this->logger->debug($message);

    }
}
