<?php

namespace Drupal\custom_general\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\custom_general\Controller\apiHelper;
use Drupal\node\Entity\Node;

class userDashboard extends ControllerBase {

  /**
   * User dashboard
   */
  public function user_dashboard() {
    $output = "";

    if (apiHelper::check_user_role('nurse')) {
      $query = \Drupal::database()->query("SELECT nffn.field_first_name_value AS firstname, nfln.field_last_name_value AS lastname, nfd.created AS created, nfd.nid AS nid FROM node_field_data AS nfd 
        LEFT JOIN node__field_first_name AS nffn ON nffn.entity_id = nfd.nid
        LEFT JOIN node__field_last_name As nfln ON nfln.entity_id = nfd.nid
        WHERE nfd.type = 'patient' AND nfd.uid = " . \Drupal::currentUser()->id() . " ORDER BY nfd.created DESC")->fetchAll();
      
      foreach ($query as $res) {
        $output .= "<div class='col-md-6 col-sm-12'><div class='portlet yellow-crusta box'>";

        $output .= "<div class='portlet-title'><div class='caption'><i class='fa fa-user'></i> " . ucwords($res->firstname) . " " . ucwords($res->lastname) . "</div><div class='actions'><a class='btn btn-default btn-sm' href='/view/patient/" . $res->nid . "'><i class='fa fa-info-circle'></i> View</a><a class='btn btn-default btn-sm' href='/update/patient/" . $res->nid . "'><i class='fa fa-edit'></i> edit</a></div></div>";

        $output .= "<div class='portlet-body'>
          <div class='row static-info'>
            <div class='col-md-5 name'>Registered on</div><div class='col-md-7 value'> " . date("d-M-Y", $res->created) . "</div>
          </div>
          <div class='row static-info'>
          </div>
        </div>";

        $output .= '<div class="modal fade" id="loan_modal' . $res->nid . '" tabindex="-1" role="basic" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Please confirm</h4>
                  </div>
                  <div class="modal-body">
                     <span class="color_green">' . ucwords($res->name) . '</span> loan will be archive.
                  </div>
                  <div class="modal-footer">
                    <a type="button" class="btn default" data-dismiss="modal">Close</a>
                    <a href="/user/loan/' . $res->nid . '/archive" class="btn blue">Submit</a>
                  </div>
                </div>
                <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>';

        $output .= "</div></div>";
      }
    }
    else if (apiHelper::check_user_role('doctor')) {
      $query = \Drupal::database()->query("SELECT nffn.field_first_name_value AS firstname, nfln.field_last_name_value AS lastname, nfd.created AS created, nfd.nid AS nid FROM node_field_data AS nfd 
        LEFT JOIN node__field_first_name AS nffn ON nffn.entity_id = nfd.nid
        LEFT JOIN node__field_last_name As nfln ON nfln.entity_id = nfd.nid
        WHERE nfd.type = 'patient' ORDER BY nfd.created DESC")->fetchAll();
      
      foreach ($query as $res) {
        $patient = Node::load($res->nid);

        $output .= "<div class='col-md-6 col-sm-12'><div class='portlet yellow-crusta box'>";

        $output .= "<div class='portlet-title'><div class='caption'><i class='fa fa-user'></i> " . ucwords($res->firstname) . " " . ucwords($res->lastname) . "</div><div class='actions'><a class='btn btn-default btn-sm' href='/update/doctor/patient/" . $res->nid . "'><i class='fa fa-edit'></i> Update</a></div></div>";

        $output .= "<div class='portlet-body'>
          <div class='row static-info'>
            <div class='col-md-5 name'>Date Of Birth:</div><div class='col-md-7 value'> " . date("d-M-Y", $patient->get('field_date_of_birth')->value) . "</div>
            <div class='col-md-5 name'>Gender:</div><div class='col-md-7 value'> " . ucwords($patient->get('field_gender')->value) . "</div>
            <div class='col-md-5 name'>Address:</div><div class='col-md-7 value'> " . ucwords($patient->get('field_patient_address')->value) . "</div>
            <div class='col-md-12 name'><p><h2>Vital signs</h2></p></div>
            <div class='col-md-5 name'>Temperature:</div><div class='col-md-7 value'> " . ucwords($patient->get('field_temperature')->value) . "</div>
            <div class='col-md-5 name'>Pulse:</div><div class='col-md-7 value'> " . ucwords($patient->get('field_pulse')->value) . "</div>
            <div class='col-md-5 name'>Respirations/Breathing:</div><div class='col-md-7 value'> " . ucwords($patient->get('field_respirations_breathing')->value) . "</div>
            <div class='col-md-5 name'>Blood Pressure:</div><div class='col-md-7 value'> " . ucwords($patient->get('field_blood_pressure')->value) . "</div>
          </div>
          <div class='row static-info'>
            <div class='col-md-5 name'>Registered on </div><div class='col-md-7 value'> " . date("d-M-Y", $patient->get('created')->value) . "</div>
          </div>
        </div>";

        $output .= '<div class="modal fade" id="loan_modal' . $res->nid . '" tabindex="-1" role="basic" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Please confirm</h4>
                  </div>
                  <div class="modal-body">
                     <span class="color_green">' . ucwords($res->name) . '</span> loan will be archive.
                  </div>
                  <div class="modal-footer">
                    <a type="button" class="btn default" data-dismiss="modal">Close</a>
                    <a href="/user/loan/' . $res->nid . '/archive" class="btn blue">Submit</a>
                  </div>
                </div>
                <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>';

        $output .= "</div></div>";
      }
    }

    if (empty($query)) {
      $output .= "<div class='col-sm-12'>No patient yet.</div>";
    }

    return array('#markup' => "<div class='row'>" . $output . "</div>", '#cache' => ['max-age' => 0,]);
  }
}