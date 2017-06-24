<?php

namespace MCordingley\LaravelSapient\Console;

use ParagonIE\ConstantTime\Base64UrlSafe;
use Throwable;

final class GenerateSigningKeyPair extends GenerateCommand
{
    /** @var string */
    protected $signature = 'sapient:generate:sign:pair
                    {--show : Display the keys instead of modifying files}
                    {--force : Force the operation to run when in production}';

    /** @var string */
    protected $description = 'Set Sapient signing keys.';

    /**
     * @return void
     */
    public function fire()
    {
        $pair = sodium_crypto_sign_keypair();

        $public = Base64UrlSafe::encode(sodium_crypto_sign_publickey($pair));
        $private = Base64UrlSafe::encode(sodium_crypto_sign_secretkey($pair));

        try {
            sodium_memzero($pair);
        } catch (Throwable $exception) {
            //
        }

        if ($this->option('show')) {
            $this->comment('<comment>Public Key: ' . $public . '</comment>');
            $this->comment('<comment>Secret Key: ' . $private . '</comment>');

            return;
        }

        if (
            $this->confirmOverwrite('SAPIENT_SIGNING_PUBLIC_KEY') &&
            $this->confirmOverwrite('SAPIENT_SIGNING_PRIVATE_KEY')
        ) {
            $this->writeConfigurationValue('SAPIENT_SIGNING_PUBLIC_KEY', $public);
            $this->writeConfigurationValue('SAPIENT_SIGNING_PRIVATE_KEY', $private);

            $this->info("Sapient signing keys set successfully.");
        }

        try {
            sodium_memzero($public);
            sodium_memzero($private);
        } catch (Throwable $exception) {
            //
        }
    }
}
