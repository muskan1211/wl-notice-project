<?php

namespace Drupal\wl_notice_list\Service;

use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Fetches data from The Gazette API.
 */
class GazetteApiService {

  protected $httpClient;
  protected $logger;

  public function __construct(ClientInterface $http_client, LoggerInterface $logger) {
    $this->httpClient = $http_client;
    $this->logger = $logger;
  }

  public function getNotices($url) {

    try {
      $response = $this->httpClient->request('GET', $url, ['timeout' => 5]);
      $data = json_decode($response->getBody(), TRUE);
      $results = [];
      if (!empty($data['entry'])) {
        foreach ($data['entry'] as $notice) {
          $results[] = [
            'title' => $notice['title'] ?? 'Untitled',
            'link' => $notice['id'] ?? '#',
            'date' => $notice['published'] ?? '',
            'content' => strip_tags($notice['content'] ?? ''),
          ];
        }
      }
      return $results;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to fetch Gazette API: @msg', ['@msg' => $e->getMessage()]);
      return [];
    }
  }
}
