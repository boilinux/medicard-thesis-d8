<?php

namespace Drupal\custom_general\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\custom_general\Controller\api;

/**
 * Provides a 'User credit' Block.
 *
 * @Block(
 *   id = "custom_general_user_point",
 *   admin_label = @Translation("Your credit(s)"),
 *   category = @Translation("User credit"),
 * )
 */
class UserPointBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $api = new api();

  	$credit = $api->user_credit_points() - $api->user_credit_deduct_points();

  	$credit = '<div class="credit-text"><span class="glyphicon glyphicon-info-sign"></span> Your credit(s): ₱' . $credit . '</div>';
    return array(
      '#markup' => $credit,
      '#prefix' => "<div class='user-credit'>",
      '#suffix' => "<div class='user-topup'>
      	<a href='/api/data/insert/credit' class='use-ajax btn btn-info btn-sm'><span class='glyphicon glyphicon-cloud-upload'> Topup</span></a></div></div><div id='credit-ajax-response'></div>",
      '#cache' => array('max-age' => 0),
    );
  }

}