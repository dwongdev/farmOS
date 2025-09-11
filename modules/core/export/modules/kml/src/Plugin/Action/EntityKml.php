<?php

declare(strict_types=1);

namespace Drupal\farm_export_kml\Plugin\Action;

use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\Action\Plugin\Action\EntityActionBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_export_kml\Plugin\Action\Derivative\EntityKmlDeriver;
use Drupal\file\FileRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Action that exports KML from an entity geofield.
 */
#[Action(
  id: 'entity:kml_action',
  action_label: new TranslatableMarkup('Export entity geometry as KML'),
  deriver: EntityKmlDeriver::class,
)]
class EntityKml extends EntityActionBase {

  /**
   * The serializer service.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The file repository service.
   *
   * @var \Drupal\file\FileRepositoryInterface
   */
  protected $fileRepository;

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, SerializerInterface $serializer, FileSystemInterface $file_system, ConfigFactoryInterface $config_factory, FileRepositoryInterface $file_repository, FileUrlGeneratorInterface $file_url_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);
    $this->serializer = $serializer;
    $this->fileSystem = $file_system;
    $this->configFactory = $config_factory;
    $this->fileRepository = $file_repository;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('serializer'),
      $container->get('file_system'),
      $container->get('config.factory'),
      $container->get('file.repository'),
      $container->get('file_url_generator'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {

    // Bail if no geofield field is provided.
    if (empty($this->configuration['geofield'])) {
      return;
    }

    // Serialize the entities using the specified geofield name.
    $context = ['geofield' => $this->configuration['geofield']];
    $output = $this->serializer->serialize($entities, 'geometry_kml', $context);

    // If there are no placemarks, bail with a warning.
    $kml = simplexml_load_string($output);
    if (empty($kml->Document->Placemark) || empty($kml->Document->Placemark->count())) {
      $this->messenger()->addWarning($this->t('No placemarks were found.'));
      return;
    }

    // Prepare the file directory.
    $default_file_scheme = $this->configFactory->get('system.file')->get('default_scheme') ?? 'public';
    $directory = $default_file_scheme . '://kml';
    $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);

    // Create the file.
    $filename = 'kml_export-' . date('c') . '.kml';
    $destination = "$directory/$filename";
    try {
      $file = $this->fileRepository->writeData($output, $destination);
    }

    // If file creation failed, bail with a warning.
    catch (\Exception $e) {
      $this->messenger()->addWarning($this->t('Could not create file.'));
      return;
    }

    // Make the file temporary.
    $file->set('status', 0);
    $file->save();

    // Show a link to the file.
    $url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
    $this->messenger()->addMessage($this->t('KML file created: <a href=":url">%filename</a>', [
      ':url' => $url,
      '%filename' => $file->label(),
    ]));
  }

  /**
   * {@inheritdoc}
   */
  public function execute($object = NULL) {
    $this->executeMultiple([$object]);
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = FALSE) {
    return $object->access('view', $account, $return_as_object);
  }

}
