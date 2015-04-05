<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 24.01.14
 */

namespace dlds\shop\models;

use dlds\shop\Basket;
use opus\payment\services\payment\Response;

/**
 * Any object that represents an order and is to be used in conjunction with the Basket object, should implement this interface.
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package dlds\shop\models
 */
interface OrderInterface
{
    /**
     * This method should load the contents of the basket and save the order with all its item in the database
     *
     * @param Basket $basket
     * @return boolean
     */
    public function saveFromBasket(Basket $basket);

    /**
     * Returns the total money due for this order. Should return a value of type double
     *
     * @return float
     */
    public function getTransactionSum();

    /**
     * Returns the primary key for the ActiveRecord item
     *
     * @return string
     */
    public function getPKValue();

    /**
     * This action is called when user returns from the bank.
     *
     * @param Response $response
     * @return OrderInterface
     */
    public function bankReturn(Response $response);
} 
