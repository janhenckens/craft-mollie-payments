---
title: Plugin settings - Mollie payments
prev: false
next: false
---

# Plugin settings

In the plugin settings you define which Mollie api key you want to use. You can also move this setting to a configuration file by creating ``config/mollie-payments.php`` and adding the following content:

```php
<?php

return [
    'apiKey' => 'test_api_key'
];
```

These settings can also be set per site, but this can only be done through the configuration file.

```php
<?php

return [
    'apiKey' => [
        'siteHandleA' => 'test_key_a',
        'siteHandleB' => 'test_key_b',
    ]   
];
```


### API key per form

If you're not using different sites but you still want to use different API keys for different forms, you can set the API key in the form settings. This will override the global API key setting.

Make sure to still set the global or primary API key in the plugin settings, as you did before.
```php
<?php

return [
    'apiKey' => 'test_api_key',
    'apiKeyPerForm' =>
        [
            'firstForm' => 'test_api_key',
            'secondForm' => 'test_key_form_2' 
        ]
];


```