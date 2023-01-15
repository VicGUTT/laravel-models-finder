<?php

declare(strict_types=1);

use VicGutt\ModelsFinder\ModelData;
use Illuminate\Foundation\Application;
use Illuminate\Support\LazyCollection;
use VicGutt\ModelsFinder\ModelsFinder;
use VicGutt\ModelsFinder\Tests\TestSupport\app\Models\BasicModel;
use VicGutt\ModelsFinder\Tests\TestSupport\app\Folder\Level3\ModelLevel3;
use VicGutt\ModelsFinder\Tests\TestSupport\app\Folder\BasicModel as BasicModel2;
use VicGutt\ModelsFinder\Tests\TestSupport\app\Folder\Level3\Level4\ModelLevel4;
use VicGutt\ModelsFinder\Tests\TestSupport\app\Folder\Level3\Level4\Level5\ModelLevel5;

$_ = static fn (string $path): string => str_replace(['\\', '/'], '/', $path);
$__ = static fn (array $paths): array => array_map($_(...), $paths);

it("defines a default base path", function () use ($_): void {
    expect(ModelsFinder::defaultBasePath())->toEqual(base_path());

    /** @var Application */
    $app = app();

    $app->setBasePath('yep/yolo');

    expect($_(ModelsFinder::defaultBasePath()))->toEqual('yep/yolo');
});

it("defines a default directory", function () use ($_): void {
    expect(ModelsFinder::defaultDirectory())->toEqual(app_path('Models'));

    /** @var Application */
    $app = app();

    $app->setBasePath('yep/yolo');

    expect($_(ModelsFinder::defaultDirectory()))->toEqual('yep/yolo/app/Models');
});

it("defines a default namespace", function () use ($_): void {
    expect(ModelsFinder::defaultBaseNamespace())->toEqual('');

    /** @var Application */
    $app = app();

    $app->setBasePath('yep/yolo');

    expect($_(ModelsFinder::defaultBaseNamespace()))->toEqual('');
});

it("can retrieve files recursively from a given path", function () use ($_, $__): void {
    expect($__(ModelsFinder::getFilesRecursively($this->getTestDirectory())))->toEqualCanonicalizing([
        $_($this->getTestDirectory('/ModelDataTest.php')),
        $_($this->getTestDirectory('/ModelsFinderTest.php')),
        $_($this->getTestDirectory('/Pest.php')),
        $_($this->getTestDirectory('/TestCase.php')),
        $_($this->getTestDirectory('/TestSupport/app/Folder/BasicClass.php')),
        $_($this->getTestDirectory('/TestSupport/app/Folder/BasicModel.php')),
        $_($this->getTestDirectory('/TestSupport/app/Folder/Level3/ClassLevel3.php')),
        $_($this->getTestDirectory('/TestSupport/app/Folder/Level3/Level4/ClassLevel4.php')),
        $_($this->getTestDirectory('/TestSupport/app/Folder/Level3/Level4/Level5/ClassLevel5.php')),
        $_($this->getTestDirectory('/TestSupport/app/Folder/Level3/Level4/Level5/ModelLevel5.php')),
        $_($this->getTestDirectory('/TestSupport/app/Folder/Level3/Level4/ModelLevel4.php')),
        $_($this->getTestDirectory('/TestSupport/app/Folder/Level3/ModelLevel3.php')),
        $_($this->getTestDirectory('/TestSupport/app/Models/BasicClass.php')),
        $_($this->getTestDirectory('/TestSupport/app/Models/BasicModel.php')),
    ]);
});

it("can determine a model fully qualified class name from a given file path", function (): void {
    $result = ModelsFinder::determineModelFullyQualifiedClassNameFromFilePath(
        $this->getTestSupportDirectory('/app/Folder/Level3/Level4/Level5/ModelLevel5.php'),
        $this->getTestDirectory(),
        'VicGutt\ModelsFinder\Tests',
    );

    expect($result)->toEqual(ModelLevel5::class);

    $result = ModelsFinder::determineModelFullyQualifiedClassNameFromFilePath(
        $this->getTestSupportDirectory('/app/Models/BasicModel.php'),
        $this->getTestDirectory(),
        'VicGutt\ModelsFinder\Tests',
    );

    expect($result)->toEqual(BasicModel::class);
});

it("applies the given base path to determine a model fully qualified class name from a given file path", function (): void {
    $result = ModelsFinder::determineModelFullyQualifiedClassNameFromFilePath(
        $this->getTestSupportDirectory('/app/Folder/Level3/Level4/Level5/ModelLevel5.php'),
        $this->getTestSupportDirectory(),
        'VicGutt\ModelsFinder\Tests',
    );

    expect($result)->toEqual(str_replace(['\TestSupport', '\app'], ['', '\App'], ModelLevel5::class));

    $result = ModelsFinder::determineModelFullyQualifiedClassNameFromFilePath(
        $this->getTestSupportDirectory('/app/Folder/Level3/Level4/Level5/ModelLevel5.php'),
        dirname($this->getTestSupportDirectory()),
        'VicGutt\ModelsFinder\Tests',
    );

    expect($result)->toEqual(ModelLevel5::class);
});

