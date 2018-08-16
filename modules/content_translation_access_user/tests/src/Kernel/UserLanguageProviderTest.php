<?php

namespace Drupal\Tests\content_translation_access_user\Kernel;

use Drupal\content_translation_access_user\Plugin\ContentTranslationAccess\LanguageProvider\UserLanguageProvider;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageInterface;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;

/**
 * @coversDefaultClass \Drupal\content_translation_access_user\
 *
 * @group content_translation_access
 */
class UserLanguageProviderTest extends EntityKernelTestBase {

  public function setUp() {
    parent::setUp();
    $this->installEntitySchema('configurable_language');
    $this->installConfig([
      'user',
      'content_translation_access_user'
    ]);

  }

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'content_translation_access',
    'content_translation_access_user',
    'language',
    'content_translation',
  ];

  /**
   * Test LanguageProviderManager::getLanguages.
   *
   * @covers ::getLanguages()
   */
  public function testGetLanguages() {

    $user = $this->createUser(['uid' => 2], [
      'administer nodes',
      'update assigned language node page content',
    ]);
    $en = ConfigurableLanguage::create(['id' => 'en']);
    $this->assertNotEmpty($en);

    $user->field_access_languages->set(0, $en);

    $user->save();

    $provider = new UserLanguageProvider($user);
    $languages = $provider->getLanguages();
    foreach ($languages as $language) {
      $this->assertNotEmpty($language);
      $this->assertEquals($en, $language);
    }
  }
}
