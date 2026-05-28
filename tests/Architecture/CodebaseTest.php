<?php

arch('app')
    ->expect('App')
    ->toUseStrictTypes();

arch('services')
    ->expect('App\Services')
    ->toExtendNothing()
    ->toOnlyBeUsedIn('App\Http\Controllers')
    ->ignoring('App\Services\Agents\BaseAgent');

arch('dto')
    ->expect('App\DTOs')
    ->toBeReadonly();

arch('interfaces')
    ->expect('App\Interfaces')
    ->toBeInterfaces();

arch('controllers')
    ->expect('App\Http\Controllers')
    ->toExtend('App\Http\Controllers\Controller');

arch('request validation')
    ->expect('App\Http\Requests')
    ->toExtend('Illuminate\Foundation\Http\FormRequest');

arch('no controllers with business logic')
    ->expect('App\Http\Controllers')
    ->not->toHaveMethods(['save', 'update', 'delete', 'process']);

arch('no dump death')
    ->expect('dd')
    ->not->toBeUsed();
