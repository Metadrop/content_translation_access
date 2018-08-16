<?php

namespace Drupal\content_translation_access_test\Plugin\ContentTranslationAccess\LanguageProvider;

use Drupal\content_translation_access\Plugin\LanguageProviderInterface;
use Drupal\Core\Language\Language;

/**
 * @LanguageProvider(
 *   id = "LanguageProviderTest2",
 *   label = "Test language provider 2"
 * )
 */
class TestLanguageProvider2 implements LanguageProviderInterface {

  public function getLanguages() {
    return [
      new Language(['name' => 'de', 'id'=> 'de']),
      new Language(['name' => 'fr', 'id'=> 'fr'])
    ];
  }
}
