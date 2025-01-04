<?php

namespace studioespresso\molliepayments\models;

use craft\base\Model;

class Settings extends Model
{
    public string $apiKey = '';

    public string $pluginLabel = "Payments";

    public string $manageSubscriptionEmailPath = '';

    public string $manageSubscriptionEmailSubject = '';

    public string $manageSubscriptionRoute = '';

    public ?array $apiKeyPerForm = null;
}
