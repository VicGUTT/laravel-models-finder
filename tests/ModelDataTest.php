<?php

declare(strict_types=1);

use VicGutt\ModelsFinder\ModelData;
use Illuminate\Contracts\Support\Arrayable;

it('is "Arrayable"', function (): void {
    expect(new ModelData('', '') instanceof Arrayable)->toEqual(true);
});

it("can be represented as an array", function (): void {
    $model = new ModelData('/some/path', '\Some\Path');

    expect($model->toArray())->toEqual([
        'path' => '/some/path',
        'class' => '\Some\Path',
    ]);
});
