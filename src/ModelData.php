<?php

declare(strict_types=1);

namespace VicGutt\ModelsFinder;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, string>
 */
final class ModelData implements Arrayable
{
    public function __construct(
        public readonly string $path,
        public readonly string $class,
    ) {
    }

    /**
     * Get the instance as an array.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'class' => $this->class,
        ];
    }
}
