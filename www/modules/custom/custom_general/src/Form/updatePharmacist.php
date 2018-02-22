<?php

namespace Drupal\custom_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;
use Drupal\custom_general\Controller\medicardApi;

class updatePharmacist extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pharmacist_comment_form';
  }

  /**
   * Form builder.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $card = medicardApi::get_card_id();
    $card = str_replace(' ', '', $card);

    $data = medicardApi::get_patient();
    foreach ($data['patient'] as $nid => $patient) {
      if ($patient['card_id'] == $card) {
        $form['nid'] = array(
          '#type' => 'hidden',
          '#value' => $nid,
        );

        $form['fullname'] = array(
          '#type' => 'hidden',
          '#value' => ucwords($patient['firstname']) . " " . ucwords($patient['lastname']),
        );
      }
    }

    $output = "";


    $query = \Drupal::database()->query("SELECT nfd.nid AS nid FROM node_field_data AS nfd 
          WHERE nfd.type = 'medicine' ORDER BY nfd.created DESC")->fetchAll();

    $medicines = [];
    $output .= "<p>Description:</p><ul class='desc-medicine'>";
    foreach ($query as $res) {
      $node = Node::load($res->nid);

      $medicines[$node->get('title')->value] = $node->get('title')->value;
      $output .= "<li class='" . str_replace(' ', '', $node->get('title')->value) . "'><p>" . $node->get('field_body')->value . "</p></li>";
    }
    $output .= "</ul>";

    $form['medicine'] = [
      '#type' => 'select',
      '#title' => 'Choose a medicine',
      '#options' => $medicines,
    ];
    $form['medicine']['#prefix'] = '<div class="portlet box green">
      <div class="portlet-title">
        <div class="caption"><i class="fa fa-upload"></i> Medicine</div>
      </div>
      <div class="portlet-body">' . $output;

    $form['medicine_quantity'] = [
      '#type' => 'textfield',
      '#title' => 'Quantity',
      '#default_value' => 1,
      '#suffix' => "<p><a id='add_medicine' href='#' class='btn btn-info'>Add medicine</a><a id='reset_medicine' href='#' class='btn btn-info'>reset</a></p>",
    ];

    $form['pharmacomment'] = array(
      '#type' => 'textarea',
      '#required' => TRUE,
    );
    $form['pharmacomment']['#suffix'] = "</div></div>";

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    );
    
    $form['#attached']['library'][] = 'custom_general/custom_general_script';

    return $form;
  }

  /**
   * Form submit.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $nid = $form_state->getValue('nid');
    $uid = \Drupal::currentUser()->id();

    $username = \Drupal::database()->query("SELECT name FROM users_field_data WHERE uid = " . $uid)->fetchField();

    $suffix = "\n posted on " . date("d-M-Y H:i", \Drupal::time()->getRequestTime()) . " by " . $username . " @ Hospital";

    $data = [
      'pharmacomment' => $form_state->getValue('pharmacomment') . $suffix,
    ];

    try {
      $client = \Drupal::httpClient();
      $response = $client->post('http://192.168.10.123/api/patient/update', [
        'headers' => [
          'Content-Type' => 'application/json',
          'token' => 'AAtqwghtXGCbcUsQuYDuIdmUL8KgVaFr',
          'secret' => 'VH7HutKJ5qsp52zSfSrJtbxz0oHuPTmJ',
          'nid' => $nid,
          'role' => 'pharmacist',
          'action' => 'update',
          'username' => $username,
          'site' => 'Barangay',
        ],
        'body' => json_encode($data),
      ]);

      $data = json_decode($response->getBody(), TRUE);
      
      if ($data['status'] == 'success') {
        drupal_set_message("Successfully updated patient " . ucwords($form_state->getValue('fullname')) . ".");

        $response = new \Symfony\Component\HttpFoundation\RedirectResponse("/");
        $response->send();
      }
      else {
        drupal_set_message("There are errors upon submission.", "error");
      }

    } catch (RequestException $e) {
      drupal_set_message("There are errors upon submission.", "error");
    }
  }
}