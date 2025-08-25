<?php

namespace Drupal\wl_notice_list\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\wl_notice_list\Service\GazetteApiService;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\Pager\PagerParametersInterface;

/**
 * Returns Gazette notices with pagination.
 */
class NoticeController extends ControllerBase {

  protected $apiService;
  protected $dateFormatter;
  protected $pagerManager;
  protected $pagerParams;


  public function __construct(GazetteApiService $apiService, DateFormatterInterface $dateFormatter, PagerManagerInterface $pager_manager, PagerParametersInterface $pager_params) {

    $this->apiService = $apiService;
    $this->dateFormatter = $dateFormatter;
    $this->pagerManager = $pager_manager;
    $this->pagerParams = $pager_params;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('wl_notice_list.api_client'),
      $container->get('date.formatter'),
      $container->get('pager.manager'),
      $container->get('pager.parameters')
    );
  }

  public function listNotices() {
    // Current page from query param.
    $current_page = $this->pagerParams->findPage();
    $limit = 10;
    $offset = $current_page * $limit;

    // Fetch API data.
    $data = $this->apiService->getNotices('https://www.thegazette.co.uk/all-notices/notice/data.json');
    $total = count($data);
    $paged_data = array_slice($data, $offset, $limit);

    // Build render array.
    $items = [];
    foreach ($paged_data as $notice) {
      $items[] = [
        '#markup' => '<article>
          <h2 class="wl-notice-title"><a href="' . $notice['link'] . '" target="_blank" rel="noopener">' . $notice['title'] . '</a></h2>
          <time datetime="' . $notice['date'] . '">' . $this->dateFormatter->format(strtotime($notice['date']), 'custom', 'j F Y') . '</time>
          <p class="wl-notice-content">' . $notice['content'] . '</p>
        </article><hr>',
      ];
    }

    $build['notices'] = [
      '#theme' => 'item_list',
      '#items' => $items,
      '#attributes' => ['class' => ['wl-gazette-notice-list']],
    ];

    // Add Drupal pager.
    $build['pager'] = ['#type' => 'pager'];

    return $build;


  }
}
