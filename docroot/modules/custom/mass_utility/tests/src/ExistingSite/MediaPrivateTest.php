<?php

namespace Drupal\Tests\mass_utility\ExistingSite;

use Drupal\file\Entity\File;
use weitzman\DrupalTestTraits\Entity\MediaCreationTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests moving files into and out of the private filesystem on media update.
 */
class MediaPrivateTest extends ExistingSiteBase {

  use MediaCreationTrait;

  /**
   * Media files are moved to private on media unpublish.
   *
   * The 'file can't de deleted' notices from this test come from mass_utility_file_move().
   */
  public function testMovesFileToPrivateOnUnpublish() {
    // Create a "Llama" media item.
    file_put_contents('public://llama-43.txt', 'Test');
    $file = File::create([
      'uri' => 'public://llama-43.txt',
    ]);
    $media = $this->createMedia([
      'title' => 'Llama',
      'bundle' => 'document',
      'field_upload_file' => [$file],
    ]);
    array_pop($this->cleanupEntities);
    $media->setUnpublished()->set('moderation_state', 'unpublished')->save();
    // Request cleanup after the switch to private has occurred.
    $this->markEntityForCleanup($media);

    $file2 = File::load($media->field_upload_file->target_id);
    $fs = \Drupal::service('file_system');
    $this->assertEquals('private', $fs->uriScheme($file2->getFileUri()));
  }

  /**
   * Media files are moved to public on media publish.
   *
   * The 'file can't de deleted' notices from this test come from mass_utility_file_move().
   */
  public function testMovesFileToPublicOnPublish() {
    // Create a "Llama" media item.
    file_put_contents('private://llama-43.txt', 'Test');
    $file = File::create([
      'uri' => 'private://llama-43.txt',
    ]);
    $media = $this->createMedia([
      'title' => 'Llama',
      'bundle' => 'document',
      'field_upload_file' => [$file],
      'status' => 0,
    ]);
    array_pop($this->cleanupEntities);
    $media->setPublished()->save();
    $this->markEntityForCleanup($media);

    $file2 = File::load($media->field_upload_file->target_id);
    $fs = \Drupal::service('file_system');
    $this->assertEquals('public', $fs->uriScheme($file2->getFileUri()));
  }

}
