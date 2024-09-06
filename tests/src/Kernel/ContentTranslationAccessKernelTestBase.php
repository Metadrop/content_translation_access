<?php

namespace Drupal\Tests\content_translation_access\Kernel;

use Drupal\content_translation\ContentTranslationManagerInterface;
use Drupal\content_translation_access\AccessControlHandler;
use Drupal\content_translation_access\Plugin\LanguageProviderInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\KernelTests\Core\Entity\EntityLanguageTestBase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Base controller.
 *
 * @group content_translation_access
 */
class ContentTranslationAccessKernelTestBase extends EntityLanguageTestBase {

  use ProphecyTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'system',
    'user',
    'entity_test',
    'language',
    'content_translation',
    'content_translation_test',
    'content_translation_access',
  ];

  /**
   * The mocked access handler.
   *
   * @var \Drupal\content_translation_access\AccessControlHandler
   */
  protected $accessHandler;

  /**
   *
   */
  public function setUp() {
    parent::setUp();

    /** @var \Drupal\Core\Language\LanguageManagerInterface $language_manager */
    $language_manager = $this->prophesize(LanguageManagerInterface::class);
    $language_manager->getLanguages()->willReturn([
      new Language(['name' => 'de', 'id' => 'de']),
      new Language(['name' => 'en', 'id' => 'en']),
    ]);

    /** @var \Drupal\content_translation\ContentTranslationManager $content_translation_handler */
    $content_translation_handler = $this->prophesize(ContentTranslationManagerInterface::class);
    $content_translation_handler->isEnabled("node", "page")
      ->willReturn([TRUE]);

    /** @var \Drupal\content_translation_access\Plugin\LanguageProviderInterface $language_provider */
    $language_provider = $this->prophesize(LanguageProviderInterface::class);
    $language_provider->getLanguages()
      ->willReturn([new Language(['name' => 'en', 'id' => 'en'])]);
    $this->accessHandler = new AccessControlHandler($language_manager->reveal(), $language_provider->reveal(), $content_translation_handler->reveal());

  }

}
