<?php

namespace Drupal\content_translation_access\Plugin;

/**
 * Defines an interface for Language provider plugins.
 */
interface LanguageProviderInterface {

  /**
   * Accessible languages.
   *
   * @return array|\Drupal\Core\Language\Language|null
   *   Accessible languages.
   */
  public function getLanguages();

}
