<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 23.01.14
 */

namespace dlds\shop\widgets;

use dlds\shop\Basket;
use yii\data\ArrayDataProvider;
use Yii;

/**
 * Class BasketGridView. Provides the default data provider with no pagination and all basket models
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package dlds\shop\widgets
 */
class BasketGridView extends GridView
{
    /**
     * @var Basket
     */
    public $basket;

    /**
     * @var string Only items of that type will be rendered. Defaults to Basket::ITEM_PRODUCT
     */
    public $itemType = Basket::ITEM_PRODUCT;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!isset($this->dataProvider)) {
            $this->dataProvider = new ArrayDataProvider([
                'allModels' => $this->basket->getItems($this->itemType),
                'pagination' => false,
            ]);
        }
        parent::init();
    }
}