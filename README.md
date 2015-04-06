E-commerce extension for Yii2
=========
Provides support for basic e-commerce operations as a wrapper for [dlds/yii2-payment](https://github.com/dlds/yii2-payment). 
For usage examples see [dlds/yii2-app-ecom](https://github.com/dlds/yii2-app-ecom) - a sample application using this extension. This guide assumes that you are familiar with implementations of Estonian bank payments (see [pangalink.net](http://pangalink.net))

Main features:
- Extensible code, almost everything can be customized
- Integration with ActiveRecord objects (orders, products)
- Shopping basket functionality (support for multiple storage methods)
- Support for payment adapters (currently Estonian banks are implemented)
- Support for discounts
- Basic widgets for displaying shopping basket contents, payment forms, lists

Installation
------------
You can install this package using composer. In your `composer.json` add the following required package: 
```json
{
	"require": {
			"dlds/yii2-ecom": "*"
	}
}
```
Technical overview
------------------
The package lives under `\dlds\shop` namespace and consists of 3 sub-components (these can be overridden in configuration and are directly accessible as properties of the main component).

1. **basket** - provides shopping basket functionality
2. **payment** - provides functionality for receiving payments
3. **formatter** - helper class used in grid views

Most classes and subclasses can be overridden in the configuration.

Configuration
-------------
After the installation you  will probably want to:

1. Override and extend the main class to provide some custom functionality
2. Register this class as application component
3. Configure the bank payment parameters and the return URL

You can use something similar in the application configuration to achieve this:
```php
$config = [
	'components' => [
        'ecom' => [
            'class' => 'app\components\MyEcomComponent',
            'payment' => [
                'class' => 'dlds\shop\Payment',
                'bankReturnRoute' => 'bankret', // use url alias to shorten the return url
                'adapterConfig' => \yii\helpers\ArrayHelper::merge(require 'banks-default.php', require 'banks-local.php')
            ],
        ],
	],
];
```
And define the class `MyEcomComponent` like this:
```php
namespace app\components;

class MyEcomComponent extends \dlds\shop\Component
{
  // override methods here to customize
}

```
You can use `banks-default.php` to store general bank configuration (e.g. which banks are used) and define the more
specific access parameters in `banks-local.php`, which is environment-specific and is not held in CVS. You can download
the example files from [here](http://demo.opus.ee/projects/ecom/sample-keys.zip).

Usage
--------------
The following examples assume that you have registered `dlds\shop\Component` (or its child class) as an application component under `Yii::$app->ecom`.

### Using ActiveRecord integration
ActiveRecord integration makes it very easy to add items to shopping basket and to use the basket to create orders and
payment forms. Currently there are three kinds of AR integration available.

1. Implement `dlds\shop\models\BasketProductInterface` in your AR model class to add support for products and services
2. Implement `dlds\shop\models\BasketDiscountInterface` to add support for discounts
3. Implement `dlds\shop\models\OrderInterface` to add support for orders


### Using the shopping basket
Operations with the shopping basket are very straightforward when using a models that implement one of the two basket interfaces.
The basket object can be accessed under `\Yii::$app->ecom->basket` and can be overridden in configuration if you need to customize it.
```php
// access the basket from "basket" subcomponent
$basket = \Yii::$app->ecom->basket;

// Product is an AR model implementing BasketProductInterface
$product = Product::find(1);

// add an item to the basket
$basket->add($product);

// add a discount object to the basket. AR model is implementing BasketDiscountInterface
$basket->add(Discount::find(1));

// returns the sum of all basket item prices
$sum = $basket->getTotalDue();

// returns the sum of all 'vat' attributes (or return values of getVat()) from all models in the basket.
$totalVat = $basket->getAttributeTotal('vat');

// clear the basket
$basket->clear();

// render the contents of the basket with default parameters
echo \dlds\shop\widgets\BasketGridView::widget([
    'basket' => $basket,
]);
```

#### Items in the basket
Products/items that are added to the basket are serialized/unserialized when saving and loading data from basket storage.
If you are using Active Record models as products/discounts, make sure that you are omitting any unnecessary references from
the serialized data to keep it compact.

```php
// get all items from the basket
$items = $basket->getItems();

// get only products
$items = $basket->getItems(Basket::ITEM_PRODUCT);

// loop through basket items
foreach ($items as $item) {
	// access any attribute/method from the model
	var_dump($item->getAttributes());

	// remove an item from the basket by its ID
	$basket->remove($item->uniqueId)
}
```

### Creating orders
The basket object can easily be converted into orders (AR objects that implement the `OrderInterface`).
```php
$order = new Order([
	'user_id' = \Yii::$app->user->id,
	'status' => 'new',
]);
\Yii::$app->ecom->basket->createOrder($order);
```
This calls the `saveFromBasket` method from your Order class, which could look something like this.
```php
public function saveFromBasket(Basket $basket)
{
    $transaction = $this->getDb()->beginTransaction();
    try
    {
        $this->due_amount = $basket->getTotalDue(false);
        if (!$this->save()) {
            throw new \RuntimeException('Could not save order model');
        }

        foreach ($basket->getItems() as $item) {
            // create and save "order line" objects looking up necessary attributes from $item
        }
        $transaction->commit();
    }
    catch (\Exception $exception)
    {
        $transaction->rollback();
        throw $exception;
    }
}
```


### Using payments
#### Generating payment forms
If you have saved your Order objects, you can use the included widget `dlds\shop\widgets\PaymentButtons` to render all the bank forms included in your configuration. You can provide your own widget class in the configuration if you need customization (override `widgetClass` under `payment` sub-component). There is a shorthand method for generating the widget with correct parameters:
```php
// generate FORM tags for every bank with hidden inputs and bank logos as submit images
\Yii::$app->ecom
	->payment
	->createWidget($order)
	->run();
```
#### Receiving requests from banks
After payment the user is directed back to the application to a URL specified in your configuration by `bankReturnRoute` under `payment` sub-component. In your controller action you can pass the request to the component like this:
```php
public function actionBankReturn()
{
	// pass in the request data and the name of the class you want to use as order object
	$model = \Yii::$app->ecom->payment->handleResponse($_REQUEST, Order::className());

	$this->redirect(['order/view', 'orderId' => $model->id]);
}
```
The above code loads the right Order from the database and calls the `bankReturn` method from your Order class. You are responsible for saving the order and logging necessary data. 
```php
public function bankReturn(Response $response)
{
    $this->status = $response->isSuccessful() ? 'paid' : 'error';
    $this->save();

    \Yii::$app->log->log('Returned from bank: ' . $response->__toString(), Logger::LEVEL_INFO);

    return $this;
}
```
Advanced examples
-------------------

### Storing basket data in database
There are two built-in adapters for storing basket data: session storage (default) and database storage. You can also write your own storage classes and define them in configuration under `basket` component:
```php
'ecom' => [
    'class' => 'app\components\MyEcomComponent',
    'basket' => [
        'class' => 'dlds\shop\Basket',
        'storage' => [
            'class' => 'dlds\shop\basket\storage\Database',
            'table' => 'eco_basket',
        ]
    ],
```
In addition to table name, you can override both field names (one for user identifier and another for session data). If `Database::$userComponent` is set (for example to `user`), then the adapter will try to ask the user identifier via `\Yii::$app->user->getId()` method. If this fails or the component name is not specified, session ID will be used to identify the user. The session table should have at least two fields and look similar to this:
```SQL
CREATE TABLE `eco_basket` (
	`session_id` varchar(255) NOT NULL,
	`basket_data` blob NOT NULL,
	PRIMARY KEY (`session_id`)
) ENGINE=InnoDB;
```
### Using discounts
To add discounts to the basket/order, simply add an object implementing `BasketDiscountInterface` to the basket and specify
the discount logic in `applyToBasket` method.

```php
class MyDiscount implements BasketDiscountInterface
{
    public function applyToBasket(Basket $basket, &$basketTotalSum)
    {
        // subtract a value
        $basketTotalSum -= 10;

        // or add a X percent discount
        $basketTotalSum *= 0.7;
    }
}

\Yii::$app->ecom->basket->add(new MyDiscount);
```