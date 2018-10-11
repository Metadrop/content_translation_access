<?php

namespace Drupal\content_translation_access;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for assigned language.
 */
interface AccessControlHandlerInterface {

  /**
   * Checks access to an operation on a given entity or entity translation.
   *
   * Use \Drupal\Core\Entity\EntityAccessControlHandlerInterface::createAccess()
   * to check access to create an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to check access.
   * @param string $operation
   *   The operation access should be checked for.
   *   Usually one of "view", "view label", "update" or "delete".
   * @param \Drupal\Core\Session\AccountInterface $account
   *   (optional) The user session for which to check access, or NULL to check
   *   access for the current user. Defaults to NULL.
   * @param \Drupal\Core\Language\Language $language
   *   The language.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(EntityInterface $entity, $operation, AccountInterface $account, Language $language = NULL);

  /**
   * Checks access to create an entity based on assigned language.
   *
   * @param string $entity_type_id
   *   The type of the entity.
   * @param string $entity_bundle
   *   The bundle of the entity. Required if the entity supports
   *   bundles, defaults to NULL otherwise.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   (optional) The user session for which to check access, or NULL to check
   *   access for the current user. Defaults to NULL.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function createAnyAccess($entity_type_id, $entity_bundle, AccountInterface $account);

  /**
   * Checks access to create an entity based on assigned language.
   *
   * @param string $entity_type_id
   *   The type of the entity.
   * @param string $entity_bundle
   *   The bundle of the entity. Required if the entity supports
   *   bundles, defaults to NULL otherwise.
   * @param \Drupal\Core\Language\Language $language
   *   The language.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   (optional) The user session for which to check access, or NULL to check
   *   access for the current user. Defaults to NULL.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function createAccess($entity_type_id, $entity_bundle, Language $language, AccountInterface $account);

}
