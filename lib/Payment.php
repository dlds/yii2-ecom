<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 24.01.14
 */

namespace opus\ecom;

use opus\ecom\models\OrderInterface;
use opus\ecom\widgets\PaymentButtons;
use opus\payment\PaymentHandlerBase;
use opus\payment\services\payment\Response;
use opus\payment\services\payment\Transaction;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This class adds some convenience functionality to the \opus\payment component
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package opus\ecom
 *
 * @property \opus\payment\services\Payment $service
 */
class Payment extends PaymentHandlerBase
{
    use SubComponentTrait;

    /**
     * PaymentHandler component configuration
     *
     * @var array
     */
    public $params;

    /**
     * Class of the widget for drawing the payment buttons section (all buttons)
     *
     * @var string
     */
    public $widgetClass = 'opus\ecom\widgets\PaymentButtons';
    /**
     * @var array Shorthand configuration param for return URL. Will override $this->params['common']['returnRoute']
     */
    public $bankReturnRoute;
    /**
     * @var array Shorthand configuration param for return URL. Will override $this->params['common']['returnRoute']
     */
    public $bankCancelRoute;
    /**
     * @var array Shorthand configuration param for adapters. Will override $this->params['adapters']
     */
    public $adapterConfig;
    /**
     * @var \opus\payment\services\Payment
     */
    private $service;

    /**
     * @inheritdoc
     */
    public function getConfiguration()
    {
        $params = $this->params;
        isset($this->bankReturnRoute) && $params['common']['returnRoute'] = $this->bankReturnRoute;
        isset($this->bankCancelRoute) && $params['common']['cancelRoute'] = $this->bankCancelRoute;
        isset($this->adapterConfig) && $params['adapters'] = $this->adapterConfig;

        return $params;
    }

    /**
     * This is a convenience method for generating a payment widget that generates all payment forms (using self::$widgetClass)
     *
     * @param OrderInterface $order
     * @param array $widgetOptions
     * @return PaymentButtons
     */
    public function createWidget(OrderInterface $order, $widgetOptions = [])
    {
        $transaction = $this->createTransaction($order);

        /** @var $widget PaymentButtons */
        $widgetConfig = ArrayHelper::merge((is_string($this->widgetClass) ? ['class' => $this->widgetClass] : $this->widgetClass),
            $widgetOptions,
            [
                'transaction' => $transaction,
                'service' => $this->getService()
            ]);

        $widget = \Yii::createObject($widgetConfig);
        return $widget;
    }

    /**
     * Creates a new transaction based on an order. Also calls opus\ecom\Component::finalizeTransaction
     *
     * @param OrderInterface $order
     * @return Transaction
     */
    public function createTransaction(OrderInterface $order)
    {
        $transaction = $this
            ->createService(self::SERVICE_PAYMENT)
            ->createTransaction($order->getPKValue(), $order->getTransactionSum());

        $this->component->finalizeTransaction($order, $transaction);
        return $transaction;
    }

    /**
     * @param array $request
     * @param $arClassName
     * @return OrderInterface|ActiveRecord
     * @throws \InvalidArgumentException
     */
    public function handleResponse(array $request, $arClassName)
    {
        /** @var $response Response */
        $response = $this->getService()->handleResponse($request); // throws exceptions on error
        $transaction = $response->getTransaction();

        if ($elementId = $transaction->getTransactionId(null)) {
            /** @var $arClassName ActiveRecord */
            $orderModel = $arClassName::findOne($elementId);
            if ($orderModel instanceof OrderInterface) {
                return $orderModel->bankReturn($response);
            }
        }
        throw new \InvalidArgumentException('Invalid data, order not found');
    }

    /**
     * Overridden to provide one directory for all key files
     *
     * @param string $relativePath
     * @return string
     */
    public function createFilePath($relativePath)
    {
        return $this->component->createKeyFilePath($relativePath);
    }

    /**
     * @return \opus\payment\services\Payment
     */
    protected function getService()
    {
        if (!isset($this->service)) {
            $this->service = $this->createService(self::SERVICE_PAYMENT);
        }
        return $this->service;
    }
}
