<?php

namespace Drupal\content_translation_access;

use Drupal\content_translation\ContentTranslationManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityType;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions for all entity types.
 */
class Permissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The content translation manager.
   *
   * @var \Drupal\content_translation\ContentTranslationManagerInterface
   */
  private $contentTranslationManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * CTAPermissions constructor.
   *
   * @param \Drupal\content_translation\ContentTranslationManagerInterface $content_translation_manager
   *   The content translation manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The content translation manager.
   */
  public function __construct(ContentTranslationManagerInterface $content_translation_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->contentTranslationManager = $content_translation_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('content_translation.manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Returns an array of node type permissions.
   *
   * @return array
   *   The node type permissions.
   */
  public function ctaPermissions() {
    $perms = [];
    $valid_entity_types = $this->contentTranslationManager->getSupportedEntityTypes();
    // Generate node permissions for all entity types.
    foreach ($valid_entity_types as $entity_type) {
      $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo($entity_type->id());
      foreach ($bundles as $bundle_id => $bundle_lable) {
        if ($this->contentTranslationManager->isEnabled($entity_type->id(), $bundle_id)) {
          $perms += $this->buildPermissions($entity_type, $bundle_id, $bundle_lable['label']) + [
            "cta translate any entity" => [
              'title' => $this->t('Translate any entity (with assigned language)'),
            ]
          ];
        }
      }
    }

    return $perms;
  }

  /**
   * Returns true if the user has the language assign permission.
   *
   * @param string $operation
   *   The operation create update delete.
   * @param string $entity_type_id
   *   The entity type id.
   * @param string $bundle_id
   *   The entity bundle id.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account.
   *
   * @return bool
   *   Return true if user has permission.
   */
  public static function hasPermission($operation, $entity_type_id, $bundle_id, AccountInterface $account) {
    if ($account->hasPermission('bypass node access') || $account->hasPermission('cta translate any entity')) {
      return TRUE;
    }

    if ($operation == 'update') {
      $operation = 'translate';
    }

    if ($operation == 'create') {
      $operation = 'create translation';
    }

    if ($operation == 'delete') {
      $operation = 'delete translation';
    }
    return $account->hasPermission("cta $operation $entity_type_id $bundle_id");
  }

  /**
   * Returns a list of node permissions for a given node type.
   *
   * @param \Drupal\Core\Entity\EntityType $entity_type
   *   The entity type.
   * @param string $bundle_id
   *   The bundle id.
   * @param string $bundle_label
   *   The bundle label.
   *
   * @return array
   *   An associative array of permission names and descriptions.
   */
  protected function buildPermissions(EntityType $entity_type, $bundle_id, $bundle_label) {
    $type_id = $entity_type->id();
    $type_label = $entity_type->getLabel();

    $type_params = [
      '%type_id' => $type_id,
      '%type_label' => $type_label,
      '%bundle_id' => $bundle_id,
      '%bundle_label' => $bundle_label,
    ];

    return [
      "cta create translation $type_id $bundle_id" => [
        'title' => $this->t('Create %type_label %bundle_label (with assigned language)', $type_params),
      ],
      "cta translate $type_id $bundle_id" => [
        'title' => $this->t('Translate %type_label %bundle_label (with assigned language)', $type_params),
      ],
      "cta delete translation $type_id $bundle_id" => [
        'title' => $this->t('Delete translation %type_label %bundle_label (with assigned language)', $type_params),
      ],
    ];
  }

}
