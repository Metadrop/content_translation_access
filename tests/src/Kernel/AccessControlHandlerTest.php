<?php

namespace Drupal\Tests\content_translation_access\Kernel;

use Drupal\Core\Language\Language;
use Drupal\entity_test\Entity\EntityTestMul;
use Drupal\node\Entity\Node;

/**
 * Tests AccessControlHandler.
 *
 * @coversDefaultClass \Drupal\content_translation_access\AccessControlHandler
 *
 * @group content_translation_access
 */
class AccessControlHandlerTest extends ContentTranslationAccessKernelTestBase {
  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'node',
  ];

  /**
   * Test AccessControlHandler::testAccess.
   *
   * @covers ::access()
   */
  public function testAccess() {

    // Create the article node type with revisions disabled.
    $user = $this->createUser(['uid' => 2], [
      'administer nodes',
      'cta translate node page',
      'cta delete translation node page',
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
    $this->assertEquals(TRUE, $access->isAllowed());

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
      'cta translate node page',
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
      'cta translate node page',
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
      'cta translate node page',
    ]);

    $handler = $this->accessHandler;
    /** @var \Drupal\Core\Access\AccessResultInterface $access */
    $access = $handler->entityCreateAccess('node', 'page', $user);
    $this->assertEquals(TRUE, $access->isAllowed());
  }

}
