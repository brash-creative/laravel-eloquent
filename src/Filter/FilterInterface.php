<?php

namespace Brash\Eloquent\Filter;

use Illuminate\Database\Eloquent\Builder;

interface FilterInterface
{
    public function __invoke(Builder $query, $value): Builder;
}
