<?php

namespace Drupal\custom_general\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\custom_general\Controller\apiHelper;
use Drupal\custom_general\Controller\apiLoan;

class userDashboard extends ControllerBase {

  /**
   * User dashboard
   */
  public function user_dashboard() {
    $output = "";

    if (!apiHelper::check_user_role('loan')) {
      $output .= '
      <div class="portlet">
        <div class="portlet-title">
          <span class="caption-subject theme-font bold uppercase">Sales Summary</span>
        </div>
        <div class="portlet-body">
            <div class="row list-separated">
              <div class="col-md-3 col-sm-3 col-xs-6">
                <div class="font-grey-mint font-sm">
                   Total Sales
                </div>
                <div class="uppercase font-hg font-red-flamingo">
                   13,760 <span class="font-lg font-grey-mint">$</span>
                </div>
              </div>
              <div class="col-md-3 col-sm-3 col-xs-6">
                <div class="font-grey-mint font-sm">
                   Revenue
                </div>
                <div class="uppercase font-hg theme-font">
                   4,760 <span class="font-lg font-grey-mint">$</span>
                </div>
              </div>
              <div class="col-md-3 col-sm-3 col-xs-6">
                <div class="font-grey-mint font-sm">
                   Expenses
                </div>
                <div class="uppercase font-hg font-purple">
                   11,760 <span class="font-lg font-grey-mint">$</span>
                </div>
              </div>
              <div class="col-md-3 col-sm-3 col-xs-6">
                <div class="font-grey-mint font-sm">
                   Growth
                </div>
                <div class="uppercase font-hg font-blue-sharp">
                   9,760 <span class="font-lg font-grey-mint">$</span>
                </div>
              </div>
            </div>
          </div>
      </div>
      <div id="sales_statistics" class="portlet-body-morris-fit morris-chart" style="height: 260px">';

    }
    else {
      $output = "<div class='col-sm-12'><h1>Active Loan</h1></div>";
      $accordion = '<div class="panel-group accordion" id="archives">
        <div class="panel">
          <div class="panel-heading">
          <h4 class="panel-title">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#archives" href="#collapse1">
            Archives </a>
            </h4>
          </div>';
      $output2 = "";
      $archives = array();

      $query_loan = \Drupal::database()->query("SELECT nfd.nid AS nid, nfn.field_name_value AS name, nfa.field_archive_value AS archive FROM node_field_data AS nfd
        LEFT JOIN node__field_name AS nfn ON nfn.entity_id = nfd.nid
        LEFT JOIN node__field_archive AS nfa ON nfa.entity_id = nfd.nid
        WHERE nfd.type = 'loan' AND nfd.uid = " . \Drupal::currentUser()->id() . " ORDER BY nfd.created DESC")->fetchAll();
      
      foreach ($query_loan as $res) {
        if ($res->archive != 1) {
          $output .= "<div class='col-md-6 col-sm-12'><div class='portlet yellow-crusta box'>";

          $output .= "<div class='portlet-title'><div class='caption'><i class='fa fa-user'></i> " . ucwords($res->name) . "</div><div class='actions'><a class='btn btn-default btn-sm' href='/user/loan/" . $res->nid . "'><i class='fa fa-info-circle'></i> View</a><a class='btn btn-default btn-sm' href='#loan_modal" . $res->nid . "' data-toggle='modal'><i class='fa fa-archive'></i> Archive</a></div></div>";

          $output .= "<div class='portlet-body'>
            <div class='row static-info'>
              <div class='col-md-5 name'>Loan amount:</div><div class='col-md-7 value'>" . apiLoan::loan_amount($res->nid) . "</div>
            </div>
            <div class='row static-info'>
              <div class='col-md-5 name'>Balance:</div><div class='col-md-7 value'>" . apiLoan::loan_balance($res->nid) . "</div>
            </div>
            <div class='row static-info'>
              <div class='col-md-5 name'>Paid:</div><div class='col-md-7 value'>" . apiLoan::loan_paid_total($res->nid) . "</div>
            </div>
            <div class='row static-info'>
              <div class='col-md-5 name'>Payment start:</div><div class='col-md-7 value'>" . apiLoan::loan_payment_start($res->nid) . "</div>
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
        else {
          $output2 .= "<div class='col-md-6 col-sm-12'><div class='portlet red box'>";

          $output2 .= "<div class='portlet-title'><div class='caption'><i class='fa fa-user'></i> " . ucwords($res->name) . "</div><div class='actions'><a class='btn btn-default btn-sm' href='/user/loan/" . $res->nid . "'><i class='fa fa-info-circle'></i> View</a></div></div>";

          $output2 .= "<div class='portlet-body'>
            <div class='row static-info'>
              <div class='col-md-5 name'>Loan amount:</div><div class='col-md-7 value'>" . apiLoan::loan_amount($res->nid) . "</div>
            </div>
            <div class='row static-info'>
              <div class='col-md-5 name'>Balance:</div><div class='col-md-7 value'>" . apiLoan::loan_balance($res->nid) . "</div>
            </div>
            <div class='row static-info'>
              <div class='col-md-5 name'>Paid:</div><div class='col-md-7 value'>" . apiLoan::loan_paid_total($res->nid) . "</div>
            </div>
          </div>";

          $output2 .= "</div></div>";
        }
      }



      if (empty($query_loan)) {
        $output .= "<div class='col-sm-12'>Please add new loan <a href='/user/add_loan'>here</a>.</div>";
      }

      $accordion .= '<div id="collapse1" class="panel-collapse collapse">
          <div class="panel-body row">' . $output2 . '</div>
        </div>
      </div>';
    }

    return array('#markup' => "<div class='row'>" . $output . "</div<div class='row'>" . $accordion . "</div", '#cache' => ['max-age' => 0,]);
  }
}