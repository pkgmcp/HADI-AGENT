<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait HasLogger
{
    protected string $logChannel = 'daily';

    public function logInfo(string $message, array $context = []): void
    {
        Log::channel($this->logChannel)->info($this->logPrefix() . $message, $context);
    }

    public function logWarning(string $message, array $context = []): void
    {
        Log::channel($this->logChannel)->warning($this->logPrefix() . $message, $context);
    }

    public function logError(string $message, array $context = []): void
    {
        Log::channel($this->logChannel)->error($this->logPrefix() . $message, $context);
    }

    public function logDebug(string $message, array $context = []): void
    {
        Log::channel($this->logChannel)->debug($this->logPrefix() . $message, $context);
    }

    protected function logPrefix(): string
    {
        return '[' . class_basename(static::class) . '] ';
    }
}
