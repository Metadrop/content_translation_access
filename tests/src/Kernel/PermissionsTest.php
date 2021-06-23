<?php

namespace Drupal\Tests\content_translation_access\Kernel;

use Drupal\content_translation_access\Permissions;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

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
    $user = $this->createUser(['uid' => 2], ['administer nodes', 'cta translate node page']);

    $has_permission = Permissions::hasPermission('update', 'node', 'page', $user);
    $this->assertEquals(TRUE, $has_permission);

  }

}
