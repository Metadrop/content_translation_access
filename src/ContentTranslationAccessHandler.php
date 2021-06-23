<?php

namespace Drupal\content_translation_access;

use Drupal\content_translation\ContentTranslationHandler;
use Drupal\content_translation\ContentTranslationManager;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Class LocalContentTranslationHandler.
 *
 * Extends the "Hide non translatable fields on translation forms" option by
 * hiding non translatable fields on translation forms
 * when the user is missing the permission.
 *
 * @ingroup entity_api
 */
class ContentTranslationAccessHandler extends ContentTranslationHandler {

  /**
   * {@inheritdoc}
   */
  public function entityFormAlter(array &$form, FormStateInterface $form_state, EntityInterface $entity) {
    parent:: entityFormAlter($form, $form_state, $entity);

    $form_object = $form_state->getFormObject();
    $form_langcode = $form_object->getFormLangcode($form_state);
    $source_langcode = $this->getSourceLangcode($form_state);

    $new_translation = !empty($source_langcode);
    $translations = $entity->getTranslationLanguages();
    if ($new_translation) {
      // Make sure a new translation does not appear as existing yet.
      unset($translations[$form_langcode]);
    }
    $has_translations = count($translations) > 1;

    if ($new_translation || $has_translations) {
      // Add the form process function.
      $form['#process'][] = [$this, 'hideNonTranslatableFieldsWithPermission'];

      // Unset process that hide non translatable fields.
      foreach ($form['#process'] as $key => $value) {
        if (is_array($value) && $value[0] instanceof ContentTranslationAccessHandler && $value[1] == 'entityFormSharedElements') {
          unset($form['#process'][$key]);
        }

      }
    }

  }

  /**
   * Process callback: Hide non translatable fields if missing permission.
   *
   * @see \Drupal\content_translation_access_user\ContentTranslationAccessHandler::entityFormAlter()
   */
  public function hideNonTranslatableFieldsWithPermission($element, FormStateInterface $form_state, $form) {
    static $ignored_types;

    // @todo Find a more reliable way to determine if a form element concerns a
    //   multilingual value.
    if (!isset($ignored_types)) {
      $ignored_types = array_flip(['actions',
        'value',
        'hidden',
        'vertical_tabs',
        'token',
        'details',
      ]);
    }

    /** @var \Drupal\Core\Entity\ContentEntityForm $form_object */
    $form_object = $form_state->getFormObject();
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $form_object->getEntity();
    /** @var \Drupal\content_translation\ContentTranslationManagerInterface $content_translation_manager */
    $content_translation_manager = \Drupal::service('content_translation.manager');
    $settings = $content_translation_manager->getBundleTranslationSettings($entity->getEntityTypeId(), $entity->bundle());
    $hide_translastion_fields = (!empty($settings['untranslatable_fields_hide_with_permission']) || ContentTranslationManager::isPendingRevisionSupportEnabled($entity->getEntityTypeId(), $entity->bundle()))
                                && !$this->currentUser->hasPermission('show entity non translatable fields');
    $hide_untranslatable_fields = $hide_translastion_fields && !$entity->isDefaultTranslation();
    $translation_form = $form_state->get(['content_translation', 'translation_form']);

    // We use field definitions to identify untranslatable field widgets to be
    // hidden. Fields that are not involved in translation changes checks should
    // not be affected by this logic (the "revision_log" field, for instance).
    $field_definitions = array_diff_key($entity->getFieldDefinitions(), array_flip($this->getFieldsToSkipFromTranslationChangesCheck($entity)));

    foreach (Element::children($element) as $key) {

      if (!isset($element[$key]['#type'])) {
        $this->hideNonTranslatableFieldsWithPermission($element[$key], $form_state, $form);
      }
      else {
        // Add (all languages) clue.
        if (empty($element[$key]['#multilingual']) && !$translation_form) {
          $this->addTranslatabilityClue($element[$key]);
        }
        // Ignore non-widget form elements.
        if (isset($ignored_types[$element[$key]['#type']])) {
          continue;
        }
        // Elements are considered to be non multilingual by default.
        if (empty($element[$key]['#multilingual']) && !$translation_form && $hide_untranslatable_fields && isset($field_definitions[$key])) {
          // Hide widgets for untranslatable fields.
          $element[$key]['#access'] = FALSE;
        }
      }
    }

    return $element;
  }

}
