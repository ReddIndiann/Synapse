<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Choose which NLP engine powers the AI Assistant.
    | Supported: 'gemini', 'local', 'regex'
    |
    | 'gemini' — Google Gemini API (requires GEMINI_API_KEY in .env)
    | 'local'  — Ollama / llama.cpp / any OpenAI-compatible local endpoint
    | 'regex'  — Built-in PHP pattern matching (no external dependencies)
    |
    */

    'provider' => env('AI_PROVIDER', 'regex'),

    /*
    |--------------------------------------------------------------------------
    | Local Model Settings
    |--------------------------------------------------------------------------
    |
    | When using 'local' provider, point to your running Ollama / llama.cpp
    | server. The endpoint must support the OpenAI chat completions format.
    |
    */

    'local' => [
        'endpoint' => env('AI_LOCAL_ENDPOINT', 'http://localhost:11434'),
        'model' => env('AI_LOCAL_MODEL', 'synapse-nlp:latest'),
        'context_length' => env('AI_LOCAL_CONTEXT', 4096),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cloud Model Settings
    |--------------------------------------------------------------------------
    */

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
    ],
];
