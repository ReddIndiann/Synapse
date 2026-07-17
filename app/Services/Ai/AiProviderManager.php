<?php

namespace App\Services\Ai;

use App\Contracts\AiNlpProvider;
use App\Services\Ai\Providers\AnthropicNlpProvider;
use App\Services\Ai\Providers\GeminiNlpProvider;
use App\Services\Ai\Providers\OpenAiCompatibleNlpProvider;

class AiProviderManager
{
    /** @var list<string> */
    protected array $openAiCompatible = [
        'openai',
        'local',
        'groq',
        'together',
        'deepseek',
        'mistral',
        'openai_compatible',
    ];

    public function activeProviderName(): string
    {
        return config('ai.provider', 'regex');
    }

    public function resolve(?string $name = null): ?AiNlpProvider
    {
        $name = $name ?? $this->activeProviderName();

        if ($name === 'regex') {
            return null;
        }

        if (in_array($name, $this->openAiCompatible, true)) {
            return new OpenAiCompatibleNlpProvider($name);
        }

        return match ($name) {
            'gemini' => app(GeminiNlpProvider::class),
            'anthropic' => app(AnthropicNlpProvider::class),
            default => null,
        };
    }

    /**
     * Parse using active provider, then optional fallback chain, before regex.
     *
     * @return array<string, mixed>|null
     */
    public function parse(string $prompt, int $userId): ?array
    {
        $chain = $this->providerChain();

        foreach ($chain as $providerName) {
            $provider = $this->resolve($providerName);
            if (!$provider || !$provider->isConfigured()) {
                continue;
            }

            $result = $provider->parse($prompt, $userId);
            if ($result) {
                return $result;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public function providerChain(): array
    {
        $active = $this->activeProviderName();
        $fallbacks = config('ai.fallback_providers', []);

        $chain = array_values(array_unique(array_filter(array_merge(
            [$active],
            is_array($fallbacks) ? $fallbacks : []
        ))));

        return array_values(array_filter($chain, fn ($name) => $name !== 'regex'));
    }

    /**
     * @return list<string>
     */
    public function supportedProviderNames(): array
    {
        return array_merge(
            ['gemini', 'anthropic'],
            $this->openAiCompatible,
            ['regex']
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function allProviderStatuses(): array
    {
        $statuses = [];

        foreach ($this->supportedProviderNames() as $name) {
            if ($name === 'regex') {
                $statuses[$name] = [
                    'name' => 'regex',
                    'label' => 'Regex Parser',
                    'configured' => true,
                    'description' => 'Built-in PHP pattern matching (no external dependencies)',
                    'type' => 'regex',
                ];
                continue;
            }

            $provider = $this->resolve($name);
            if ($provider) {
                $statuses[$name] = $provider->status();
            }
        }

        return $statuses;
    }

    public function test(string $providerName): array
    {
        if ($providerName === 'regex') {
            return ['status' => 'ok', 'message' => 'Regex parser is always available locally.'];
        }

        $provider = $this->resolve($providerName);
        if (!$provider) {
            return ['status' => 'error', 'message' => "Unknown provider: {$providerName}"];
        }

        return $provider->testConnection();
    }
}
