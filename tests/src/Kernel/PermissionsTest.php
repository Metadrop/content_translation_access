<?php

namespace Drupal\Tests\content_translation_access\Kernel;

use Drupal\content_translation_access\AccessControlHandler;
use Drupal\content_translation_access\Permissions;
use Drupal\content_translation_access\Plugin\LanguageProviderInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests AccessControlHandler.
 *
 * @coversDefaultClass \Drupal\content_translation_access\Permissions
 *
 * @group content_translation_access
 */
class PermissionsTest extends EntityKernelTestBase {

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
   * Test CTAPermissions::hasPermission.
   *
   * @covers ::hasPermission()
   */
  public function testHasPermission() {

    // Create the article node type with revisions disabled.
    $user = $this->createUser(['uid' => 2], ['administer nodes', 'update assigned language node page content']);

    $node_en = Node::create([
      'title' => $this->randomMachineName(8),
      'uid' => $user->id(),
      'type' => 'page',
    ]);
    $node_en->save();

    $has_permission = Permissions::hasPermission('update', 'node', 'page', $user);
    $this->assertEquals(TRUE, $has_permission);

  }

}
