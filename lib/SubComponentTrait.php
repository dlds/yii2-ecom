<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 24.01.14
 */

namespace dlds\shop;

/**
 * Common functionality for sub-components of the dlds\shop\Component class
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package dlds\shop
 *
 * @property Component $component
 */
trait SubComponentTrait
{
    /**
     * @var Component
     */
    private $component;

    /**
     * @param \dlds\shop\Component $component
     * @return static
     */
    public function setComponent(Component $component)
    {
        $this->component = $component;
        return $this;
    }

    /**
     * @return Component
     */
    protected function getComponent()
    {
        return $this->component;
    }
} 