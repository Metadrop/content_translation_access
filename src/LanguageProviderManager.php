<?php

namespace Drupal\content_translation_access;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\content_translation_access\Plugin\LanguageProviderInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides the default ui_patterns manager.
 */
class LanguageProviderManager extends DefaultPluginManager implements PluginManagerInterface, LanguageProviderInterface {

  private $languages = NULL;

  /**
   * LanguageProviderManager constructor.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ContentTranslationAccess/LanguageProvider', $namespaces, $module_handler, 'Drupal\content_translation_access\Plugin\LanguageProviderInterface', 'Drupal\content_translation_access\Annotation\LanguageProvider');
    $this->moduleHandler = $module_handler;
    $this->alterInfo('content_translation_access_info');
    $this->setCacheBackend($cache_backend, 'content_translation_access', ['content_translation_access']);
  }

  /**
   * Returns all languages provided by plugins.
   *
   * @return array|\Drupal\Core\Language\Language|null
   *   The list of languages.
   */
  public function getLanguages() {
    if ($this->languages !== NULL) {
      return $this->languages;
    }
    $loaded_languages = [];

    foreach ($this->getDefinitions() as $definition) {
      /** @var \Drupal\content_translation_access\LanguageProviderInterface $language_provider */
      $language_provider = $this->getFactory()->createInstance($definition['id']);
      $languages = $language_provider->getLanguages();
      $languages = is_array($languages) ? $languages : [];
      $loaded_languages = array_merge($languages, $loaded_languages);

    }
    foreach ($loaded_languages as $language) {
      $key = $language->getId();
      if (!isset($this->languages[$key])) {
        $this->languages[$key] = $language;
      }
    }
    if ($this->languages == NULL) {
      $this->languages = [];
    }
    return $this->languages;
  }

}
