<?php

declare(strict_types=1);

namespace Bf16;

use RuntimeException;

final class Bf16Exception extends RuntimeException
{
    public readonly ?int $statusCode;

    public function __construct(string $message, ?int $statusCode = null)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
    }
}

final class Client
{
    public const DEFAULT_BASE_URL = 'https://api.bf16.ai/v1';
    public const ANTHROPIC_BASE_URL = 'https://api.bf16.ai';

    public const MODEL_DEEPSEEK_V41_FLASH = 'deepseek/deepseek-v4.1-flash-uncensored';
    public const MODEL_DEEPSEEK_V41_FLASH_FAST = 'deepseek/deepseek-v4.1-flash-uncensored-fast';
    public const MODEL_GLM_53_FLASH = 'zai-org/glm-5.3-flash-uncensored';
    public const MODEL_GLM_53 = 'zai-org/glm-5.3-uncensored';
    public const MODEL_KIMI_K31_PRO = 'moonshotai/kimi-k3.1-pro-uncensored';
    public const MODEL_AUTO = 'bf16/auto';

    private ?string $apiKey;
    private string $baseUrl;
    private int $timeoutSeconds;

    public function __construct(
        ?string $apiKey = null,
        string $baseUrl = self::DEFAULT_BASE_URL,
        int $timeoutSeconds = 120
    ) {
        $resolved = $apiKey ?? (getenv('BF16_API_KEY') ?: null);
        $this->apiKey = $resolved !== '' ? $resolved : null;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeoutSeconds = $timeoutSeconds;
    }

    /** @return array<string, mixed> */
    public function models(): array
    {
        return $this->requestJson('GET', '/models', null, false);
    }

    /** @return array<string, mixed> */
    public function me(): array
    {
        return $this->requestJson('GET', '/me', null, true);
    }

    /**
     * @param array<int, array<string, mixed>> $messages
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function chat(string $model, array $messages, array $options = []): array
    {
        $payload = array_merge($options, [
            'model' => $model,
            'messages' => $messages,
        ]);
        return $this->requestJson('POST', '/chat/completions', $payload, true);
    }

    /**
     * @param array<string, mixed>|null $payload
     * @return array<string, mixed>
     */
    private function requestJson(string $method, string $path, ?array $payload, bool $requireAuth): array
    {
        if ($requireAuth && ($this->apiKey === null || $this->apiKey === '')) {
            throw new Bf16Exception('Missing BF16_API_KEY');
        }

        $ch = curl_init($this->baseUrl . $path);
        if ($ch === false) {
            throw new Bf16Exception('Unable to initialize cURL handle');
        }

        $headers = [
            'Accept: application/json',
            'User-Agent: bf16-php/0.1.0',
        ];
        if ($this->apiKey !== null && $this->apiKey !== '') {
            $headers[] = 'Authorization: Bearer ' . $this->apiKey;
        }
        if ($payload !== null) {
            $jsonBody = json_encode($payload, JSON_THROW_ON_ERROR);
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
        }

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
        ]);

        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if (!is_string($raw)) {
            throw new Bf16Exception('cURL request failed: ' . $err);
        }

        $decoded = json_decode($raw, true);
        $data = is_array($decoded) ? $decoded : ['raw' => $raw];

        if ($status >= 400) {
            throw new Bf16Exception("bf16.ai HTTP {$status}: {$raw}", $status);
        }

        return $data;
    }
}
