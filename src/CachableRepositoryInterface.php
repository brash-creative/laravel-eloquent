<?php

namespace Brash\Eloquent;

interface CachableRepositoryInterface extends RepositoryInterface
{
    public function key(string $key);
}
