<?php

namespace Drupal\content_translation_access\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Content translation access language provider annotation object.
 *
 * @see \Drupal\content_translation_access\LanguageProviderManager
 * @see plugin_api
 *
 * @Annotation
 */
class LanguageProvider extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
