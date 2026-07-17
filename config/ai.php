<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Set AI_PROVIDER to the engine that powers the assistant NLP layer.
    |
    | Built-in (no API key):
    |   regex — PHP pattern matching fallback
    |
    | Cloud APIs:
    |   gemini, openai, anthropic, groq, together, deepseek, mistral
    |
    | Generic OpenAI-compatible (any host that supports /v1/chat/completions):
    |   openai_compatible — set AI_COMPATIBLE_* env vars
    |
    | Local:
    |   local — Ollama / llama.cpp / LM Studio
    |
    */

    'provider' => env('AI_PROVIDER', 'regex'),

    /*
    |--------------------------------------------------------------------------
    | Fallback Providers (optional)
    |--------------------------------------------------------------------------
    |
    | Comma-separated list tried in order when the primary provider fails.
    | Example: AI_FALLBACK_PROVIDERS=openai,gemini,local
    |
    */

    'fallback_providers' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('AI_FALLBACK_PROVIDERS', ''))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Google Gemini
    |--------------------------------------------------------------------------
    */

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
        'timeout' => (int) env('GEMINI_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI
    |--------------------------------------------------------------------------
    */

    'openai' => [
        'label' => 'OpenAI',
        'endpoint' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'timeout' => (int) env('OPENAI_TIMEOUT', 30),
        'temperature' => 0.1,
        'max_tokens' => 512,
        'json_mode' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Anthropic Claude
    |--------------------------------------------------------------------------
    */

    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-3-5-haiku-latest'),
        'version' => env('ANTHROPIC_API_VERSION', '2023-06-01'),
        'timeout' => (int) env('ANTHROPIC_TIMEOUT', 30),
        'temperature' => 0.1,
        'max_tokens' => 512,
    ],

    /*
    |--------------------------------------------------------------------------
    | Groq (OpenAI-compatible)
    |--------------------------------------------------------------------------
    */

    'groq' => [
        'label' => 'Groq',
        'endpoint' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
        'key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        'timeout' => (int) env('GROQ_TIMEOUT', 30),
        'temperature' => 0.1,
        'max_tokens' => 512,
        'json_mode' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Together AI (OpenAI-compatible)
    |--------------------------------------------------------------------------
    */

    'together' => [
        'label' => 'Together AI',
        'endpoint' => env('TOGETHER_BASE_URL', 'https://api.together.xyz/v1'),
        'key' => env('TOGETHER_API_KEY'),
        'model' => env('TOGETHER_MODEL', 'meta-llama/Llama-3.3-70B-Instruct-Turbo'),
        'timeout' => (int) env('TOGETHER_TIMEOUT', 30),
        'temperature' => 0.1,
        'max_tokens' => 512,
        'json_mode' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | DeepSeek (OpenAI-compatible)
    |--------------------------------------------------------------------------
    */

    'deepseek' => [
        'label' => 'DeepSeek',
        'endpoint' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com/v1'),
        'key' => env('DEEPSEEK_API_KEY'),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
        'timeout' => (int) env('DEEPSEEK_TIMEOUT', 30),
        'temperature' => 0.1,
        'max_tokens' => 512,
        'json_mode' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Mistral (OpenAI-compatible)
    |--------------------------------------------------------------------------
    */

    'mistral' => [
        'label' => 'Mistral AI',
        'endpoint' => env('MISTRAL_BASE_URL', 'https://api.mistral.ai/v1'),
        'key' => env('MISTRAL_API_KEY'),
        'model' => env('MISTRAL_MODEL', 'mistral-small-latest'),
        'timeout' => (int) env('MISTRAL_TIMEOUT', 30),
        'temperature' => 0.1,
        'max_tokens' => 512,
        'json_mode' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Generic OpenAI-compatible endpoint (Azure, custom gateways, etc.)
    |--------------------------------------------------------------------------
    */

    'openai_compatible' => [
        'label' => env('AI_COMPATIBLE_LABEL', 'OpenAI-Compatible API'),
        'endpoint' => env('AI_COMPATIBLE_ENDPOINT', 'https://api.openai.com/v1'),
        'key' => env('AI_COMPATIBLE_API_KEY'),
        'model' => env('AI_COMPATIBLE_MODEL', 'gpt-4o-mini'),
        'timeout' => (int) env('AI_COMPATIBLE_TIMEOUT', 30),
        'temperature' => 0.1,
        'max_tokens' => 512,
        'json_mode' => filter_var(env('AI_COMPATIBLE_JSON_MODE', true), FILTER_VALIDATE_BOOL),
    ],

    /*
    |--------------------------------------------------------------------------
    | Local AI (Ollama / llama.cpp)
    |--------------------------------------------------------------------------
    */

    'local' => [
        'label' => 'Local AI (Ollama)',
        'endpoint' => env('AI_LOCAL_ENDPOINT', 'http://localhost:11434'),
        'key' => env('AI_LOCAL_API_KEY'), // optional; Ollama usually needs no key
        'model' => env('AI_LOCAL_MODEL', 'synapse-nlp:latest'),
        'context_length' => (int) env('AI_LOCAL_CONTEXT', 4096),
        'timeout' => (int) env('AI_LOCAL_TIMEOUT', 30),
        'temperature' => 0.1,
        'max_tokens' => 512,
        'json_mode' => true,
    ],
];
