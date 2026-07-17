<?php

namespace App\Services\Ai\Concerns;

trait ParsesJsonNlpResponse
{
  /**
   * @return array<string, mixed>|null
   */
    protected function decodeIntentJson(?string $content, string $providerLabel): ?array
    {
        if (!$content) {
            return null;
        }

        $content = trim($content);
        // Strip markdown code fences if model wraps JSON
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $content, $m)) {
            $content = trim($m[1]);
        }

        $parsed = json_decode($content, true);

        if (!is_array($parsed) || !isset($parsed['intent'])) {
            logger()->warning("{$providerLabel} returned malformed JSON", ['raw' => $content]);
            return null;
        }

        return $parsed;
    }
}
