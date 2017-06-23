<?php

namespace MCordingley\LaravelSapient\Contracts;

interface KeyResolver
{
    /**
     * @return string Base64UrlSafe-encoded
     */
    public function resolveKey(): string;
}
