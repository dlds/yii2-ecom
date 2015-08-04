<?php
/**
 * @copyright Copyright (c) 2014 Digital Deals s.r.o.
 * @license http://www.digitaldeals.cz/license/
 */

namespace dlds\ecom\widgets;

use Yii;
use yii\helpers\Html;
use dlds\ecom\Basket;

/**
 * Class BasketSummary. Provides the default data provider with no pagination and all basket models
 *
 * @author Jiri Svoboda <jiri.svoboda@dlds.cz>
 * @package dlds\ecom\widgets
 */
class BasketSummary extends \yii\base\Widget {

    /**
     * @var Basket
     */
    public $basket;

    /**
     * @var string basket icon name
     */
    public $basketIcon = false;

    /**
     * @var string wrapper tag
     */
    public $basketLink = false;

    /**
     * @var array wrapper html options
     */
    public $options = [];

    /**
     * @var string basket summaryText template
     */
    public $summaryText;

    /**
     * @var string empty basket warning message
     */
    public $emptyText = 'Your basket is empty';

    /**
     * @var string Only items of that type will be rendered. Defaults to Basket::ITEM_PRODUCT
     */
    public $itemType = Basket::ITEM_PRODUCT;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        if ($this->basketLink)
        {
            $this->options['href'] = $this->basketLink;
            $html = Html::beginTag('a', $this->options);
        }
        else
        {
            $html = Html::beginTag('div', $this->options);
        }

        if ($this->basketIcon)
        {
            $html .= Html::tag('i', '', ['class' => $this->basketIcon]);
        }

        $count = $this->basket->getCount($this->itemType);

        if ($count)
        {
            $html .= call_user_func($this->summaryText, $count, $this->basket->getTotalDue());
        }
        else
        {
            $html .= Html::tag('span', $this->emptyText);
        }

        if ($this->basketLink)
        {
            $html .= Html::endTag('a');
        }
        else
        {
            $html .= Html::endTag('div');
        }

        echo $html;
    }
}