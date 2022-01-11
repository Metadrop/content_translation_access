<?php

namespace Drupal\content_translation_access\Controller;

use Drupal\content_translation_access\Permissions;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\content_translation\Controller\ContentTranslationController;
use Drupal\user\Entity\User;

/**
 * Base class for entity translation controllers.
 */
class AllowedLanguagesController extends ContentTranslationController {

  /**
   * Override overview method defined in ContentTranslationController.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param string $entity_type_id
   *   (optional) The entity type ID.
   *
   * @return array
   *   Array of page elements to render.
   */
  public function overview(RouteMatchInterface $route_match, $entity_type_id = NULL) {
    $build = parent::overview($route_match, $entity_type_id);
    $user = $this->currentUser();
    $user_entity = User::load($user->id());
    $entity = $build["#entity"];
    $bundle_id = $entity->bundle();
    // Map with operations add = "create", edit = "", delete = "delete".
    // Empty value is edit translate.
    $permissions = ['add' => 'create', 'edit' => 'update', 'delete' => 'delete'];

    foreach ($permissions as $operation => $permission) {
      $allow_operations[$operation] = Permissions::hasPermission($permission, $entity_type_id, $bundle_id, $user);
    }

    if (!$user->hasPermission('translate all languages') && $user_entity->hasRole("local_editor") && !empty($build['content_translation_overview']['#rows'])) {
      $rows = &$build['content_translation_overview']['#rows'];
      $languages = $this->languageManager()->getLanguages();

      $allowed_languages = [];
      $tmp_allowed_languages = $user_entity->hasField('field_access_languages') ? $user_entity->field_access_languages->getValue() : [];
      foreach ($tmp_allowed_languages as $langcode) {
        $allowed_languages[] = $langcode["target_id"];
      }
      // Index of a row with the language in the parent output.
      $i = 0;
      // Parent overview() method does the same loop through available languages.
      foreach ($languages as $language) {
        $options = ['language' => $language];
        $fix_urls = [
          "edit" => $entity->toUrl('edit-form', $options),
          "delete" => $entity->toUrl('delete-form', $options),
        ];
        // $delete_url = $entity->toUrl('drupal:content-translation-delete', $options);
        $target_row = $rows[$i];
        // Row with operations will always be the last. See parent method.
        end($target_row);
        $operations_key = key($target_row);
        // If the user is not allowed to manage entities in this language.
        if (!in_array($language->getId(), $allowed_languages)) {
          // Unset operations element in case if user can't edit entities in this language.
          unset($rows[$i][$operations_key]['data']["#links"]);
        }
        else {
          foreach ($rows[$i][$operations_key]['data']["#links"] as $operation => $operation_link) {
            $allow = $allow_operations[$operation];
            if (!$allow) {
              // Unset operations element in case if user can't edit entities in this language.
              unset($rows[$i][$operations_key]['data']["#links"][$operation]);
            }
            elseif (array_key_exists($operation, $fix_urls)) {
              $rows[$i][$operations_key]['data']["#links"][$operation]["url"] = $fix_urls[$operation];
            }
          }

          if (empty($rows[$i][$operations_key]['data']["#links"])) {
            unset($rows[$i][$operations_key]['data']);
          }
        }

        // Increment the language index.
        $i++;
      }
    }

    return $build;

  }

}
