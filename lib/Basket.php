<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 23.01.14
 */

namespace dlds\ecom;

use dlds\ecom\models\BasketDiscountInterface;
use dlds\ecom\models\BasketItemInterface;
use dlds\ecom\models\OrderInterface;
use yii\base\InvalidParamException;
use yii\web\Session;

/**
 * Provides basic basket functionality (adding, removing, clearing, listing items). You can extend this class and
 * override it in the application configuration to extend/customize the functionality
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package dlds\ecom
 *
 * @property int $count
 * @property Session $session
 */
class Basket extends Component {

    const ITEM_PRODUCT = '\dlds\ecom\models\BasketProductInterface';
    const ITEM_DISCOUNT = '\dlds\ecom\models\BasketDiscountInterface';

    use SubComponentTrait;
    /**
     * @var BasketItemInterface[]
     */
    protected $items;

    /**
     * @var Session
     */
    private $session;

    /**
     * Override this to provide custom (e.g. database) storage for basket data
     *
     * @var string|\dlds\ecom\basket\StorageInterface
     */
    private $storage = 'dlds\ecom\basket\storage\Session';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->clear(false);
        $this->setStorage(\Yii::createObject($this->storage));
        $this->items = $this->storage->load($this);
    }

    /**
     * Delete all items from the basket
     *
     * @param bool $save
     * @return $this
     */
    public function clear($save = true)
    {
        $this->items = [];

        $save && $this->storage->save($this);
        return $this;
    }

    /**
     * Setter for the storage component
     *
     * @param \dlds\ecom\basket\StorageInterface|string $storage
     * @return Basket
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * Create an order from the basket contents
     *
     * @param OrderInterface $order
     * @param bool $clear
     * @return \dlds\ecom\models\OrderInterface
     * @throws \Exception
     */
    public function createOrder(OrderInterface $order, $clear = true)
    {
        try
        {
            $order->saveFromBasket($this);
            $clear && $this->clear();
            return $order;
        }
        catch (\Exception $exception)
        {
            throw $exception;
        }
    }

    /**
     * Add an item to the basket
     *
     * @param models\BasketItemInterface $element
     * @param bool $save
     * @return $this
     */
    public function add(BasketItemInterface $element, $save = true)
    {
        $this->addItem($element);
        $save && $this->storage->save($this);
        return $this;
    }

    /**
     * @param \dlds\ecom\models\BasketItemInterface $item
     * @internal param $quantity
     */
    protected function addItem(BasketItemInterface $item)
    {
        $id = $this->getItemId($item);

        if (isset($this->items[$id]))
        {
            $this->items[$id]->addQuantity($item->getQuantity());
        }
        else
        {
            $this->items[$id] = $item;
        }

        if ($this->items[$id]->getQuantity() < 1)
        {
            $this->remove($item);
        }
    }

    /**
     * Get an item from the basket
     *
     * @param models\BasketItemInterface $element
     * @return $this
     */
    public function get(BasketItemInterface $item)
    {
        $id = $this->getItemId($item);

        return isset($this->items[$id]) ? $this->items[$id] : null;
    }

    /**
     * Removes an item from the basket
     *
     * @param string $uniqueId
     * @param bool $save
     * @throws \yii\base\InvalidParamException
     * @return $this
     */
    public function remove(BasketItemInterface $item, $save = true)
    {
        $id = $this->getItemId($item);

        if (!isset($this->items[$id]))
        {
            throw new InvalidParamException('Item not found');
        }
        unset($this->items[$id]);

        $save && $this->storage->save($this);
        return $this;
    }

    /**
     * @param string $itemType If specified, only items of that type will be counted
     * @return int
     */
    public function getCount($itemType = null)
    {
        $items = $this->getItems($itemType);

        $count = 0;

        foreach ($items as $item)
        {
            $count += $item->basketQuantity;
        }

        return $count;
    }

    /**
     * Returns all items of a given type from the basket
     *
     * @param string $itemType One of self::ITEM_ constants
     * @return BasketItemInterface[]
     */
    public function getItems($itemType = null)
    {
        $items = $this->items;
        if (!is_null($itemType))
        {
            $items = array_filter($items, function ($item) use ($itemType) {
                /** @var $item BasketItemInterface */
                return is_subclass_of($item, $itemType);
            });
        }

        return $items;
    }

    /**
     * Returns the difference between finalized basket price and the sum of all items. If you haven't done any custom
     * price modification, this most likely returns 0
     *
     * @param bool $format
     * @return int|string
     */
    public function getTotalDiscounts($format = true)
    {
        $sum = $this->getAttributeTotal('totalPrice', self::ITEM_PRODUCT);
        $totalDue = $this->getTotalDue(false);

        $sum = $sum - $totalDue;
        return $format ? $this->component->formatter->asPrice($sum) : $sum;
    }

    /**
     * Finds all items of type $itemType, sums the values of $attribute of all models and returns the sum.
     *
     * @param string $attribute
     * @param string|null $itemType
     * @return int|float
     */
    public function getAttributeTotal($attribute, $itemType = null)
    {
        $sum = 0;
        foreach ($this->getItems($itemType) as $model)
        {
            $sum += $model->{$attribute};
        }
        return $sum;
    }

    /**
     * Returns the total price of all items and applies discounts and custom functionality to the sum (calls finalizeBasketPrice).
     *
     * @param bool $format
     * @return int|string
     */
    public function getTotalDue($format = true)
    {
        // get item total sum
        $sum = $this->getAttributeTotal('totalPrice', self::ITEM_PRODUCT);

        // apply discounts
        foreach ($this->getItems(self::ITEM_DISCOUNT) as $discount)
        {
            /** @var $discount BasketDiscountInterface */
            $discount->applyToBasket($this, $sum);
        }

        $sum = $this->component->finalizeBasketPrice($sum, $this);
        $sum = max(0, $sum);
        return $format ? $this->component->formatter->asPrice($sum) : $sum;
    }

    /**
     * @param \yii\web\Session $session
     * @return Basket
     */
    public function setSession(Session $session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @return \yii\web\Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return \dlds\ecom\basket\StorageInterface|string
     */
    protected function getStorage()
    {
        return $this->storage;
    }

    /**
     * Retrieves item id
     */
    protected function getItemId(BasketItemInterface $item)
    {
        return md5(sprintf('%s_%s', $item->getType(), $item->getPKValue()));
    }

    /**
     * @param string $uniqueId
     * @param string $attribute
     * @param string $value
     * @return bool
     */
    public function update($uniqueId, $attribute, $value)
    {
        if (!isset($this->items[$uniqueId]) || !$this->items[$uniqueId]->hasAttribute($attribute))
        {
            return false;
        }

        $this->items[$uniqueId]->setAttribute($attribute, $value);
    }
}