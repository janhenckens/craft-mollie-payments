<?php
/**
 * Mollie Payments plugin for Craft CMS 3.x
 *
 * Easily accept payment with Mollie Payments
 *
 * @link      https://studioespresso.co
 * @copyright Copyright (c) 2019 Studio Espresso
 */

namespace studioespresso\molliepayments\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\enums\Color;
use craft\helpers\Cp;
use craft\helpers\UrlHelper;
use Mollie\Api\Resources\Customer;
use studioespresso\molliepayments\elements\db\SubscriptionQuery;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\SubscriptionRecord;

/**
 * @author    Studio Espresso
 * @package   MolliePayments
 * @since     1.0.0
 */
class Subscription extends Element
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $email;
    public $customerId;
    public $amount = 0;
    public $subscriptionId;
    public $subscriptionStatus;
    public $interval;
    public $times = null;
    public $formId;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('mollie-payments', 'Subscription');
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string
    {
        return Craft::t('mollie-payments', 'Subscriptions');
    }

    /**
     * @inheritDoc
     */
    public static function lowerDisplayName(): string
    {
        return Craft::t('mollie-payments', 'subscription');
    }

    /**
     * @inheritDoc
     */
    public static function pluralLowerDisplayName(): string
    {
        return Craft::t('mollie-payments', 'subscriptions');
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return false;
    }

    public static function hasStatuses(): bool
    {
        return true;
    }

    public function getUiLabel(): string
    {
        return $this->email ?: Craft::t("mollie-payments", 'Cart') . ' ' . $this->id;
    }


    public static function statuses(): array
    {
        return [
            'cart' => ['label' => Craft::t('mollie-payments', 'In Cart'), 'color' => Color::Gray],
            'pending' => ['label' => Craft::t('mollie-payments', 'Pending'), 'color' => Color::Orange],
            'active' => ['label' => Craft::t('mollie-payments', 'Active'), 'color' => Color::Green],
            'canceled' => ['label' => Craft::t('mollie-payments', 'Canceled'), 'color' => Color::Red],
            'expired' => ['label' => Craft::t('mollie-payments', 'Expired'), 'color' => Color::Red],
            'refunded' => ['label' => Craft::t('mollie-payments', 'Refunded'), 'color' => Color::Gray],
        ];
    }

    public function getStatus(): ?string
    {
        return $this->subscriptionStatus;
    }

    public function getCpStatusItem()
    {
        return Cp::componentStatusLabelHtml($this);
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return false;
    }

    public function getForm()
    {
        return MolliePayments::getInstance()->forms->getFormByid($this->formId);
    }

    public function getTransaction()
    {
        return MolliePayments::getInstance()->transaction->getTransactionbyPayment($this->id);
    }

    /**
     *
     * @return ElementQueryInterface The newly created [[ElementQueryInterface]] instance.
     */
    public static function find(): ElementQueryInterface
    {
        return new SubscriptionQuery(static::class);
    }


    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources[] = [
            'key' => '*',
            'label' => Craft::t('app', 'All'),
        ];
        $forms = MolliePayments::getInstance()->forms->getAllFormsByType(PaymentFormModel::TYPE_SUBSCRIPTION);

        foreach ($forms as $form) {
            $sources[] = [
                'key' => 'form:' . $form['handle'],
                'label' => $form['title'],
                'criteria' => [
                    'formId' => $form['id'],
                ],
                'defaultSort' => ['dateCreated', 'desc'],
            ];
        }

        return $sources;
    }

    protected static function defineActions(string $source = null): array
    {
        return [];
    }

    protected static function defineSortOptions(): array
    {
        return [
            'dateCreated' => \Craft::t('app', 'Date created'),
        ];
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'email' => Craft::t('mollie-payments', 'Email'),
            'amount' => Craft::t('mollie-payments', 'Amount'),
            'interval' => Craft::t('mollie-payments', 'Interval'),
            'status' => Craft::t('mollie-payments', 'Status'),
            'dateCreated' => Craft::t('mollie-payments', 'Date Created'),
        ];
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['email'];
    }


    public function getCustomer(): Customer|null
    {
        if (!$this->customerId) {
            return null;
        }
        $form = $this->getForm();
        return MolliePayments::getInstance()->mollie->getCustomer($this->customerId, $form->handle);
    }

    // Public Methods
    // =========================================================================
    public function getCpEditUrl(): ?string
    {
        return UrlHelper::cpUrl("mollie-payments/subscriptions/" . $this->uid);
    }

    /**
     * @inheritDoc
     */
    public function canView(User $user): bool
    {
        if ($user->can("accessPlugin-mollie-payments")) {
            return true;
        }
        return false;
    }

    public function canDelete(User $user): bool
    {
        if ($user->can("accessPlugin-mollie-payments")) {
            return true;
        }
        return false;
    }


    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['email', 'interval', 'times'], 'string'];
        if ($this->getScenario() != Element::SCENARIO_ESSENTIALS) {
            $rules[] = [['email', 'amount', 'interval'], 'required'];
        }
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getIsEditable(): bool
    {
        return true;
    }


    // Events
    // -------------------------------------------------------------------------
    /**
     * @inheritdoc
     */
    public function beforeSave(bool $isNew): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew): void
    {
        if ($isNew) {
            \Craft::$app->db->createCommand()
                ->insert(SubscriptionRecord::tableName(), [
                    'id' => $this->id,
                    'email' => $this->email,
                    'subscriptionStatus' => $this->subscriptionStatus,
                    'customerId' => $this->customerId,
                    'interval' => $this->interval,
                    'times' => $this->times,
                    'amount' => $this->amount,
                    'formId' => $this->formId,
                ])
                ->execute();
        } else {
            \Craft::$app->db->createCommand()
                ->update(SubscriptionRecord::tableName(), [
                    'email' => $this->email,
                    'subscriptionId' => $this->subscriptionId,
                    'customerId' => $this->customerId,
                    'subscriptionStatus' => $this->subscriptionStatus,
                ], ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }

    public function afterDelete(): void
    {
        \Craft::$app->db->createCommand()
            ->delete(SubscriptionRecord::tableName(), ['id' => $this->id])
            ->execute();
    }
}
