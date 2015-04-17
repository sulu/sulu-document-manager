<?php

namespace Sulu\Component\DocumentManager;

use PHPCR\Util\UUIDHelper;
use PHPCR\SessionInterface;
use Sulu\Component\DocumentManager\Exception\DocumentNotFoundException;
use PHPCR\RepositoryException;
use PHPCR\Util\PathHelper;
use PHPCR\Util\NodeHelper;

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
     * Find a document with the given path or UUID
     *
     * @param string $id UUID or path
     * @return NodeInterface
     *
     * @throws DocumentNotFoundException
     */
    public function find($id)
    {
        try {
            if (UUIDHelper::isUUID($id)) {
                return $this->session->getNodeByIdentifier($id);
            }

            return $this->session->getNode($id);
        } catch (RepositoryException $e) {
            throw new DocumentNotFoundException(sprintf(
                'Could not find document with ID or path "%s"', $id
            ), null, $e);
        }
    }

    /**
     * Remove the document with the given path or UUID
     *
     * @param string $id ID or path
     */
    public function remove($id)
    {
        $id = $this->normalizeToPath($id);
        $this->session->removeItem($id);
    }

    /**
     * Move the documet with the given path or ID to the path
     * of the destination document (as a child)
     *
     * @param string $srcId
     * @param string $destId
     */
    public function move($srcId, $destId)
    {
        $srcPath = $this->normalizeToPath($srcId);
        $destPath = $this->normalizeToPath($destId);
        $destPath = $destPath . '/' . PathHelper::getNodeName($srcPath);

        $this->session->move($srcPath, $destPath);
    }

    public function copy($srcId, $destId)
    {
        $workspace = $this->session->getWorkspace();
        $srcPath = $this->normalizeToPath($srcId);
        $destPath = $this->normalizeToPath($destId);
        $destPath = $destPath . '/' . PathHelper::getNodeName($srcPath);

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
     * Create a path
     *
     * @param mixed $path
     */
    public function createPath($path)
    {
        NodeHelper::createPath($this->session, $path);
    }

    /**
     * Normalize the given path or ID to a path
     *
     * @param mixed $id
     */
    private function normalizeToPath($id)
    {
        if (UUIDHelper::isUUID($id)) {
            $id = $this->session->getNodeByIdentifier($id)->getPath();
        }

        return $id;
    }
}
