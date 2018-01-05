<?php

namespace Drupal\custom_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;
use Drupal\custom_general\Controller\api;

class addLoanForm extends FormBase {

	/**
   * {@inheritdoc}
   */
	public function getFormId() {
		return "add_loan_form";
	}

  /**
   * Form builder.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $uid = \Drupal::currentUser()->id();

    $form['name'] = array(
    	'#title' => 'Name',
    	'#type' => 'textfield',
    	'#required' => TRUE,
    );
    $form['name']['#prefix'] = '<div class="portlet box green">
			<div class="portlet-title">
				<div class="caption"><i class="fa fa-upload"></i> Person Details:</div>
			</div>
			<div class="portlet-body">';


    $form['contact'] = array(
    	'#title' => 'Contact',
    	'#type' => 'textfield',
    	'#required' => TRUE,
    );
    $form['address'] = array(
    	'#title' => 'Address',
    	'#type' => 'textarea',
    	'#required' => TRUE,
    );
    $form['address']['#suffix'] = "</div></div>";


    $form['amount'] = array(
    	'#title' => 'Amount',
    	'#type' => 'textfield',
    	'#required' => TRUE,
    );
    $form['amount']['#prefix'] = '<div class="portlet box green">
			<div class="portlet-title">
				<div class="caption"><i class="fa fa-upload"></i> Loan Details:</div>
			</div>
			<div class="portlet-body">';

    $no_interest = array();
    for ($count = 1; $count <= 100; $count++) {
    	$no_interest[$count] = $count . "%";
    }

    $form['interest'] = array(
    	'#title' => 'Interest',
    	'#type' => 'select',
    	'#options' => $no_interest,
    	'#default_value' => 20,
    	'#required' => TRUE,
    );

    $form['loan_type'] = array(
    	'#title' => 'Terms',
    	'#type' => 'select',
    	'#options' => array(
    		'daily' => 'Daily',
    		'month' => 'Month',
    	),
    	'#default_value' => 'daily',
    	'#required' => TRUE,
    );
    $form['loan_type']['#prefix'] = '<div class="portlet box yellow">
			<div class="portlet-title">
				<div class="caption"><i class="fa fa-calendar"></i> Terms</div>
			</div>
			<div class="portlet-body">';

    $form['daily'] = array(
    	'#title' => 'if <span class="color_red">daily</span>, how many days?',
    	'#type' => 'textfield',
    );

    $form['monthly'] = array(
    	'#title' => 'if <span class="color_red">month</span>, how many gives?',
    	'#type' => 'textfield',
    );
    $form['monthly']['#suffix'] = "</div></div>";

    $form['start_payment'] = array(
    	'#title' => 'Start of payment',
    	'#type' => 'date',
    	'#required' => TRUE,
    );

    $form['start_payment']['#suffix'] = "</div></div>";

    
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
      '#attributes' => array(
      	'class' => array('btn green'),
      ),
    );	

    return $form;
  }

  /**
   * Validate form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    if (!is_numeric($form_state->getValue('amount'))) {
    	$form_state->setErrorByName('amount', $this->t("Invalid amount. Numbers only."));
    }

    if ($form_state->getValue('loan_type') == 'daily') {
    	if (empty($form_state->getValue('daily'))) {
    		$form_state->setErrorByName('daily', $this->t("Please fill textfield for how many days."));
    	}
    	if (!is_numeric($form_state->getValue('daily'))) {
    		$form_state->setErrorByName('daily', $this->t("Invalid, Numbers only for how many days."));
    	}
    }

    if ($form_state->getValue('loan_type') == 'month') {
    	if (empty($form_state->getValue('monthly'))) {
    		$form_state->setErrorByName('monthly', $this->t("Please fill textfield for how many gives."));
    	}
    	if (!is_numeric($form_state->getValue('monthly'))) {
    		$form_state->setErrorByName('monthly', $this->t("Invalid, Numbers only for how many gives."));
    	}
    }

  }

  /**
   * Form Submit.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  	$uid = \Drupal::currentUser()->id();

    $start_payment = strtotime($form_state->getValue('start_payment'));

  	// Create loan data content.
  	$values = array(
      'type' => 'loan',
      'uid' => $uid,
      'title' => 'loan---' . $uid . '---' . \Drupal::time()->getRequestTime(),
    );

    $values['field_amount'] = array(
      'value' => $form_state->getValue('amount'),
    );
    $values['field_interest'] = array(
      'value' => $form_state->getValue('interest'),
    );
    $values['field_loan_type'] = array(
      'value' => $form_state->getValue('loan_type'),
    );
    $values['field_name'] = array(
      'value' => $form_state->getValue('name'),
    );
    $values['field_contact'] = array(
      'value' => $form_state->getValue('contact'),
    );
    $values['field_address'] = array(
      'value' => $form_state->getValue('address'),
    );
    $values['field_start_payment'] = array(
      'value' => $start_payment,
    );

    $interest_amount = ($form_state->getValue('amount') * ($form_state->getValue('interest') / 100));
    $total_amount = $form_state->getValue('amount') + $interest_amount;

    $values['field_total_amount'] = array(
      'value' => $total_amount,
    );
    $values['field_interest_amount'] = array(
      'value' => $interest_amount,
    );

    // Node data save.
    $loan_node = Node::create($values);
    $loan_node->save();

    // Generate loan payable content.
    $loan_type = $form_state->getValue('loan_type');

    if ($loan_type == 'daily') {
    	$days = $form_state->getValue('daily');
    	$daily_amount = $total_amount / $days; // 40 days

    	for ($count = 1; $count <= $days; $count++) {

    		if ($count > 1) {
    			$start_payment = strtotime($form_state->getValue('start_payment') . "+" . ($count-1) . " day");
    		}

    		$values = array(
		      'type' => 'loan_payable',
		      'uid' => $uid,
		      'title' => 'loan_payable---' . $uid . '---' . \Drupal::time()->getRequestTime(),
		    );

		    $values['field_amount_payable'] = array(
		      'value' => $daily_amount,
		    );
		    $values['field_loan_payable_weight'] = array(
		      'value' => $count,
		    );
		    $values['field_loan_reference'] = array(
		      'target_id' => $loan_node->id(),
		    );
		    $values['field_payment_on'] = array(
		      'value' => $start_payment,
		    );
		    $values['field_status'] = array(
		      'value' => 'unpaid',
		    );

		    // Node data save.
		    $loan_payable = Node::create($values);
		    $loan_payable->save();
    	}
    }
    else if ($loan_type == 'month'){
    	$gives = $form_state->getValue('monthly');
    	$monthlygives = $total_amount / $gives; // 2 gives

    	$gives_day = $day_payment = 30 / $gives; // 30 days = 1 month

    	for ($count = 1; $count <= $gives; $count++) {

    		if ($count > 1) {
    			$start_payment = strtotime($form_state->getValue('start_payment') . "+" . $gives_day . " day");

		    	$gives_day += $day_payment;
    		}

    		$values = array(
		      'type' => 'loan_payable',
		      'uid' => $uid,
		      'title' => 'loan_payable---' . $uid . '---' . \Drupal::time()->getRequestTime(),
		    );

		    $values['field_amount_payable'] = array(
		      'value' => $monthlygives,
		    );
		    $values['field_loan_payable_weight'] = array(
		      'value' => $count,
		    );
		    $values['field_loan_reference'] = array(
		      'target_id' => $loan_node->id(),
		    );
		    $values['field_payment_on'] = array(
		      'value' => $start_payment,
		    );
		    $values['field_status'] = array(
		      'value' => 'unpaid',
		    );

		    // Node data save.
		    $loan_payable = Node::create($values);
		    $loan_payable->save();
    	}
    }

    drupal_set_message("Successfully added loan for " .  $form_state->getValue('name') . ".");
  }

}