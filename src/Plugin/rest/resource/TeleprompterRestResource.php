<?php

namespace Drupal\teleprompter\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a resource to create a Question Entity.
 *
 * @RestResource(
 *   id = "teleprompter_rest_resource",
 *   label = @Translation("Teleprompter rest resource"),
 *   uri_paths = {
 *     "create" = "/api/v1/teleprompter/question"
 *   }
 * )
 */
class TeleprompterRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->logger = $container->get('logger.factory')->get('teleprompter');
    $instance->currentUser = $container->get('current_user');
    return $instance;
  }

  /**
   * Responds to POST requests to create a question node.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Throws exception if user has not got permission to create questions content.
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   Throws exception if data provided is not valid.
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   Throws exception unable to save question node.
   */
  public function post(Request $request) {
    if (!$this->currentUser->hasPermission('create questions content')) {
      throw new AccessDeniedHttpException();
    }

    $post_body = Json::decode($request->getContent());

    // Validate data
    if (count(array_keys($post_body)) > 1 || !isset($post_body['question'])) {
      throw new BadRequestHttpException('POST data must only contain the question field.');
    }

    if (!$this->questionIsValid($post_body['question'])) {
      throw new BadRequestHttpException('Question text is not valid.');
    }

    // Create the content
    \Drupal\node\Entity\Node::create([
      'title' => $post_body['question'],
      'field_question_status' => 'show',
      'type' => 'questions'
    ])->save();


    return new ModifiedResourceResponse('Question created', 201);
  }

  /**
   * Validate a question field.
   * 
   * @param string $question
   * 
   * @return boolean
   *   Does the field meet validation criteria.
   */
  protected function questionIsValid($question) {
    // Does question contains anything other than alpha characters, spaces and a question marks.
    if (preg_match('/[^A-Za-z \?]+/', $question)) {
      return FALSE;
    }

    if (strlen($question) > 255) {
      return FALSE;
    }

    // Ensure there is only one question mark and it's the last character
    if (substr($question, -1) !== '?' || substr_count($question, '?') !== 1) {
      return FALSE;
    }

    return TRUE;
  }

}
