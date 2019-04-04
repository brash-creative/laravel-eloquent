<?php

namespace Brash\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use function request;

/**
 * Class CacheKeyGenerator
 * @package Brash\Eloquent
 */
class CacheKeyGenerator
{

    /**
     * @var Request|null
     */
    private $request;

    /**
     * CacheKeyGenerator constructor.
     *
     * @param Request|null $request
     */
    public function __construct(Request $request = null)
    {
        $this->request = $request ?? request();
    }

    protected function setUserCacheKey(string $existingKey = ''): string
    {
        $user = $this->request->user();

        if ($user instanceof Model && $user->roles instanceof Collection) {
            /** @var array $roles */
            $roles = $user->roles->map(function ($item) {
                return $item->name;
            })->all();

            ksort($roles);

            return sprintf('%s:roles:%s', $existingKey, implode('.', $roles));
        }

        return $existingKey;
    }

    protected function setQueryCacheKey(string $existingKey = ''): string
    {
        $queryArray = $this->request->query->all();

        if (!empty($queryArray)) {
            $cacheKey = empty($existingKey) ? 'request' : ':request';
            $queryArray = array_dot($queryArray);

            ksort($queryArray);

            foreach ($queryArray as $key => $value) {
                $cacheKey = sprintf('%s:%s,%s', $cacheKey, $key, $value);
            }

            return sprintf('%s%s', $existingKey, $cacheKey);
        }

        return $existingKey;
    }

    protected function setRelationsCacheKey(string $existingKey = ''): string
    {
        $key = '';

        if (!empty($this->with)) {
            $with = $this->with;

            sort($with);

            $key = sprintf(':relations:%s', implode(',', $with));
        }

        return sprintf('%s%s', $existingKey, $key);
    }

    protected function setCountCacheKey(string $existingKey = ''): string
    {
        $key = '';

        if (!empty($this->withCount)) {
            $with = $this->withCount;

            sort($with);

            $key = sprintf(':count:%s', implode(',', $with));
        }

        return sprintf('%s%s', $existingKey, $key);
    }

    protected function getCacheKey(...$params): string
    {
        $key = sprintf('querybuilder:%s', $this->getTable());
        $key = $this->setUserCacheKey($key);
        $key = $this->setQueryCacheKey($key);
        $key = $this->setRelationsCacheKey($key);
        $key = $this->setCountCacheKey($key);

        if ($this->cacheKey) {
            $key = sprintf('%s:%s', $key, $this->cacheKey);
        }

        foreach ($params as $param) {
            $key = sprintf('%s:%s', $key, $param);
        }

        return md5($key);
    }
}
