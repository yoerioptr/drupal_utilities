<?php

namespace Drupal\field_fallback\Plugin\Field\FieldFormatter;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FormatterPluginManager;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @FieldFormatter(
 *   id = "field_fallback",
 *   label = @Translation("Field fallback"),
 *   description = @Translation("Displays a selected fallback field of the same
 *   type if the main field is empty."),
 *   field_types = {
 *     "entity_reference",
 *     "entity_reference_revisions",
 *     "string",
 *     "string_long",
 *   }
 * )
 */
final class FieldFallbackFormatter extends FormatterBase {

  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    private readonly EntityFieldManagerInterface $entityFieldManager,
    private readonly FormatterPluginManager $formatterPluginManager
  ) {
    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $label,
      $view_mode,
      $third_party_settings
    );
  }

  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ): self {
    return new self(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity_field.manager'),
      $container->get('plugin.manager.field.formatter')
    );
  }

  public static function defaultSettings(): array {
    return [
      'fallback_field' => NULL,
      'fallback_field_formatter' => [],
    ];
  }

  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $form = parent::settingsForm($form, $form_state);

    $fallback_field_mapping = $this->getFallbackFieldMapping();

    $form['fallback_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Fallback field'),
      '#options' => array_combine(
        array_keys($fallback_field_mapping),
        array_column($fallback_field_mapping, 'label')
      ),
      '#default_value' => $this->getSetting('fallback_field'),
      '#name' => 'fallback_field',
      '#required' => TRUE,
    ];

    foreach ($fallback_field_mapping as $field_name => $field_info) {
      $form['fallback_field_formatter'][$field_name] = [
        '#type' => 'select',
        '#title' => $this->t('Fallback field formatter'),
        '#options' => $field_info['formatters'],
        '#default_value' => $this->getSetting('fallback_field_formatter')[$field_name] ?? '',
        '#states' => [
          'visible' => [':input[name="fallback_field"]' => ['value' => $field_name]],
          'required' => [':input[name="fallback_field"]' => ['value' => $field_name]],
        ],
      ];
    }

    return $form;
  }

  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $formatter = $this->getSetting('fallback_field_formatter')[$this->settings['fallback_field']] ?? NULL;
    if (is_null($formatter)) {
      return [];
    }

    $fallback_field = $this->getSetting('fallback_field');
    if (!$items->getEntity()->hasField($fallback_field)) {
      return [];
    }

    $display_field = !$items->getEntity()->get($items->getName())->isEmpty()
      ? $items->getEntity()->get($items->getName())
      : $items->getEntity()->get($fallback_field);

    return $display_field->view([
      'format' => $formatter,
      'label' => $this->label,
    ]);
  }

  private function getFallbackFieldMapping(): array {
    $fields = $this->entityFieldManager->getFieldDefinitions(
      $this->fieldDefinition->getTargetEntityTypeId(),
      $this->fieldDefinition->getTargetBundle()
    );

    $fields = array_filter($fields, function (FieldDefinitionInterface $field_definition): bool {
      return $field_definition->getType() === $this->fieldDefinition->getType()
        && $field_definition->getName() !== $this->fieldDefinition->getName();
    });

    return array_map(function (FieldDefinitionInterface $field_definition): array {
      return [
        'label' => "{$field_definition->getLabel()} ({$field_definition->getName()})",
        'formatters' => $this->getFieldFormatters(),
      ];
    }, $fields);
  }

  private function getFieldFormatters(): array {
    $options = $this->formatterPluginManager->getOptions($this->fieldDefinition->getType());

    return array_filter($options, function (string $formatter_id): bool {
      if ($formatter_id === $this->getPluginId()) {
        return FALSE;
      }

      $plugin_class = DefaultFactory::getPluginClass(
        $formatter_id,
        $this->formatterPluginManager->getDefinition($formatter_id)
      );

      return $plugin_class::isApplicable($this->fieldDefinition);
    }, ARRAY_FILTER_USE_KEY);
  }

}
