<?php

namespace Amitav\LaravelElastic\Traits;

use Carbon\Carbon;
use Elasticsearch\ClientBuilder;

trait Searchable
{
    public static function boot()
    {
        parent::boot();

        $instance = new static;

        $client = $instance->getElasticClient();

        $prefix = config('laraelastic.prefix');

        $indexName = $prefix . $instance->getTable();

        self::created(function ($model) use ($client, $indexName) {
            $model = $model->toArray();

            $params = ['index' => $indexName];

            // This will be executed for first time
            // when the index is not present.
            if (!$client->indices()->exists($params)) {
                $client->indices()->create($params);
            }

            $params = [
                'index' => $indexName,
                'type' => $indexName,
                'id' => $indexName . $model['id'],
                'body' => $model,
            ];

            $client->index($params);
        });

        self::updated(function ($model) use ($client, $indexName) {
            $model = $model->toArray();

            $params = ['index' => $indexName];

            // This will be executed for first time
            // when the index is not present.
            if (!$client->indices()->exists($params)) {
                $client->indices()->create($params);
            }

            $params = [
                'index' => $indexName,
                'type' => $indexName,
                'id' => $indexName . $model['id'],
                'body' => [
                    'doc' => $model,
                ],
            ];

            $client->update($params);
        });

        self::deleted(function ($model) use ($client, $indexName) {
            $model = $model->toArray();

            $params = ['index' => $indexName];

            // This will be executed for first time
            // when the index is not present.
            if (!$client->indices()->exists($params)) {
                $client->indices()->create($params);
            }

            $params = [
                'index' => $indexName,
                'type' => $indexName,
                'id' => $indexName . $model['id'],
            ];

            $client->delete($params);
        });
    }

    public static function reindex()
    {
        $instance = new static;
        $prefix = config('laraelastic.prefix');
        $indexName = $prefix . $instance->getTable();
        $timestamps = $instance->timestamps;

        $client = $instance->getElasticClient();

        self::all()->each(function ($record) use ($client, $indexName, $timestamps) {

            $deleteParams = [
                'index' => $indexName,
                'type' => $indexName,
                'id' => $record->id,
            ];

            try {
                $client->delete($deleteParams);
            } catch (\Exception $exception) {
                logger($exception->getMessage());
            }

            $record = $record->toArray();

            if (is_array($timestamps)) {
                foreach ($timestamps as $key => $timestamp) {
                    $record[$timestamp] = Carbon::parse($record[$timestamp]);
                }
            }

            $params = [
                'index' => $indexName,
                'type' => $indexName,
                'id' => $record['id'],
                'body' => $record,
            ];

            $client->index($params);
        });
    }

    public static function search($string, $formatted = false)
    {
        parent::boot();

        $instance = new static;

        $client = $instance->getElasticClient();

        $prefix = config('laraelastic.prefix');

        $indexName = $prefix . $instance->getTable();

        $searchFields = [];
        if (isset($instance->searchFields)) {
            $searchFields = $instance->searchFields;
        }

        $params = [
            'index' => $indexName,
            'type' => $indexName,
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $string,
                        'fields' => $searchFields,
                    ],
                ],
            ],
        ];

        if ($formatted === true) {
            return $instance->formatSearchResult($client->search($params), $instance);
        }

        return $client->search($params);
    }

    protected function getElasticClient()
    {
        $hosts = config('laraelastic.hosts');

        $client = ClientBuilder::create()
            ->setHosts($hosts)
            ->build();

        return $client;
    }

    protected function formatSearchResult($result)
    {
        $hits = $result['hits']['hits'];

        $data = [];
        foreach ($hits as $hit) {
            $data[] = self::recursive_collect($hit['_source']);
        }

        return $data;
    }

    protected function recursive_collect($array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = self::recursive_collect($value);
                $array[$key] = $value;
            }
        }

        return collect($array);
    }
}
