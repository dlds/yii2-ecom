<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 4.02.14
 */

namespace dlds\ecom\models;

/**
 * All objects that can be added to the basket must implement this interface
 *
 * @package dlds\ecom\models
 */
interface BasketItemInterface extends \Serializable {

    /**
     * Returns the label for the basket item (displayed in basket etc)
     *
     * @return string
     */
    public function getLabel();

    /**
     * Checks if the item is valid
     * Errors are viewable through $this->getErrors();
     *
     * @return bool
     */
    public function validateItem();

    /**
     * Returns all errors for current model (after validating)
     *
     * @return string[]
     */
    public function getItemErrors();

    /**
     * Sets the named attribute value.
     * @param string $name the attribute name.
     * @param mixed $value the attribute value.
     * @see hasAttribute()
     */
    public function setAttribute($name, $value);

    /**
     * Returns a value indicating whether the record has an attribute with the specified name.
     * @param string $name the name of the attribute
     * @return boolean whether the record has an attribute with the specified name.
     */
    public function hasAttribute($name);

    /**
     * Returns the primary key for the ActiveRecord item
     *
     * @return string
     */
    public function getPKValue();

    /**
     * Retrieves Basket Item type
     *
     * @return string
     */
    public function getType();

    /**
     * Adds quantity of product (if quantity is < 0 it will subtract the quantity)
     * @param int $quantity
     */
    public function addQuantity($quantity);

    /**
     * Gets quantity of product
     * @return int quantity of product
     */
    public function getQuantity();
}