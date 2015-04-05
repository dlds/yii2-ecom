<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 23.01.14
 */

namespace dlds\shop\basket;

use dlds\shop\Basket;

/**
 * Interface StorageInterface
 *
 * @package dlds\shop\basket
 */
interface StorageInterface
{
    /**
     * @param Basket $basket
     * @return mixed
     */
    public function load(Basket $basket);

    /**
     * @param \dlds\shop\Basket $basket
     * @return void
     */
    public function save(Basket $basket);
}