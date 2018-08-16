<?php

namespace Drupal\content_translation_access\Plugin;

/**
 * Defines an interface for Language provider plugins.
 */
interface LanguageProviderInterface {

  /**
   * Accessible languages.
   *
   * @return \Drupal\Core\Language\Language
   *   Accessible languages.
   */
  public function getLanguages();

}
