<?php

namespace Drupal\content_translation_access_user\Plugin\ContentTranslationAccess\LanguageProvider;

use Drupal\content_translation_access\Plugin\LanguageProviderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns languages from user field field_access_languages.
 *
 * @LanguageProvider(
 *   id = "user_language_provider",
 *   label = "User language provider"
 * )
 */
class UserLanguageProvider implements LanguageProviderInterface, ContainerFactoryPluginInterface {

  private $languages = NULL;
  /**
   * The current user.
   *
   * @var \Drupal\user\Entity\User
   */
  private $currentUser;

  /**
   * UserLanguageProvider constructor.
   *
   * @param \Drupal\user\Entity\User $current_user
   *   The current user.
   */
  public function __construct(User $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguages() {
    if ($this->languages !== NULL) {
      return $this->languages;
    }
    $this->languages = [];
    if ($this->currentUser->hasField('field_access_languages')) {
      foreach ($this->currentUser->field_access_languages as $language) {
        if ($language->entity != NULL) {
          $this->languages[] = $language->entity;
        }
      }
    }
    return $this->languages;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      User::load($container->get('current_user')->id())
    );
  }

}
