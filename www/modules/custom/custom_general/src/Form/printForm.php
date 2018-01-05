<?php

namespace Drupal\custom_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;
use Drupal\custom_general\Controller\api;

class printForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'print_form';
  }

  /**
   * Form builder.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // $file = \Drupal\file\Entity\File::load(18);
    // dsm($file->getFilename());
    // dsm(getcwd());

    $uid = \Drupal::currentUser()->id();

    $validators = array(
      'file_validate_extensions' => array('doc docx pdf ppt pptx odt png jpg'),
      'file_validate_size' => array(file_upload_max_size()),
    );

    $form['print_file'] = array(
      '#type' => 'managed_file',
      '#title' => t("Document"),
      '#description' => t('Upload your file.'),
      '#upload_location' => 'public://printable_files/' . hash('md5', $uid) . '/',
      '#upload_validators' => $validators,
      '#required' => TRUE,
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Upload File'),
      '#button_type' => 'primary',
    );
    $form['actions']['submit_print'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Upload & Print'),
      '#button_type' => 'primary',
    );

    return $form;
  }

  /**
   * Validate form.
   */
  // public function validateForm(array &$form, FormStateInterface $form_state) {
  //   $file = $form_state->getValue('print_file');

  //   if (!empty($file)) {
  //     $file_raw_path = $file_path = $_SERVER['DOCUMENT_ROOT'] . "/sites/default/files/";
  //     $file = \Drupal\file\Entity\File::load($file[0]);

  //     $file_uri_raw = $file_uri = str_replace('public://', '', $file->getFileUri());
  //     $file_uri = str_replace(" ", "\ ", $file_uri);

  //     $file_path = $file_path . $file_uri;

  //     // check error on file.
  //     exec("/bin/bash unoconv --stdout " . $file_path . " 2> " . $file_raw_path . "error_file.txt");
  //     $file_error = exec("cat " . $file_raw_path . "error_file.txt");
  //     if (!empty($file_error)) {
  //       $form_state->setErrorByName('print_file', $this->t("We can't read your document. Sorry for the inconvenience."));

  //     }
  //   }
  // }

  /**
   * Form submit.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file_raw_path = $file_path = getcwd() . "/sites/default/files/";
    $uid = \Drupal::currentUser()->id();

    $file = $form_state->getValue('print_file');
    $fid = $file[0];

    $file = \Drupal\file\Entity\File::load($fid);

    $file_uri = str_replace('public://', '', $file->getFileUri());
    $file_uri_raw = str_replace(" ", "\ ", $file_uri);  

    $file_path = $file_path . $file_uri;

    $values = array(
      'type' => 'data_print',
      'uid' => $uid,
      'title' => 'Print---' . $uid . '---' . \Drupal::time()->getRequestTime(),
    );

    $values['field_documents'] = array(
      'target_id' => $fid,
    );

    $page = 0;

    /* 
     * Count number of pages.
     * extension: doc docx pdf ppt pptx odt
     */
    $ext = pathinfo($file_path, PATHINFO_EXTENSION);
    $page = api::get_page_file_count($ext, $file_path);

    if ($page > 0) {
      $values['field_doc_pages'] = array(
        'value' => $page,
      );

      $file->setPermanent();
      $file->save();

      // Node data save.
      $node = Node::create($values);
      $node->save();

      // if need to print
      if ($form_state->getValue('op') == 'Upload & Print') {
        api::print_me($fid);
      }

      drupal_set_message("Successfully added file.");
    }
    else {
      drupal_set_message("We can't read your document. Sorry for the inconvenience.", "error");
    }
  }
}