<?php

declare(strict_types=1);

namespace Drupal\farm_import_csv\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\File\FileExists;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\file\FileUsage\FileUsageInterface;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\MigrationPluginManager;
use Drupal\migrate_tools\MigrateBatchExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the CSV import form.
 */
class CsvImportForm extends FormBase {

  public function __construct(
    protected MigrationPluginManager $migrationPluginManager,
    protected FileSystemInterface $fileSystem,
    protected TimeInterface $time,
    protected KeyValueFactoryInterface $keyValueFactory,
    protected TranslationManager $translationManager,
    protected FileUsageInterface $fileUsage,
    protected PrivateTempStoreFactory $tempStoreFactory,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('plugin.manager.migration'),
      $container->get('file_system'),
      $container->get('datetime.time'),
      $container->get('keyvalue'),
      $container->get('string_translation'),
      $container->get('file.usage'),
      $container->get('tempstore.private'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'farm_import_csv';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $migration_id = NULL): array {

    // Migration ID.
    $form['migration_id'] = [
      '#type' => 'value',
      '#value' => $migration_id,
    ];

    // File upload field.
    $form['source_file'] = [
      '#type' => 'file',
      '#title' => $this->t('Upload the source file'),
      '#upload_validators' => [
        'FileExtension' => [
          'extensions' => 'csv',
        ],
      ],
    ];

    // Import submit button.
    $form['actions'] = [
      '#type' => 'actions',
      '#weight' => 1000,
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {

    // Prepare the private://csv directory.
    $directory = 'private://csv';
    $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);

    // Save the uploaded file.
    $validators = ['FileExtension' => ['extensions' => 'csv']];
    $file = file_save_upload('source_file', $validators, $directory, 0, FileExists::Rename);

    if (isset($file)) {
      // File upload was attempted.
      if ($file) {
        $form_state->setValue('file_path', $file->getFileUri());
      }
      // File upload failed.
      else {
        $form_state->setErrorByName('source_file', $this->t('The file could not be uploaded.'));
      }
    }
    else {
      $form_state->setErrorByName('source_file', $this->t('You have to upload a source file.'));
    }

    // If there is no uploaded file, bail.
    if (empty($form_state->getValue('file_path'))) {
      return;
    }

    // Register file usage.
    $this->fileUsage->add($file, 'farm_import_csv', 'migration', $form_state->getValue('migration_id'));

    // Save the file ID to the private tempstore.
    $this->tempStoreFactory->get('farm_import_csv')->set($this->currentUser()->id() . ':' . $form_state->getValue('migration_id'), $file->id());
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $migration_id = $form_state->getValue('migration_id');
    /** @var \Drupal\migrate\Plugin\Migration $migration */
    $migration = $this->migrationPluginManager->createInstance($migration_id);

    // Reset status.
    $status = $migration->getStatus();
    if ($status !== MigrationInterface::STATUS_IDLE) {
      $migration->setStatus(MigrationInterface::STATUS_IDLE);
      $this->messenger()->addWarning($this->t('Migration @id reset to Idle', ['@id' => $migration_id]));
    }

    // Build and execute the batch operation.
    $batch_options = [
      'configuration' => [
        'source' => [
          'path' => $form_state->getValue('file_path'),
        ],
      ],
    ];
    $executable = new MigrateBatchExecutable($migration, new MigrateMessage(), $this->keyValueFactory, $this->time, $this->translationManager, $this->migrationPluginManager, $batch_options);
    $executable->batchImport();
  }

}
