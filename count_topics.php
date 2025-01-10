<?php

use GuzzleHttp\Client;

/*
 * This file closes the topic for today
 */

require_once __DIR__ . '/bootstrap.php';

$guzzle = new Client([
    'base_uri' => $_ENV['ZULIP_URL'],
    'auth' => [$_ENV['ZULIP_USERNAME'], $_ENV['ZULIP_API_KEY']],
    'headers' => [
        'User-Agent' => 'Zulip-Messenger',
    ],
]);

$response = $guzzle->get('/api/v1/streams');
$channels = array_map(function ($channel) {
    return $channel->stream_id;
}, json_decode($response->getBody()->getContents())->streams);


$topics = array_map(function($channelId) use ($guzzle) {
    $response = $guzzle->get('/api/v1/users/me/' . $channelId . '/topics');
    return array_map(function ($topic) {
        return $topic->name;
    }, json_decode($response->getBody()->getContents())->topics);
}, $channels);

$topics = array_merge(...$topics);

$completedTopics = count(array_filter($topics, function ($topic) {
    return str_starts_with($topic, '✔');
}));

$incompleteTopics = count($topics) - $completedTopics;

var_dump($completedTopics, $incompleteTopics);
