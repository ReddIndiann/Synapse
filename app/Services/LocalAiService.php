<?php

namespace App\Services;

use App\Services\Ai\Providers\OpenAiCompatibleNlpProvider;

/**
 * Backward-compatible wrapper around the local OpenAI-compatible provider.
 */
class LocalAiService
{
    protected OpenAiCompatibleNlpProvider $provider;

    public function __construct()
    {
        $this->provider = new OpenAiCompatibleNlpProvider('local');
    }

    public function parse(string $prompt, int $userId): ?array
    {
        return $this->provider->parse($prompt, $userId);
    }

    public function isAvailable(): bool
    {
        return $this->provider->isAvailable();
    }
}
