<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 24.01.14
 */

namespace dlds\shop\widgets;

use dlds\shop\assets\PayAssetBundle;
use dlds\shop\models\OrderInterface;
use dlds\shop\SubComponentTrait;
use opus\payment\services\payment\Form;
use opus\payment\services\payment\Transaction;
use opus\payment\services\Payment;
use opus\payment\widgets\PaymentWidget;
use yii\helpers\Html;

/**
 * Class PaymentWidget
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package dlds\shop\widgets
 *
 * @property OrderInterface order
 * @property Payment service
 */
class PaymentButtons extends PaymentWidget
{
    /**
     * @var OrderInterface
     */
    protected $order;
    /**
     * @var Payment
     */
    protected $service;
    /**
     * @var Transaction
     */
    protected $transaction;
    /**
     * @var PayAssetBundle
     */
    protected $asset;

    public function init()
    {
        $this->asset = PayAssetBundle::register($this->view);
        parent::init();
    }

    public function run()
    {
        $this->forms = $this->service->generateForms($this->transaction);
        parent::run();
    }

    /**
     * @param \dlds\shop\models\OrderInterface $order
     * @return PaymentButtons
     */
    public function setOrder(OrderInterface $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param \opus\payment\services\Payment $service
     * @return PaymentButtons
     */
    public function setService(Payment $service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * @param \opus\payment\services\payment\Transaction $transaction
     * @return PaymentButtons
     */
    public function setTransaction(Transaction $transaction)
    {
        $this->transaction = $transaction;
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function generateSubmit(Form $form)
    {
        $image = $this->asset->baseUrl . '/banks/' . strtolower($form->getProviderTag()) . '.jpg';
        return Html::input('image', $form->getProviderName(), null, ['src' => $image]);
    }
}