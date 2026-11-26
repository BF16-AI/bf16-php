# bf16/sdk (Official PHP SDK)

Official zero-dependency PHP 8.1+ client for [bf16.ai](https://bf16.ai) — uncensored 1M-context open-weight LLM API with prompt caching and strict Zero Data Retention (ZDR).

## Installation

```bash
composer require bf16/sdk
```

## Quickstart

```php
<?php
require 'vendor/autoload.php';

use Bf16\Client;

$client = new Client(getenv('BF16_API_KEY'));
$reply = $client->chat(Client::MODEL_DEEPSEEK_V41_FLASH, [
    ['role' => 'user', 'content' => 'Review this SQL migration for unsafe string concatenation.'],
]);
echo $reply['choices'][0]['message']['content'] . PHP_EOL;
```

## Documentation & Links

- Website & Console: [bf16.ai](https://bf16.ai)
- API Documentation: [bf16.ai/docs](https://bf16.ai/docs)
- Open-Source Grants (`$50` API credits): [bf16.ai/open-source](https://bf16.ai/open-source)
