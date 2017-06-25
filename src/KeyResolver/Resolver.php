<?php

namespace MCordingley\LaravelSapient\KeyResolver;

interface Resolver
{
    /**
     * @return string Base64UrlSafe-encoded
     */
    public function resolveKey(): string;
}
