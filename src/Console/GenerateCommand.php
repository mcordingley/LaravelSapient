<?php

namespace MCordingley\LaravelSapient\Console;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use ParagonIE\ConstantTime\Base64UrlSafe;

abstract class GenerateCommand extends Command
{
    use ConfirmableTrait;

    /**
     * @param string $key
     * @return string
     */
    final protected function decode(string $key): string
    {
        return Base64UrlSafe::decode($key);
    }

    /**
     * @param string $key
     * @return string
     */
    final protected function encode(string $key): string
    {
        return Base64UrlSafe::encode($key);
    }

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
        $contents = file_get_contents($this->laravel->environmentFilePath());

        $pattern = "/^$key=.*$/m";
        $line = $key . '=' . $value;

        file_put_contents(
            $this->laravel->environmentFilePath(),
            preg_match($pattern, $contents) ? preg_replace($pattern, $line, $contents) : $contents . "\n" . $line
        );
    }
}