it("applies the given base namespace to determine a model fully qualified class name from a given file path", function (): void {
    $result = ModelsFinder::determineModelFullyQualifiedClassNameFromFilePath(
        $this->getTestSupportDirectory('/app/Folder/Level3/Level4/Level5/ModelLevel5.php'),
        $this->getTestDirectory(),
        'Yolo',
    );

    expect($result)->toEqual(str_replace('VicGutt\ModelsFinder\Tests', 'Yolo', ModelLevel5::class));

    $result = ModelsFinder::determineModelFullyQualifiedClassNameFromFilePath(
        $this->getTestSupportDirectory('/app/Folder/Level3/Level4/Level5/ModelLevel5.php'),
        $this->getTestDirectory(),
        '',
    );

    expect($result)->toEqual(str_replace('VicGutt\ModelsFinder\Tests\\', '', ModelLevel5::class));
});

it('can find all models in a given directory', function (): void {
    $models = ModelsFinder::find(
        $this->getTestSupportDirectory('/app/Folder/Level3/Level4/Level5'),
        $this->getTestDirectory(),
        "VicGutt\ModelsFinder\Tests",
    );

    expect($models instanceof LazyCollection)->toEqual(true);

    expect($models->count())->toEqual(1);
});

it('provides the found model in the form of a ModelData', function (): void {
    $models = ModelsFinder::find(
        $this->getTestSupportDirectory('/app/Folder/Level3/Level4/Level5'),
        $this->getTestDirectory(),
        "VicGutt\ModelsFinder\Tests",
    );

    expect($models->count())->toEqual(1);

    /** @var ModelData */
    $data = $models->first();

    expect(is_object($data))->toEqual(true);
    expect($data instanceof ModelData)->toEqual(true);

    $path = realpath($this->getTestSupportDirectory('/app/Folder/Level3/Level4/Level5/ModelLevel5.php'));

    expect($data->path)->toEqual($path);
    expect($data->class)->toEqual(ModelLevel5::class);
});

it("can find all models in a given directory and it's sub-directories", function (): void {
    $models = ModelsFinder::find(
        $this->getTestSupportDirectory(),
        $this->getTestDirectory(),
        'VicGutt\ModelsFinder\Tests',
    );

    expect($models->count())->toEqual(5);

    /** @var array{path: string, class: string} */
    $items = $models
        ->sort(static fn (ModelData $data): string => $data->class)
        ->map(static fn (ModelData $data): array => [
            'path' => $data->path,
            'class' => $data->class,
        ])
        ->toArray();

    expect($items)->toEqualCanonicalizing([
        [
            'path' => realpath($this->getTestSupportDirectory('/app/Folder/BasicModel.php')),
            'class' => BasicModel2::class,
        ],
        [
            'path' => realpath($this->getTestSupportDirectory('/app/Folder/Level3/Level4/Level5/ModelLevel5.php')),
            'class' => ModelLevel5::class,
        ],
        [
            'path' => realpath($this->getTestSupportDirectory('/app/Folder/Level3/Level4/ModelLevel4.php')),
            'class' => ModelLevel4::class,
        ],
        [
            'path' => realpath($this->getTestSupportDirectory('/app/Folder/Level3/ModelLevel3.php')),
            'class' => ModelLevel3::class,
        ],
        [
            'path' => realpath($this->getTestSupportDirectory('/app/Models/BasicModel.php')),
            'class' => BasicModel::class,
        ],
    ]);
});

it("will not find any models in a given directory at the same level as the base path without adjusting the given base namespace", function (): void {
    $models = ModelsFinder::find(
        $this->getTestSupportDirectory(),
        $this->getTestSupportDirectory(),
        'VicGutt\ModelsFinder\Tests',
    );

    expect($models->count())->toEqual(0);

    $models = ModelsFinder::find(
        $this->getTestSupportDirectory(),
        $this->getTestSupportDirectory(),
        'VicGutt\ModelsFinder\Tests\TestSupport',
    );

    expect($models->count())->toEqual(5);
});

it("will not find any models in a given directory level inferior to the base path without adjusting the given base namespace to an extent", function (): void {
    $models = ModelsFinder::find(
        $this->getTestSupportDirectory(),
        dirname($this->getTestSupportDirectory()),
        'VicGutt\ModelsFinder\Tests',
    );

    expect($models->count())->toEqual(5);

    $models = ModelsFinder::find(
        $this->getTestSupportDirectory(),
        dirname($this->getTestSupportDirectory(), 2),
        'VicGutt\ModelsFinder\Tests',
    );

    expect($models->count())->toEqual(0);

    $models = ModelsFinder::find(
        $this->getTestSupportDirectory(),
        dirname($this->getTestSupportDirectory(), 3),
        'VicGutt\ModelsFinder\Tests',
    );

    expect($models->count())->toEqual(0);

    $models = ModelsFinder::find(
        $this->getTestSupportDirectory(),
        dirname($this->getTestSupportDirectory(), 2),
        'VicGutt\ModelsFinder',
    );

    expect($models->count())->toEqual(5);

    $models = ModelsFinder::find(
        $this->getTestSupportDirectory(),
        dirname($this->getTestSupportDirectory(), 3),
        'VicGutt',
    );

    expect($models->count())->toEqual(0);

    $models = ModelsFinder::find(
        $this->getTestSupportDirectory(),
        dirname($this->getTestSupportDirectory(), 4),
        '',
    );

    expect($models->count())->toEqual(0);
});
