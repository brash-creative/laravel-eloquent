<?php

namespace Brash\Eloquent;

interface CachableQueryBuilderInterface extends QueryBuilderInterface
{
    public function key(string $key);
}
