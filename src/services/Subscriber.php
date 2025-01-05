<?php

namespace studioespresso\molliepayments\services;

use Craft;
use craft\base\Component;
use Mollie\Api\Resources\Customer;
use studioespresso\molliepayments\elements\Subscription as SubscriptionElement;
use studioespresso\molliepayments\models\SubscriberModel;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\SubscriberRecord;

class Subscriber extends Component
{
    public function getStatus($id)
    {
        $element = SubscriptionElement::findOne(['id' => $id]);
        if ($element) {
            return $element->subscriptionStatus;
        }
    }

    public function getByEmail($email): SubscriberModel
    {
        $record = SubscriberRecord::findOne(['email' => $email]);
        $model = new SubscriberModel();
        $model->setAttributes($record->getAttributes());
        return $model;
    }

    public function getOrCreateSubscriberByEmail($email, $formHandle): SubscriberModel
    {
        $record = SubscriberRecord::findOne(['email' => $email]);
        if ($record) {
            $model = new SubscriberModel();
            $model->setAttributes($record->getAttributes());
            return $model;
        }

        /** @var Customer $customer */
        $customer = MolliePayments::getInstance()->mollie->createCustomer($email, $formHandle);
        $model = new SubscriberModel();
        if (Craft::$app->getUser()->getIdentity()) {
            $model->userId = Craft::$app->getUser()->getIdentity()->id;
        }
        $model->customerId = $customer->id;
        $model->email = $customer->email;
        $model->locale = $customer->locale ?? '';
        $model->metadata = $customer->metadata ?? '';
        $model->links = $customer->_links;

        $this->save($model);
        return $model;
    }

    public function save(SubscriberModel $model)
    {
        if ($model->id) {
            $record = SubscriberRecord::findOne(['id' => $model->id]);
        } else {
            $record = new SubscriberRecord();
        }
        $record->customerId = $model->customerId;
        $record->userId = $model->userId;
        $record->email = $model->email;
        $record->locale = $model->locale ?? '';
        $record->metadata = $model->metadata ?? '';
        $record->links = $model->links;
        return $record->save();
    }

    public function deleteById($id): void
    {
        $record = SubscriberRecord::findOne(['customerId' => $id]);
        if ($record) {
            $record->delete();
        }
    }

    public function getAllSubscribers()
    {
        return SubscriberRecord::find()->all();
    }
}
