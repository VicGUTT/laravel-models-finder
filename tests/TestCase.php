<?php

declare(strict_types=1);

namespace VicGutt\ModelsFinder\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getTestSupportDirectory(string $path = ''): string
    {
        return $this->getTestDirectory("/TestSupport/{$path}");
    }

    protected function getTestDirectory(string $path = ''): string
    {
        return str_replace(['\\', '//'], '/', realpath(__DIR__) . '/' . $path);
    }
}
