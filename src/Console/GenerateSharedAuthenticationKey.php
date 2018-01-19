<?php

namespace MCordingley\LaravelSapient\Console;

use ParagonIE\ConstantTime\Base64UrlSafe;
use Throwable;

final class GenerateSharedAuthenticationKey extends GenerateCommand
{
    /** @var string */
    protected $signature = 'sapient:generate:shared:authentication
                    {--show : Display the key instead of modifying files}
                    {--force : Force the operation to run when in production}';

    /** @var string */
    protected $description = 'Set Sapient shared authentication key.';

    /**
     * @return void
     */
    public function handle()
    {
        $key = Base64UrlSafe::encode(random_bytes(SODIUM_CRYPTO_AUTH_KEYBYTES));

        if ($this->option('show')) {
            $this->comment('<comment>Key: ' . $key . '</comment>');

            return;
        }

        if ($this->confirmOverwrite('SAPIENT_SHARED_AUTHENTICATION_KEY')) {
            $this->writeConfigurationValue('SAPIENT_SHARED_AUTHENTICATION_KEY', $key);

            $this->info("Sapient shared authentication key set successfully.");
        }

        try {
            sodium_memzero($key);
        } catch (Throwable $exception) {
            //
        }
    }
}
