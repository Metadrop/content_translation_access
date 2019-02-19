<?php

namespace Drupal\content_translation_access\Plugin\Field\FieldWidget;

use Drupal\content_translation_access\AccessControlHandlerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\LanguageSelectWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'Content Access Translation Language' widget.
 *
 * @FieldWidget(
 *   id = "cta_language_select",
 *   label = @Translation("CTA Language select"),
 *   field_types = {
 *     "language"
 *   }
 * )
 */
class CTALanguageSelectWidget extends LanguageSelectWidget implements ContainerFactoryPluginInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * The content translation manager.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  private $currentUser;

  /**
   * The access control handler.
   *
   * @var \Drupal\content_translation_access\AccessControlHandlerInterface
   */
  private $accessControlHandler;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\language\DefaultLanguageItem $item */

    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $languages = $this->languageManager->getLanguages();
    $item = $items[$delta];
    $entity = $item->getEntity();
    $options = [];

    // Check if we are on a entity form. Otherwise show all languages.
    if ($entity != NULL && $entity instanceof Entity) {

      /** @var \Drupal\Core\Language\Language $language */
      foreach ($languages as $language) {
        $result = AccessResult::neutral();
        if ($entity->isNew()) {
          // On create page check if the user has access
          // to create this page in this language.
          $result = \Drupal::entityTypeManager()
            ->getAccessControlHandler($entity->getEntityTypeId())
            ->createAccess($entity->bundle(), $this->currentUser->getAccount(), ['langcode' => $language->getId()], TRUE);
        }
        else {
          // On edit page:
          if ($entity instanceof TranslatableInterface) {
            // Allow current edited languge.
            if ($entity->language()->getId() == $language->getId()) {
              $result = AccessResult::allowed();
            }
            else {
              if ($entity->hasTranslation($language->getId()) == FALSE) {
                // For pages in which the node is not translated
                // we check if the user can translate in this language.
                $result = \Drupal::entityTypeManager()
                  ->getAccessControlHandler($entity->getEntityTypeId())
                  ->createAccess($entity->bundle(), $this->currentUser->getAccount(), ['langcode' => $language->getId()], TRUE);
              }
              else {
                if ($entity->hasTranslation($language->getId()) == TRUE) {
                  // For pages in which the node is translated
                  // we check if update permissions.
                  $translated_entity = $entity->getTranslation($language->getId());
                  $result = \Drupal::entityTypeManager()
                    ->getAccessControlHandler($entity->getEntityTypeId())
                    ->access($translated_entity, 'update', $this->currentUser->getAccount(), TRUE);

                }
              }
            }
          }
        }

        if ($result->isAllowed()) {
          $options[$language->getId()] = $language->getName();
        }
      }
      $element['value']['#options'] = $options;
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, LanguageManagerInterface $language_manager, AccessControlHandlerInterface $access_control_handler, EntityTypeManagerInterface $entity_type_manager, AccountProxyInterface $current_user) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);

    $this->languageManager = $language_manager;
    $this->accessControlHandler = $access_control_handler;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('language_manager'),
      $container->get('content_translation_access.access_control_handler'),
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

}
