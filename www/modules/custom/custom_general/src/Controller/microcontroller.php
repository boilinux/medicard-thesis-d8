<?php

namespace Drupal\custom_general\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\node\Entity\Node;

class microcontroller extends ControllerBase {

	/**
	 * Create data
	 */
	public function create_data(Request $request) {
		$response = "";

		if (strpos($request->headers->get('Content-Type'), 'application/json') === 0) {
      $data = json_decode($request->getContent(), TRUE);

      $secret = $request->headers->get('secret');
      $token = $request->headers->get('token');

			// Check for validation.
			$query_secret = \Drupal::database()->query("SELECT COUNT(*) FROM node_revision__field_secret_api WHERE field_secret_api_value = '" . $secret . "'")->fetchField();

			$query_token = \Drupal::database()->query("SELECT COUNT(*) FROM node__field_device_token WHERE field_device_token_value = '" . $token . "'")->fetchField();

			if ($query_secret > 0 && $query_token > 0) {
				$amount = $data['amount'];

				$entity_id = \Drupal::database()->query("SELECT entity_id FROM node__field_device_token WHERE field_device_token_value = '" . $token . "'")->fetchField();

				$uid = \Drupal::database()->query("SELECT uid FROM node_field_data WHERE nid = '" . $entity_id . "'")->fetchField();

				// check for existing username.
				while (1) {
					$username = substr(str_shuffle(MD5(microtime())), 0, 6);
					$password = substr(str_shuffle(MD5(microtime())), 0, 6);

					$check_username = \Drupal::database()->query("SELECT COUNT(*) FROM node__field_username WHERE field_username_value = '" . $username . "'")->fetchField();
					if (empty($check_username)) {
						break;
					}
				}

				$values = array(
		      'type' => 'data',
		      'uid' => $uid,
		      'title' => 'microcontroller---' . $token . '---' . \Drupal::time()->getRequestTime(),
		      'field_amount' => array(
		        'value' => $amount,
		      ),
		      'field_username' => array(
		        'value' => $username,
		      ),
		      'field_passwrd' => array(
		        'value' => $password,
		      ),
		    );

		    if (!empty($entity_id)) {
		      $values['field_reference_device'] = array(
		        'target_id' => $entity_id,
		      );
		    }

		    // Node data save.
		    $node = Node::create($values);
		    $node->save();

				$response = array('status' => 'success', 'username' => $username, 'password' => $password);
			}
			else {
				$response = array('status' => 'failed');
			}
    }

    return new JsonResponse($response);
	}
}