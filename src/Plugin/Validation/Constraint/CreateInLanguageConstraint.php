<?php

namespace Drupal\content_translation_access\Plugin\Validation\Constraint;

use Drupal\Core\Entity\Plugin\Validation\Constraint\CompositeConstraintBase;

/**
 * Supports validating entity creating.
 *
 * @Constraint(
 *   id = "CreateInLanguage",
 *   label = @Translation("Create in language", context = "Validation"),
 *   type = "entity"
 * )
 */
class CreateInLanguageConstraint extends CompositeConstraintBase {

  /**
   * Message shown when the user has no access to this language.
   *
   * @var string
   */
  public $invalidLanguage = 'Not allowed to create this entity in this language';

  /**
   * {@inheritdoc}
   */
  public function coversFields() {
    return [];
  }

}
