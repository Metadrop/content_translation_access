<?php

namespace Drupal\content_translation_access;

use Drupal\content_translation\ContentTranslationManagerInterface;
use Drupal\content_translation_access\Plugin\LanguageProviderInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the node entity type.
 *
 * @see \Drupal\node\Entity\Node
 * @ingroup node_access
 */
class AccessControlHandler implements AccessControlHandlerInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * The language provider.
   *
   * @var \Drupal\content_translation_access\Plugin\LanguageProviderInterface
   */
  private $languageProvider;

  /**
   * The content translation manager.
   *
   * @var \Drupal\content_translation\ContentTranslationManagerInterface
   */
  private $contentTranslationManager;

  /**
   * AccessControlHandler constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\content_translation_access\Plugin\LanguageProviderInterface $language_provider
   *   The language provider.
   * @param \Drupal\content_translation\ContentTranslationManagerInterface $content_translation_manager
   *   The cta manager.
   */
  public function __construct(LanguageManagerInterface $language_manager, LanguageProviderInterface $language_provider, ContentTranslationManagerInterface $content_translation_manager) {
    $this->languageManager = $language_manager;
    $this->languageProvider = $language_provider;
    $this->contentTranslationManager = $content_translation_manager;
  }

  /**
   * Returns true if the language provider returns the given language.
   *
   * @param \Drupal\Core\Language\Language $language
   *   The language to check.
   *
   * @return bool
   *   True if the language provider returns given language.
   */
  private function hasAssignedLanguage(Language $language) {
    $assigned_languages = $this->languageProvider->getLanguages();
    foreach ($assigned_languages as $assigned_language) {
      if ($assigned_language->getId() == $language->getId()) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function access(EntityInterface $entity, $operation, AccountInterface $account, Language $language = NULL) {
    // Operation == view is not supported right. So return neutral.
    if ($operation == 'view') {
      return AccessResult::neutral();
    }
    $type_id = $entity->getEntityTypeId();

    // If the entity type is not supported return neutral.
    if ($this->contentTranslationManager->isEnabled($type_id, $entity->bundle()) == FALSE) {
      return AccessResult::neutral();
    }
    if ($language == NULL) {
      $language = $entity->language();
    }
    $bundle_id = $entity->bundle();
    if ($this->hasAssignedLanguage($language) && Permissions::hasPermission($operation, $type_id, $bundle_id, $account)) {
      $result = AccessResult::allowed();
    }
    else {
      $result = AccessResult::neutral();
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function createAccess($entity_type_id, $entity_bundle, Language $language, AccountInterface $account) {
    // If the entity type is not supported return neutral.
    if ($this->contentTranslationManager->isEnabled($entity_type_id, $entity_bundle) == FALSE) {
      return AccessResult::neutral();
    }

    if ($this->hasAssignedLanguage($language) && Permissions::hasPermission('create', $entity_type_id, $entity_bundle, $account)) {
      $result = AccessResult::allowed();
    }
    else {
      $result = AccessResult::neutral();
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function createAnyAccess($entity_type_id, $entity_bundle, AccountInterface $account) {
    // If the entity type is not supported return neutral.
    if ($this->contentTranslationManager->isEnabled($entity_type_id, $entity_bundle) == FALSE) {
      return AccessResult::neutral();
    }

    $all_languages = $this->languageManager->getLanguages();
    foreach ($all_languages as $lang) {
      $access = $this->createAccess($entity_type_id, $entity_bundle, $lang, $account);
      if ($access->isAllowed()) {
        return $access;
      }
    }
    $result = AccessResult::neutral();
    return $result;
  }

}
