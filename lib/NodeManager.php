<?php

namespace Sulu\Component\DocumentManager;

use PHPCR\RepositoryException;
use PHPCR\SessionInterface;
use PHPCR\Util\UUIDHelper;
use Sulu\Component\DocumentManager\Exception\DocumentNotFoundException;

/**
 * The node manager is responsible for talking to the PHPCR
 * implementation.
 */
class NodeManager
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Find a document with the given path or UUID.
     *
     * @param string $identifier UUID or path
     *
     * @return NodeInterface
     *
     * @throws DocumentNotFoundException
     */
    public function find($identifier)
    {
        try {
            if (UUIDHelper::isUUID($identifier)) {
                return $this->session->getNodeByIdentifier($identifier);
            }

            return $this->session->getNode($identifier);
        } catch (RepositoryException $e) {
            throw new DocumentNotFoundException(sprintf(
                'Could not find document with ID or path "%s"', $identifier
            ), null, $e);
        }
    }

    /**
     * Determine if a node exists at the specified path or if a UUID is given,
     * then if a node with the UUID exists.
     *
     * @param string $identifier
     */
    public function has($identifier)
    {
        $this->normalizeToPath($identifier);
        try {
            $this->find($identifier);

            return true;
        } catch (DocumentNotFoundException $e) {
            return false;
        }
    }

    /**
     * Remove the document with the given path or UUID.
     *
     * @param string $identifier ID or path
     */
    public function remove($identifier)
    {
        $identifier = $this->normalizeToPath($identifier);
        $this->session->removeItem($identifier);
    }

    /**
     * Move the documet with the given path or ID to the path
     * of the destination document (as a child).
     *
     * @param string $srcId
     * @param string $destId
     */
    public function move($srcId, $destId, $name)
    {
        $srcPath = $this->normalizeToPath($srcId);
        $destPath = $this->normalizeToPath($destId);
        $destPath = $destPath . '/' . $name;

        $this->session->move($srcPath, $destPath);
    }

    public function copy($srcId, $destId, $name)
    {
        $workspace = $this->session->getWorkspace();
        $srcPath = $this->normalizeToPath($srcId);
        $parentDestPath = $this->normalizeToPath($destId);
        $destPath = $parentDestPath . '/' . $name;

        $workspace->copy($srcPath, $destPath);

        return $destPath;
    }

    public function save()
    {
        $this->session->save();
    }

    public function clear()
    {
        $this->session->refresh(false);
    }

    /**
     * Create a path.
     *
     * @param mixed $path
     */
    public function createPath($path)
    {
        $current = $this->session->getRootNode();

        $segments = preg_split('#/#', $path, null, PREG_SPLIT_NO_EMPTY);
        foreach ($segments as $segment) {
            if ($current->hasNode($segment)) {
                $current = $current->getNode($segment);
            } else {
                $current = $current->addNode($segment);
                $current->addMixin('mix:referenceable');
                $current->setProperty('jcr:uuid', UUIDHelper::generateUUID());
            }
        }

        return $current;
    }

    /**
     * Normalize the given path or ID to a path.
     *
     * @param mixed $identifier
     */
    private function normalizeToPath($identifier)
    {
        if (UUIDHelper::isUUID($identifier)) {
            $identifier = $this->session->getNodeByIdentifier($identifier)->getPath();
        }

        return $identifier;
    }
}
