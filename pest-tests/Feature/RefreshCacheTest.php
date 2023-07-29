<?php

use craft\elements\Asset;
use putyourlightson\blitz\Blitz;

beforeEach(function () {
    Blitz::$plugin->cacheStorage->deleteAll();
    Blitz::$plugin->flushCache->flushAll();
    Blitz::$plugin->generateCache->options->cachingEnabled = true;
    Blitz::$plugin->refreshCache->batchMode = true;
    Blitz::$plugin->refreshCache->reset();
});

test('ignores an element when unchanged', function () {
    $entry = createEntry();
    Blitz::$plugin->refreshCache->addElement($entry);

    expect(null)->toBeTracked();
});

test('adds an element when status is changed', function () {
    $entry = createEntry();
    $entry->enabled = false;
    Blitz::$plugin->refreshCache->addElement($entry);

    expect($entry)->toBeTracked();
});

test('adds an element when attribute is changed', function () {
    $entry = createEntry();
    $entry->title = 'Title123';
    Blitz::$plugin->refreshCache->addElement($entry);

    expect($entry)->toBeTracked('attributes', ['title']);
});

test('adds an element when field is changed', function () {
    $entry = createEntry();
    $entry->plainText = 'Text123';
    Blitz::$plugin->refreshCache->addElement($entry);

    expect($entry)->toBeTracked('fields', [], ['plainText']);
});

test('adds an element when attribute and field are changed', function () {
    $entry = createEntry();
    $entry->title = 'Title123';
    $entry->plainText = 'Text123';
    Blitz::$plugin->refreshCache->addElement($entry);

    expect($entry)->toBeTracked('attributes', ['title'], ['plainText']);
});

test('adds an element when status and attribute and field are changed', function () {
    $entry = createEntry();
    $entry->enabled = false;
    $entry->title = 'Title123';
    $entry->plainText = 'Text123';
    Blitz::$plugin->refreshCache->addElement($entry);

    expect($entry)->toBeTracked('', ['title'], ['plainText']);
});

test('adds an element when file is replaced', function () {
    $asset = createAsset();
    $asset->scenario = Asset::SCENARIO_REPLACE;
    Blitz::$plugin->refreshCache->addElement($asset);

    expect($asset)->toBeTracked()
        ->and(Blitz::$plugin->refreshCache->refreshData->getAssetsChangedByFile())
        ->toBe([$asset->id]);
});
