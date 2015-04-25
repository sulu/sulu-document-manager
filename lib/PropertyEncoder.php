<?php

namespace Sulu\Component\DocumentManager;

/**
 * Class responsible for encoding properties to PHPCR nodes.
 */
class PropertyEncoder
{
    private $namespaceRegistry;

    /**
     * @param NamespaceRegistry $namespaceRegistry
     */
    public function __construct(NamespaceRegistry $namespaceRegistry)
    {
        $this->namespaceRegistry = $namespaceRegistry;
    }

    /**
     * @param string $name
     * @param string $locale
     *
     * @return string
     */
    public function localizedSystemName($name, $locale)
    {
        return $this->formatLocalizedName('system_localized', $name, $locale);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function systemName($name)
    {
        return $this->formatName('system', $name);
    }

    /**
     * @param string $name
     * @param string $locale
     *
     * @return string
     */
    public function localizedContentName($name, $locale)
    {
        return $this->formatLocalizedName('content_localized', $name, $locale);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function contentName($name)
    {
        return $this->formatName('content', $name);
    }

    private function formatName($role, $name)
    {
        $prefix = $this->namespaceRegistry->getPrefix($role);

        if (!$prefix) {
            return $name;
        }

        return sprintf(
            '%s:%s',
            $prefix,
            $name
        );
    }

    private function formatLocalizedName($role, $name, $locale)
    {
        $prefix = $this->namespaceRegistry->getPrefix($role);

        if (!$prefix) {
            return sprintf('%s-%s', $locale, $name);
        }

        return sprintf(
            '%s:%s-%s',
            $prefix,
            $locale,
            $name
        );
    }
}
