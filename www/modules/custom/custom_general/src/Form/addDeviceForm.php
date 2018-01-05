<?php

namespace Drupal\custom_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;
use Drupal\custom_general\Controller\api;

class addDeviceForm extends FormBase {

	/**
   * {@inheritdoc}
   */
	public function getFormId() {
		return "add_device_form";
	}

  /**
   * Form builder.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $uid = \Drupal::currentUser()->id();

    $how_many_device = \Drupal::database()->query("SELECT nfhmd.field_how_many_device_value AS how_many_device FROM node_field_data AS nfd 
    	LEFT JOIN node__field_how_many_device AS nfhmd ON nfhmd.entity_id = nfd.nid
    	WHERE nfd.uid = " . $uid . " AND nfd.type = 'user_settings'")->fetchField();

    $how_many_device = $how_many_device == 0 ? 0 : $how_many_device; 
    $msg_notice = $how_many_device == 0 ? '<span class="custom_message" data-notice="error">Please contact site administrator to add more devices.</span>' : '<span class="custom_message" data-notice="success">You can now add ' . $how_many_device . ' device.</span>';

    $form['notice'] = array(
    	'#type' => 'markup',
    	'#markup' => $msg_notice,
    );

    if ($how_many_device > 0) {
	    $form['device_token'] = array(
	      '#type' => 'textfield',
	      '#title' => t("Token"),
	      '#description' => t('Access token.'),
	      '#required' => TRUE,
	    );
	    $form['actions']['#type'] = 'actions';
	    $form['actions']['submit'] = array(
	      '#type' => 'submit',
	      '#value' => $this->t('Submit'),
	      '#button_type' => 'primary',
	    );	
    }
    else {
    	$form['notice2'] = array(
	    	'#type' => 'markup',
	    	'#markup' => "Your account is not authorize to add more device.",
	    );
    }

    return $form;
  }

  /**
   * Form Submit.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  	$uid = \Drupal::currentUser()->id();

  	// Update user settings.
    $query = \Drupal::database()->query("SELECT nfhmd.field_how_many_device_value AS how_many_device, nfhmd.entity_id AS device_nid FROM node_field_data AS nfd 
    	LEFT JOIN node__field_how_many_device AS nfhmd ON nfhmd.entity_id = nfd.nid
    	WHERE nfd.uid = " . $uid . " AND nfd.type = 'user_settings'")->fetchAll();
  	foreach($query as $result) {
	  	if ($result->how_many_device > 0) {
		  	$values = array(
		      'type' => 'device',
		      'uid' => $uid,
		      'title' => 'device---' . $uid . '---' . \Drupal::time()->getRequestTime(),
		    );

		    $values['field_token'] = array(
		      'value' => $form_state->getValue('device_token'),
		    );

		    // Node data save.
		    $node = Node::create($values);
		    $node->save();

		    // update device settings
	      $node = Node::load($result->device_nid);
	      $node->field_how_many_device->value = $result->how_many_device - 1;
	      $node->save();

		    drupal_set_message("Successfully added file.");
	  	}
	  	else {
	  		drupal_set_message("There is an error. Please contact site administrator.", 'error');
	  	}
	  }
  	}

}