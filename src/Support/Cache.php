<?php

declare(strict_types=1);

namespace TiMacDonald\JsonApi\Support;

use TiMacDonald\JsonApi\JsonApiResource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

/**
 * @internal
 */
class Cache
{
    /**
     * @param JsonApiResource|JsonApiResourceCollection $resource
     */
    public static function flush($resource): void
    {
        $resource->flush();

        Includes::getInstance()->flush();

        Fields::getInstance()->flush();
    }
}
