<?php

namespace Drupal\custom_general\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\custom_general\Controller\apiHelper;

class medicardApi extends ControllerBase {
  /**
   * Check user auth.
   */
  public function check_user_auth() {
    $uid = \Drupal::currentUser()->id();

    if ($uid == 0) {
      return AccessResult::forbidden();
    }
    else {
      return AccessResult::allowed();
    }
  }
}
