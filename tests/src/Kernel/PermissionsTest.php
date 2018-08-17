<?php

namespace Drupal\Tests\content_translation_access\Kernel;

use Drupal\content_translation_access\AccessControlHandler;
use Drupal\content_translation_access\Permissions;
use Drupal\content_translation_access\Plugin\LanguageProviderInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\KernelTests\KernelTestBase;
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
   * Test Permissions::hasPermission.
   *
   * @covers ::hasPermission()
   */
  public function testHasPermission() {

    // Create the article node type with revisions disabled.
    $user = $this->createUser(['uid' => 2], ['administer nodes', 'translate cta node page']);

    $has_permission = Permissions::hasPermission('update', 'node', 'page', $user);
    $this->assertEquals(TRUE, $has_permission);

  }

}
