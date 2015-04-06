<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 24.01.14
 */

namespace dlds\ecom;

/**
 * Common functionality for sub-components of the dlds\ecom\Component class
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package dlds\ecom
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
     * @param \dlds\ecom\Component $component
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