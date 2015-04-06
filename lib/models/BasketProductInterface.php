<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 23.01.14
 */

namespace dlds\ecom\models;

use dlds\ecom\Basket;

/**
 * All 'purchasable' objects that can be added to the basket must implement this interface.
 *
 * @package dlds\ecom\models
 */
interface BasketProductInterface extends BasketItemInterface
{
    /**
     * Returns the price of the element. This should include multiplication with any quantity attributes
     *
     * @return mixed
     */
    public function getTotalPrice();
}