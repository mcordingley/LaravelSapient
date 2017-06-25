<?php

namespace MCordingley\LaravelSapient\KeyResolver;

final class StaticResolver implements Resolver
{
    /** @var string */
    private $key;

    /**
     * StaticResolver constructor.
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * @return string Base64UrlSafe-encoded
     */
    public function resolveKey(): string
    {
        return $this->key;
    }
}
