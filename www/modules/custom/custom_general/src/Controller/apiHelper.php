<?php

namespace Drupal\custom_general\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;

class apiHelper extends ControllerBase {

	public static function check_user_role($role) {
		$roles = \Drupal::currentUser()->getRoles();

		return in_array($role, $roles) ? true : false;
	}

	public function check_user_auth_loan() {
		$roles = \Drupal::currentUser()->getRoles();
		
		if (!in_array('loan', $roles)) {
      return AccessResult::forbidden();
    }
    else {
      return AccessResult::allowed();
    }
	}

	public static function check_loan_ownership($node_uid) {
		$uid = \Drupal::currentUser()->id();
		if ($uid == $node_uid) {
			return true;
		}
		else {
			return false;
		}
	}
}