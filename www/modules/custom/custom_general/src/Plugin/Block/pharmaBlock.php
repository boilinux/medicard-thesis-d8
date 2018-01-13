<?php

namespace Drupal\custom_general\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 *
 * @Block(
 *   id = "pharma_block",
 *   admin_label = @Translation("Pharmacist comment"),
 *   category = @Translation("Pharmacist commet block form"),
 * )
 */
class pharmaBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    drupal_set_message('Test');
    $builtForm = \Drupal::formBuilder()->getForm('Drupal\custom_general\Form\updatePharmacist');
    $renderArray['form'] = $builtForm;

    return $renderArray;
  }

}