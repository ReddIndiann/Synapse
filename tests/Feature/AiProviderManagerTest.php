<?php

namespace Tests\Feature;

use App\Services\Ai\AiProviderManager;
use App\Services\Ai\Providers\AnthropicNlpProvider;
use App\Services\Ai\Providers\GeminiNlpProvider;
use App\Services\Ai\Providers\OpenAiCompatibleNlpProvider;
use Tests\TestCase;

class AiProviderManagerTest extends TestCase
{
    public function test_resolves_gemini_provider(): void
    {
        $manager = app(AiProviderManager::class);
        $provider = $manager->resolve('gemini');

        $this->assertInstanceOf(GeminiNlpProvider::class, $provider);
    }

    public function test_resolves_anthropic_provider(): void
    {
        $manager = app(AiProviderManager::class);
        $provider = $manager->resolve('anthropic');

        $this->assertInstanceOf(AnthropicNlpProvider::class, $provider);
    }

    public function test_resolves_openai_compatible_providers(): void
    {
        $manager = app(AiProviderManager::class);

        foreach (['openai', 'groq', 'together', 'deepseek', 'mistral', 'local', 'openai_compatible'] as $name) {
            $provider = $manager->resolve($name);
            $this->assertInstanceOf(OpenAiCompatibleNlpProvider::class, $provider);
            $this->assertSame($name, $provider->name());
        }
    }

    public function test_regex_provider_resolves_to_null(): void
    {
        $manager = app(AiProviderManager::class);
        $this->assertNull($manager->resolve('regex'));
    }

    public function test_provider_chain_includes_active_and_fallbacks(): void
    {
        config([
            'ai.provider' => 'openai',
            'ai.fallback_providers' => ['gemini', 'local'],
        ]);

        $manager = app(AiProviderManager::class);

        $this->assertSame(['openai', 'gemini', 'local'], $manager->providerChain());
    }

    public function test_all_provider_statuses_includes_regex(): void
    {
        $manager = app(AiProviderManager::class);
        $statuses = $manager->allProviderStatuses();

        $this->assertArrayHasKey('regex', $statuses);
        $this->assertArrayHasKey('gemini', $statuses);
        $this->assertArrayHasKey('openai', $statuses);
        $this->assertArrayHasKey('anthropic', $statuses);
        $this->assertTrue($statuses['regex']['configured']);
    }

    public function test_openai_provider_reports_not_configured_without_key(): void
    {
        config(['ai.openai.key' => null]);

        $provider = new OpenAiCompatibleNlpProvider('openai');

        $this->assertFalse($provider->isConfigured());
    }

    public function test_local_provider_configured_without_api_key(): void
    {
        config([
            'ai.local.endpoint' => 'http://localhost:11434',
            'ai.local.model' => 'synapse-nlp:latest',
            'ai.local.key' => null,
        ]);

        $provider = new OpenAiCompatibleNlpProvider('local');

        $this->assertTrue($provider->isConfigured());
    }
}
