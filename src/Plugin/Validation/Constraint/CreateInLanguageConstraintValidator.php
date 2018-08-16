<?php

namespace Drupal\content_translation_access\Plugin\Validation\Constraint;

use Drupal\content_translation_access\AccessControlHandlerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the CommentName constraint.
 */
class CreateInLanguageConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * Validator 2.5 and upwards compatible execution context.
   *
   * @var \Symfony\Component\Validator\Context\ExecutionContextInterface
   */
  protected $context;

  /**
   * User storage handler.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * Access control handler.
   *
   * @var \Drupal\content_translation_access\AccessControlHandlerInterface
   */
  protected $accessControlHandler;

  /**
   * Constructs a new CommentNameConstraintValidator.
   *
   * @param \Drupal\user\UserStorageInterface $user_storage
   *   The user storage handler.
   * @param \Drupal\content_translation_access\AccessControlHandlerInterface $access_control_handler
   *   Access control handler.
   */
  public function __construct(UserStorageInterface $user_storage, AccessControlHandlerInterface $access_control_handler) {
    $this->userStorage = $user_storage;
    $this->accessControlHandler = $access_control_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.manager')
      ->getStorage('user'), $container->get('content_translation_access.access_control_handler'));
  }

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    if ($entity->isNew()) {
      $result = \Drupal::entityTypeManager()
        ->getAccessControlHandler($entity->getEntityTypeId())
        ->createAccess($entity->bundle(), $entity->getRevisionAuthor(), [
          'langcode' => $entity->language()
            ->getId(),
        ], TRUE);
      if ($result->isAllowed() === FALSE) {
        $this->context->buildViolation($constraint->invalidLanguage)
          ->addViolation();
      }
    }
  }

}
