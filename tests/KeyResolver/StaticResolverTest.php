<?php

namespace MCordingley\LaravelSapient\Test\KeyResolver;

use MCordingley\LaravelSapient\KeyResolver\StaticResolver;

final class StaticResolverTest extends TestCase
{
    public function testResolveKey()
    {
        $key = 'foo';
        $resolver = new StaticResolver($key);

        static::assertEquals($key, $resolver->resolveKey());
    }
}
