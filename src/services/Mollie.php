<?php

namespace studioespresso\molliepayments\services;

use Craft;
use craft\base\Component;
use craft\helpers\App;
use craft\helpers\ConfigHelper;
use craft\helpers\UrlHelper;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Customer;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\elements\Subscription;
use studioespresso\molliepayments\models\PaymentTransactionModel;
use studioespresso\molliepayments\models\SubscriberModel;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\PaymentFormRecord;
use studioespresso\molliepayments\records\SubscriberRecord;

class Mollie extends Component
{
    private MollieApiClient $mollie;

    private $baseUrl;

    public function init(): void
    {
        $this->mollie = $this->getMollieClient();
        $this->baseUrl = Craft::$app->getSites()->getCurrentSite()->getBaseUrl();
    }

    public function getMollieClient($formHandle = null)
    {
        $mollie = new MollieApiClient();
        $key = null;

        try {
            if ($formHandle && MolliePayments::getInstance()->getSettings()->apiKeyPerForm) {
                if (isset(MolliePayments::getInstance()->getSettings()->apiKeyPerForm[$formHandle])) {
                    $key = MolliePayments::getInstance()->getSettings()->apiKeyPerForm[$formHandle];
                }
            }
            if (!$key) {
                $key = App::parseEnv(ConfigHelper::localizedValue(MolliePayments::$plugin->getSettings()->apiKey));
            }
        } catch (\Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
            $key = App::parseEnv(ConfigHelper::localizedValue(MolliePayments::$plugin->getSettings()->apiKey));
        }

        $mollie->setApiKey($key);
        return $mollie;
    }

    public function generatePayment(Payment $payment, $redirect, $extraMeta = [])
    {
        $paymentForm = MolliePayments::getInstance()->forms->getFormByid($payment->formId);

        if ($paymentForm->descriptionFormat) {
            $description = Craft::$app->getView()->renderObjectTemplate($paymentForm->descriptionFormat, $payment);
        } else {
            $description = "Order #{$payment->id}";
        }

        $currentSite = Craft::$app->getSites()->getCurrentSite();
        $metaData = [
            "redirectUrl" => $redirect,
            "elementType" => Payment::class,
            "element" => $payment->uid,
            "e-mail" => $payment->email,
            "description" => $description,
            'currentSite' => $currentSite->handle,
        ];

        if ($extraMeta) {
            foreach (array_keys($metaData) as $key) {
                if (isset($extraMeta[$key])) {
                    unset($extraMeta[$key]);
                }
            }
        }

        $metaData = array_merge($metaData, $extraMeta);

        $mollie = $this->getMollieClient($paymentForm->handle);
        $authorization = $mollie->payments->create([
            "amount" => [
                "currency" => $paymentForm->currency,
                "value" => number_format($payment->amount, 2, '.', ''), // You must send the correct number of decimals, thus we enforce the use of strings
            ],
            "description" => $description,
            "redirectUrl" => UrlHelper::url("{$this->baseUrl}mollie-payments/payment/redirect", [
                "order_id" => $payment->uid,
                "redirect" => $redirect,
            ]),
            "webhookUrl" => "{$this->baseUrl}mollie-payments/payment/webhook",
            "metadata" => $metaData,
        ]);


        $transaction = new PaymentTransactionModel();
        $transaction->id = $authorization->id;
        $transaction->payment = $payment->id;
        $transaction->currency = $paymentForm->currency;
        $transaction->amount = $payment->amount;
        $transaction->redirect = $redirect;
        $transaction->status = $authorization->status;

        MolliePayments::getInstance()->transaction->save($transaction);


        return $authorization->_links->checkout->href;
    }

    public function createFirstPayment(Subscription $subscription, SubscriberModel $subscriber, PaymentFormRecord $form, $redirect)
    {
        if ($form->descriptionFormat) {
            $description = Craft::$app->getView()->renderObjectTemplate($form->descriptionFormat, $subscription);
        } else {
            $description = "Order #{$subscription->id}";
        }

        $mollie = $this->getMollieClient($form->handle);
        $response = $mollie->payments->create([
            "amount" => [
                "value" => number_format((float)$subscription->amount, 2, '.', ''),
                "currency" => $form->currency,
            ],
            "customerId" => $subscriber->customerId,
            "sequenceType" => "first",
            "description" => $description,
            "redirectUrl" => UrlHelper::url("{$this->baseUrl}mollie-payments/subscription/redirect", [
                "formUid" => $form->uid,
                "subscriptionUid" => $subscription->uid,
                "redirect" => $redirect,
            ]),
            "webhookUrl" => "{$this->baseUrl}mollie-payments/subscription/webhook",
            "metadata" => [
                "elementType" => Subscription::class,
                "formId" => $form->id,
                "createSubscription" => true, //  TODO interval true or false
            ],
        ]);

        $transaction = new PaymentTransactionModel();
        $transaction->id = $response->id;
        $transaction->payment = $subscription->id;
        $transaction->currency = $form->currency;
        $transaction->amount = $subscription->amount;
        $transaction->redirect = $redirect;
        $transaction->status = $response->status;

        MolliePayments::$plugin->transaction->save($transaction);
        return $response->_links->checkout->href;
    }

    public function createSubscription(Subscription $element)
    {
        /** @var  $customer */
        $form = MolliePayments::$plugin->forms->getFormByid($element->formId);

        if ($form->descriptionFormat) {
            $description = Craft::$app->getView()->renderObjectTemplate($form->descriptionFormat, $element);
        } else {
            $description = "Order #{$element->id}";
        }

        $subscriber = MolliePayments::$plugin->subscriber->getByEmail($element->email);

        $customer = $this->getCustomer($subscriber->customerId, $form->handle);
        $data = [
            "amount" => [
                "value" => $element->amount,
                "currency" => $form->currency,
            ],
            "interval" => $element->interval,
            "description" => $description,
            "webhookUrl" => "{$this->baseUrl}mollie-payments/subscription/webhook",

        ];

        if ($element->times) {
            $data["times"] = $element->times;
        }

        $response = $customer->createSubscription($data);
        if ($response) {
            $element->subscriptionStatus = "active";
            $element->subscriptionId = $response->id;
            Craft::$app->getElements()->saveElement($element);
        }
    }

    public function cancelSubscription(SubscriberRecord $subscriber, Subscription $subscription)
    {
        try {
            $form = MolliePayments::getInstance()->forms->getFormByid($subscription->formId);
            $customer = $this->getCustomer($subscriber->customerId, $form->handle);
            $customer->cancelSubscription($subscription->subscriptionId);
            return true;
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    public function createCustomer($email, $formHandle): Customer
    {
        $mollie = $this->getMollieClient($formHandle);
        $customer = $mollie->customers->create([
            "email" => $email,
        ]);
        return $customer;
    }

    public function deleteCustomer($id): void
    {
        try {
            //TODO
            $this->mollie->customers->delete($id);
        } catch (ApiException $e) {
            Craft::error($e->getMessage(), __METHOD__);
        }
    }

    public function getStatus($orderId, $formHandle)
    {
        $mollie = $this->getMollieClient($formHandle);
        return $mollie->payments->get($orderId);
    }

    /**
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    public function getCustomer($id, $formHandle): Customer
    {
        $mollie = $this->getMollieClient($formHandle);
        return $mollie->customers->get($id);
    }

    public function validateInterval($interval): bool
    {
        $split = explode(' ', $interval);

        if (count($split) != 2) {
            return false;
        }

        if (!is_int((int)$split[0])) {
            return false;
        }
        if (!in_array($split[1], ['months', 'weeks', 'days'])) {
            return false;
        }
        return true;
    }
}
