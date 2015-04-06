<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 23.01.14
 */

namespace dlds\ecom\basket;

use dlds\ecom\Basket;

/**
 * Interface StorageInterface
 *
 * @package dlds\ecom\basket
 */
interface StorageInterface
{
    /**
     * @param Basket $basket
     * @return mixed
     */
    public function load(Basket $basket);

    /**
     * @param \dlds\ecom\Basket $basket
     * @return void
     */
    public function save(Basket $basket);
}