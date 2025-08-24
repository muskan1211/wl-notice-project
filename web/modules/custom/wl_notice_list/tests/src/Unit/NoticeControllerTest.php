<?php

namespace Drupal\Tests\wl_notice_list\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\wl_notice_list\Controller\NoticeController;
use Drupal\wl_notice_list\Service\GazetteApiService;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\Pager\PagerParametersInterface;

/**
 * Unit test for NoticeController.
 *
 * @group wl_notice_list
 */
class NoticeControllerTest extends UnitTestCase {

  /**
   * Tests listNotices() returns expected render array.
   */
  public function testListNotices() {
    // Mock data returned by API service.
    $mockData = [
      [
        'title' => 'Test Notice',
        'link' => 'https://example.com',
        'date' => '2025-08-24',
        'content' => 'Sample content.',
      ],
    ];

    // Mock GazetteApiService.
    $apiService = $this->createMock(GazetteApiService::class);
    $apiService->method('getNotices')
      ->willReturn($mockData);

    // Mock DateFormatterInterface.
    $dateFormatter = $this->createMock(DateFormatterInterface::class);
    $dateFormatter->method('format')
      ->willReturn('24 August 2025');

      //Mock Pager parameters

      $pager_manager = $this->createMock(PagerManagerInterface::class);
       $pager_params = $this->createMock(PagerParametersInterface::class);

    

    // Instantiate controller with mocks.
    $controller = new NoticeController($apiService, $dateFormatter,$pager_manager, $pager_params);

    // Call the method.
    $result = $controller->listNotices();

    // Assertions: Ensure structure & content are correct.
    $this->assertArrayHasKey('notices', $result);
    $this->assertArrayHasKey('#items', $result['notices']);
    $this->assertStringContainsString('Test Notice', $result['notices']['#items'][0]['#markup']);
    $this->assertStringContainsString('https://example.com', $result['notices']['#items'][0]['#markup']);
    $this->assertArrayHasKey('pager', $result);
    $this->assertEquals('pager', $result['pager']['#type']);
  }

}
