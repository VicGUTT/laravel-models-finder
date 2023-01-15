<?php

declare(strict_types=1);

namespace VicGutt\ModelsFinder;

use Generator;
use Illuminate\Support\Str;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Illuminate\Support\Stringable;
use Illuminate\Support\LazyCollection;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://github.com/spatie/laravel-model-info/blob/86a8a9524a9d313762e4f1b775f275d35dda1cc2/src/ModelFinder.php
 */
class ModelsFinder
{
    /**
     * @return LazyCollection<int, ModelData>
     */
    public static function find(
        ?string $directory = null,
        ?string $basePath = null,
        ?string $baseNamespace = null,
    ): LazyCollection {
        $directory ??= self::defaultDirectory();
        $basePath ??= self::defaultBasePath();
        $baseNamespace ??= self::defaultBaseNamespace();

        return LazyCollection::make(static function () use ($directory, $basePath, $baseNamespace): Generator {
            foreach (static::getFilesRecursively($directory) as $filePath) {
                $class = self::determineModelFullyQualifiedClassNameFromFilePath($filePath, $basePath, $baseNamespace);

                if (is_subclass_of($class, Model::class)) {
                    yield new ModelData(
                        $filePath,
                        $class,
                    );
                }
            }
        })->filter();
    }

    public static function defaultBasePath(): string
    {
        return base_path();
    }

    public static function defaultDirectory(): string
    {
        return app_path('Models');
    }

    public static function defaultBaseNamespace(): string
    {
        return '';
    }

    public static function getFilesRecursively(string $path): array
    {
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $files = [];

        /** @var RecursiveDirectoryIterator $file */
        foreach ($rii as $file) {
            if ($file->isDir()) {
                continue;
            }

            $files[] = realpath($file->getPathname());
        }

        return $files;
    }

    public static function determineModelFullyQualifiedClassNameFromFilePath(
        string $filePath,
        string $basePath,
        string $baseNamespace,
    ): string {
        $NAMESPACE_SEPARATOR = '\\';

        return Str::of((string) realpath($filePath))
            ->after((string) realpath($basePath))
            ->beforeLast('.php')
            ->trim(DIRECTORY_SEPARATOR)
            ->replace(DIRECTORY_SEPARATOR, $NAMESPACE_SEPARATOR)
            ->when(
                static fn (Stringable $value): bool => $value->startsWith(mb_strtolower(app()->getNamespace())),
                static fn (Stringable $value): Stringable => $value->replace(
                    mb_strtolower(app()->getNamespace()),
                    app()->getNamespace(),
                ),
            )
            ->prepend($baseNamespace . $NAMESPACE_SEPARATOR)
            ->trim($NAMESPACE_SEPARATOR)
            ->value();
    }
}
