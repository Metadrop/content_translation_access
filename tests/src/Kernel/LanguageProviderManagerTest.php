<?php

namespace Drupal\Tests\content_translation_access\Kernel;

use Drupal\Core\Language\Language;

/**
 * @coversDefaultClass \Drupal\content_translation_access\LanguageProviderManager
 *
 * @group content_translation_access
 */
class LanguageProviderManagerTest extends ContentTranslationAccessKernelTestBase {
  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'content_translation_access_test',
  ];

  /**
   * Test LanguageProviderManager::getLanguages.
   *
   * @covers ::getLanguages()
   */
  public function testGetLanguages() {
    /** @var \Drupal\content_translation_access\LanguageProviderManager $language_provider_manager */
    $language_provider_manager = \Drupal::service('plugin.manager.content_translation_access_language_provider');
    $languages = $language_provider_manager->getLanguages();
    $this->assertEquals(3, count($languages));
    foreach ($languages as $language) {
      $this->assertInstanceOf(Language::class, $language);
    }
  }

}
