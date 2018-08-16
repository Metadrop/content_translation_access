<?php

namespace Drupal\Tests\content_translation_access\Kernel;

use Drupal\content_translation_access\AccessControlHandler;
use Drupal\content_translation_access\CTAManager;
use Drupal\content_translation_access\Plugin\LanguageProviderInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\node\Entity\Node;

/**
 * Tests AccessControlHandler.
 *
 * @coversDefaultClass \Drupal\content_translation_access\AccessControlHandler
 *
 * @group content_translation_access
 */
class AccessControlHandlerTest extends EntityKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'system',
    'node',
    'content_translation',
    'content_translation_access',
  ];

  /**
   * The mocked access handler.
   *
   * @var \Drupal\content_translation_access\AccessControlHandler
   */
  private $accessHandler;

  public function setUp() {

    parent::setUp();
    /** @var \Drupal\Core\Language\LanguageManagerInterface $language_manager */
    $language_manager = $this->prophesize(LanguageManagerInterface::class);
    $language_manager->getLanguages()->willReturn([
      new Language(['name' => 'de', 'id' => 'de']),
      new Language(['name' => 'en', 'id' => 'en']),
    ]);

    /** @var \Drupal\content_translation_access\Plugin\LanguageProviderInterface $language_provider */
    $language_provider = $this->prophesize(LanguageProviderInterface::class);
    $language_provider->getLanguages()
      ->willReturn([new Language(['name' => 'en', 'id' => 'en'])]);
    $this->accessHandler = new AccessControlHandler($language_manager->reveal(), $language_provider->reveal(), new CTAManager());

  }

  /**
   * Test AccessControlHandler::testAccess.
   *
   * @covers ::access()
   */
  public function testAccess() {

    // Create the article node type with revisions disabled.
    $user = $this->createUser(['uid' => 2], [
      'administer nodes',
      'update assigned language node page content',
    ]);
    $node_en = Node::create([
      'title' => $this->randomMachineName(8),
      'uid' => $user->id(),
      'type' => 'page',
    ]);
    $node_en->save();

    $handler = $this->accessHandler;
    /** @var \Drupal\Core\Access\AccessResultInterface $access */
    $access = $handler->access($node_en, 'update', $user);
    $this->assertEquals(TRUE, $access->isAllowed());

    $access = $handler->access($node_en, 'delete', $user);
    $this->assertEquals(TRUE, $access->isNeutral());

    $access = $handler->access($node_en, 'view', $user);
    $this->assertEquals(TRUE, $access->isNeutral());
  }

  /**
   * Test AccessControlHandler::testCreateAccess.
   *
   * @covers ::createAccess()
   */
  public function testCreateAccess() {

    // Create the article node type with revisions disabled.
    $user = $this->createUser(['uid' => 2], [
      'administer nodes',
      'create assigned language node page content',
    ]);

    $en = new Language(['name' => 'en', 'id' => 'en']);
    $handler = $this->accessHandler;
    /** @var \Drupal\Core\Access\AccessResultInterface $access */
    $access = $handler->createAccess('node', 'page', $en, $user);
    $this->assertEquals(TRUE, $access->isAllowed());
  }

  /**
   * Test AccessControlHandler::testCreateAnyAccess.
   *
   * @covers ::createAnyAccess()
   */
  public function testCreateAnyAccess() {

    // Create the article node type with revisions disabled.
    $user = $this->createUser(['uid' => 2], [
      'administer nodes',
      'create assigned language node page content',
    ]);

    $handler = $this->accessHandler;
    /** @var \Drupal\Core\Access\AccessResultInterface $access */
    $access = $handler->createAnyAccess('node', 'page', $user);
    $this->assertEquals(TRUE, $access->isAllowed());
  }

  /**
   * Test AccessControlHandler::testEntityCreateAccess.
   *
   * @covers ::entityCreateAccess()
   */
  public function testEntityCreateAccess() {

    // Create the article node type with revisions disabled.
    $user = $this->createUser(['uid' => 2], [
      'administer nodes',
      'create assigned language node page content',
    ]);

    $handler = $this->accessHandler;
    /** @var \Drupal\Core\Access\AccessResultInterface $access */
    $access = $handler->entityCreateAccess('node', 'page', $user);
    $this->assertEquals(TRUE, $access->isAllowed());
  }

}
