<?php

uses(Tests\TestCase::class)->in('Feature', 'Unit', 'API', 'MCP', 'Agents', 'AI');

expect()->extend('toBeResponse', function () {
    return $this->toHaveKey('data');
});
