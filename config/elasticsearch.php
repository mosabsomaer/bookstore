<?php
// config/elasticsearch.php

return [
    'hosts' => [
        env('ELASTICSEARCH_HOST', 'localhost:9200'),
    ],
];
