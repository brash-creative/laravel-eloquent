<?php

namespace Brash\Eloquent;

interface CachableQueryBuilderInterface extends QueryBuilderInterface
{
    public function addKey(string $key);

    public function setKey(string $key);
}
