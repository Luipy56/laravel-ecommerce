<?php

declare(strict_types=1);

namespace App\Scout;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

final class ElasticsearchClientFactory
{
    /**
     * Build an Elasticsearch client from scout.elasticsearch config.
     */
    public static function make(array $config): Client
    {
        $builder = ClientBuilder::create();

        $hosts = $config['hosts'] ?? ['http://127.0.0.1:9200'];
        $builder->setHosts(is_array($hosts) ? $hosts : [$hosts]);

        $apiKey = (string) ($config['api_key'] ?? '');
        if ($apiKey !== '') {
            $builder->setApiKey($apiKey);
        }

        $username = (string) ($config['username'] ?? '');
        $password = (string) ($config['password'] ?? '');
        if ($username !== '' && $password !== '') {
            $builder->setBasicAuthentication($username, $password);
        }

        $retries = (int) ($config['retries'] ?? 2);
        if ($retries >= 0) {
            $builder->setRetries($retries);
        }

        return $builder->build();
    }
}
