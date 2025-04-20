<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/
use PazaramaApi\PazaramaSpApi\Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeSuccessfulResponse', function () {
    return $this->and($this->value)
                ->toBeArray()
                ->toHaveKey('success')
                ->toHaveKey('data');
});

expect()->extend('toBeFailedResponse', function () {
    return $this->and($this->value)
                ->toBeArray()
                ->toHaveKey('success', false)
                ->toHaveKey('error');
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

/**
 * Private özelliklere erişmek için yardımcı fonksiyon
 */
function setPrivateProperty($object, $property, $value)
{
    $reflection = new \ReflectionClass($object);
    $property = $reflection->getProperty($property);
    $property->setAccessible(true);
    $property->setValue($object, $value);
} 