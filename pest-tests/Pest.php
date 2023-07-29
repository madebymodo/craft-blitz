<?php

use craft\base\Element;
use craft\elements\Asset;
use craft\elements\Entry;
use markhuot\craftpest\factories\Asset as AssetFactory;
use markhuot\craftpest\factories\Entry as EntryFactory;
use markhuot\craftpest\test\RefreshesDatabase;
use markhuot\craftpest\test\TestCase;
use putyourlightson\blitz\behaviors\ElementChangedBehavior;
use putyourlightson\blitz\Blitz;
use putyourlightson\blitz\helpers\FieldHelper;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(TestCase::class, RefreshesDatabase::class)->in('./');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeTracked', function (string $changedBy = '', array $changedAttributes = [], array $changedFields = []
) {
    /** @var Element|ElementChangedBehavior|null $element */
    $element = $this->value;
    $refreshData = Blitz::$plugin->refreshCache->refreshData;
    $changedFieldIds = FieldHelper::getFieldIdsFromHandles($changedFields);

    if ($element === null) {
        return expect($refreshData->isEmpty())->toBeTrue();
    }

    expect($refreshData->getElementIds($element::class))
        ->toEqual([$element->id])
        ->and($refreshData->getSourceIds($element::class))
        ->toEqual(!empty($element->sectionId) ? [$element->sectionId] : [])
        ->and($refreshData->getChangedAttributes($element::class, $element->id))
        ->toEqual($changedAttributes)
        ->and($refreshData->getChangedFields($element::class, $element->id))
        ->toEqual($changedFieldIds);

    if ($changedBy === 'attributes') {
        expect($refreshData->getIsChangedByAttributes(Entry::class, $element->id))
            ->toBeTrue()
            ->and($refreshData->getIsChangedByFields(Entry::class, $element->id))
            ->toBeFalse();
    } elseif ($changedBy === 'fields') {
        expect($refreshData->getIsChangedByAttributes(Entry::class, $element->id))
            ->toBeFalse()
            ->and($refreshData->getIsChangedByFields(Entry::class, $element->id))
            ->toBeTrue();
    } else {
        expect($refreshData->getIsChangedByAttributes(Entry::class, $element->id))
            ->toBeFalse()
            ->and($refreshData->getIsChangedByFields(Entry::class, $element->id))
            ->toBeFalse();
    }

    return $this;
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function createEntry(): Entry
{
    $entry = EntryFactory::factory()
        ->section('blog')
        ->create();

    $entry->attachBehavior(ElementChangedBehavior::BEHAVIOR_NAME, ElementChangedBehavior::class);

    Blitz::$plugin->refreshCache->reset();

    return $entry;
}

function createAsset(): Asset
{
    $asset = AssetFactory::factory()
        ->volume('test')
        ->create();

    Blitz::$plugin->refreshCache->reset();

    $asset->attachBehavior(ElementChangedBehavior::BEHAVIOR_NAME, ElementChangedBehavior::class);

    return $asset;
}
