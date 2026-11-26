<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Client.php';

use Bf16\Bf16Exception;
use Bf16\Client;

$client = new Client(null);
$caught = false;
try {
    $client->me();
} catch (Bf16Exception $e) {
    $caught = str_contains($e->getMessage(), 'Missing BF16_API_KEY');
}

if (!$caught) {
    fwrite(STDERR, "Expected Bf16Exception when API key is missing\n");
    exit(1);
}

echo "OK: bf16/sdk PHP assertions passed.\n";
