<?php

namespace MCordingley\LaravelSapient\KeyResolver;

final class UserResolver implements Resolver
{
    /** @var string */
    private $property;

    /**
     * @param string $property
     */
    public function __construct(string $property)
    {
        $this->property = $property;
    }

    /**
     * @return string Base64UrlSafe-encoded
     */
    public function resolveKey(): string
    {
        return request()->user()->{$this->property};
    }
}
