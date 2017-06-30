<?php

namespace MCordingley\LaravelSapient\Console;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

abstract class GenerateCommand extends Command
{
    use ConfirmableTrait;

    /**
     * @param string $key
     * @return bool
     */
    final protected function confirmOverwrite(string $key): bool
    {
        return !$this->laravel['config'][$key] || $this->confirmToProceed();
    }

    /**
     * @param string $key
     * @param string $value
     * @return void
     */
    final protected function writeConfigurationValue(string $key, string $value)
    {
        $pattern = "/^$key=.*$/m";
        $line = $key . '=' . $value;

        $filePath = $this->laravel->basePath() . '/.env';
        $contents = file_get_contents($filePath);
        $updated = preg_match($pattern, $contents) ? preg_replace($pattern, $line, $contents) : $contents . "\n" . $line;

        file_put_contents($filePath, $updated);
    }
}
