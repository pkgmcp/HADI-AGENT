<?php

use App\Traits\HasTokens;

beforeEach(function () {
    $this->trait = new class {
        use HasTokens;
    };
});

it('starts with zero tokens and cost', function () {
    expect($this->trait->getTotalTokens())->toBe(0);
    expect($this->trait->getTotalCost())->toBe(0.0);
});

it('accumulates tokens correctly', function () {
    $this->trait->addTokens(100, 50, 0.003);

    expect($this->trait->getTotalTokens())->toBe(150);
    expect($this->trait->getTotalCost())->toBe(0.003);
});

it('accumulates multiple additions', function () {
    $this->trait->addTokens(100, 50, 0.003);
    $this->trait->addTokens(200, 100, 0.006);

    expect($this->trait->getTotalTokens())->toBe(450);
    expect($this->trait->getTotalCost())->toBe(0.009);
});

it('resets tokens and cost', function () {
    $this->trait->addTokens(100, 50, 0.003);
    $this->trait->resetTokens();

    expect($this->trait->getTotalTokens())->toBe(0);
    expect($this->trait->getTotalCost())->toBe(0.0);
});

it('returns token summary', function () {
    $this->trait->addTokens(100, 50, 0.003);

    $summary = $this->trait->tokenSummary();

    expect($summary['total_tokens'])->toBe(150);
    expect($summary['total_cost'])->toBe(0.003);
});

it('handles zero token addition', function () {
    $this->trait->addTokens(0, 0, 0.0);

    expect($this->trait->getTotalTokens())->toBe(0);
    expect($this->trait->getTotalCost())->toBe(0.0);
});
