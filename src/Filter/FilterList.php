<?php

namespace Brash\Eloquent\Filter;

use Brash\TypeCollection\AbstractTypeCollection;

class FilterList extends AbstractTypeCollection
{
    protected function willAcceptType($value): bool
    {
        return $value instanceof FilterInterface;
    }
}
