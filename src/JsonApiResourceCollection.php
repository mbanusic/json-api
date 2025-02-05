<?php

declare(strict_types=1);

namespace TiMacDonald\JsonApi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use TiMacDonald\JsonApi\Support\Cache;

class JsonApiResourceCollection extends AnonymousResourceCollection
{
    /**
     * @param Request $request
     */
    public function with($request): array
    {
        return [
            'included' => $this->collection
                ->map(fn (JsonApiResource $resource): Collection => $resource->included($request))
                ->flatten()
                ->reject(fn (?JsonApiResource $resource): bool => $resource === null)
                ->uniqueStrict(fn (JsonApiResource $resource): string => $resource->toUniqueResourceIdentifier($request)),
        ];
    }

    /**
     * @param Request $request
     */
    public function toResponse($request)
    {
        return tap(parent::toResponse($request)->header('Content-type', 'application/vnd.api+json'), fn () => Cache::flush($this));
    }

    /**
     * @internal
     * @return static
     */
    public function withIncludePrefix(string $prefix)
    {
        /** @phpstan-ignore-next-line */
        $this->collection->each(fn (JsonApiResource $resource): JsonApiResource => $resource->withIncludePrefix($prefix));

        return $this;
    }

    /**
     * @internal
     */
    public function included(Request $request): Collection
    {
        return $this->collection->map(fn (JsonApiResource $resource): Collection => $resource->included($request));
    }

    /**
     * @internal
     */
    public function toResourceIdentifier(Request $request): array
    {
        return $this->collection->map(fn (JsonApiResource $resource): array => $resource->toResourceIdentifier($request))->all();
    }

    /**
     * @internal
     */
    public function includable(): Collection
    {
        return $this->collection;
    }

    /**
     * @internal
     * @return static
     */
    public function filterDuplicates(Request $request)
    {
        $this->collection = $this->collection->uniqueStrict(fn (JsonApiResource $resource): string => $resource->toUniqueResourceIdentifier($request));

        return $this;
    }

    /**
     * @internal
     * @infection-ignore-all
     */
    public function flush(): void
    {
        $this->collection->each(fn (JsonApiResource $resource) => $resource->flush());
    }
}
