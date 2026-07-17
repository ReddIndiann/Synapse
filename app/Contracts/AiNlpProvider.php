<?php

namespace App\Contracts;

interface AiNlpProvider
{
    /**
     * Provider identifier (matches config key / AI_PROVIDER value).
     */
    public function name(): string;

    /**
     * Human-readable label for admin UI.
     */
    public function label(): string;

    public function isConfigured(): bool;

    /**
     * @return array{status: string, message: string}
     */
    public function testConnection(): array;

    /**
     * Parse natural language into intent JSON.
     *
     * @return array<string, mixed>|null
     */
    public function parse(string $prompt, int $userId): ?array;

    /**
     * @return array<string, mixed>
     */
    public function status(): array;
}
