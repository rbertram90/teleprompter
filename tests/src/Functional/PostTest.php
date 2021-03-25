<?php

namespace Drupal\Tests\teleprompter\Functional;

use Drupal\Core\Url;
use Drupal\Tests\rest\Functional\ResourceTestBase;
use Drupal\Tests\rest\Functional\BasicAuthResourceTestTrait;

/**
 * Test to ensure that a question can be posted.
 *
 * @group teleprompter
 */
class PostTest extends ResourceTestBase {

  use BasicAuthResourceTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected $profile = 'standard';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['node', 'user', 'options', 'menu_ui', 'path', 'teleprompter'];

  /**
   * The authentication mechanism to use in this test.
   *
   * @var string
   */
  protected static $auth = 'basic';

  /**
   * CSRF Token.
   *
   * @var string
   */
  protected $token;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $response = $this->request('GET', Url::fromRoute('system.csrftoken'), []);
    $this->token = $response->getBody()->getContents();
  }

  /**
   * {@inheritdoc}
   */
  protected function setUpAuthorization($method) {
    switch ($method) {
      case 'POST':
        $this->grantPermissionsToTestedRole(['create questions content']);
        break;

      default:
        throw new \UnexpectedValueException();
    }
  }

  /**
   * Tests that the API call comes back with a with a 201 response.
   */
  public function testLoad() {
    $url = Url::fromUserInput('/api/v1/teleprompter/question');
    $options = array_merge_recursive($this->getAuthenticationRequestOptions('POST'), [
      'body' => '{ question: "Is this a good functional test?" }',
      'headers' => [
        'Content-Type' => 'application/json',
        'X-CSRF-Token' => $this->token,
      ]
    ]);

    $response = $this->request('POST', $url, $options);
    $this->assertResourceResponse(201, 'Question created', $response);
  }

  /**
   * {@inheritdoc}
   */
  protected function assertNormalizationEdgeCases($method, Url $url, array $request_options) {}

  /**
   * {@inheritdoc}
   */
  protected function getExpectedUnauthorizedAccessMessage($method) {}

  /**
   * {@inheritdoc}
   */
  protected function getExpectedUnauthorizedAccessCacheability() {}

}
