<?php

namespace Drupal\custom_general\Twig;

use Drupal\node\Entity\Node;
use Drupal\custom_general\Controller\apiHelper;
use Drupal\custom_general\Controller\apiLoan;

class CustomTwigExtension extends \Twig_Extension {
  /**
   * @return array
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('custom_general_parse_photo', [$this, 'custom_general_parse_photo']),
      new \Twig_SimpleFunction('custom_general_render_attribute', [$this, 'custom_general_render_attribute']),
      new \Twig_SimpleFunction('custom_general_render_gallery_tags', [$this, 'custom_general_render_gallery_tags']),
      new \Twig_SimpleFunction('custom_general_activity_message', [$this, 'custom_general_activity_message']),
      new \Twig_SimpleFunction('custom_general_logo_path', [$this, 'custom_general_logo_path']),
      new \Twig_SimpleFunction('custom_general_print_username', [$this, 'custom_general_print_username']),
      new \Twig_SimpleFunction('custom_general_get_current_uid', [$this, 'custom_general_get_current_uid']),
      new \Twig_SimpleFunction('custom_general_render_menu', [$this, 'custom_general_render_menu']),
      new \Twig_SimpleFunction('custom_general_check_user_role', [$this, 'custom_general_check_user_role']),
      new \Twig_SimpleFunction('custom_general_loan_action', [$this, 'custom_general_loan_action']),
      new \Twig_SimpleFunction('custom_general_loan_balance', [$this, 'custom_general_loan_balance']),
      new \Twig_SimpleFunction('custom_general_loan_paid', [$this, 'custom_general_loan_paid']),
      new \Twig_SimpleFunction('custom_general_loan_status', [$this, 'custom_general_loan_status']),
      new \Twig_SimpleFunction('custom_general_loan_amount_payable', [$this, 'custom_general_loan_amount_payable']),
    ];
  }

  public function getName() {
    return 'custom_general.twig_extension';
  }

  public function custom_general_parse_photo($photo) {
    return str_replace('.jpg', '', render($photo));
  }

  public function custom_general_render_attribute($field) {
    $explode = explode(':', $field['content']['#cache']['tags'][0]);

    $alt = \Drupal::database()->query("SELECT field_gallery_alt FROM node__field_gallery WHERE field_gallery_target_id = " . $explode[1])->fetchField();

    return $alt;
  }

  public function custom_general_render_gallery_tags($field) {
    print_r($field);
  }

  public function custom_general_activity_message() {
    $output = "";

    if (!isset($_REQUEST['token'])) {
      $uid = \Drupal::currentUser()->id();

      $query = \Drupal::database()->query("SELECT b.body_value AS message, nfd.nid AS nid, nfn.field_notice_value AS notice FROM node_field_data AS nfd
        LEFT JOIN node__body AS b ON b.entity_id = nfd.nid
        LEFT JOIN node__field_is_seen AS nfis ON nfis.entity_id = nfd.nid
        LEFT JOIN node__field_notice AS nfn ON nfn.entity_id = nfd.nid
        WHERE nfd.type = 'activity' AND nfis.field_is_seen_value = 0")->fetchAll();


      foreach ($query as $msg) {
        $notice = 'info';
        if ($msg->notice == 'no_activity') {
          $notice = 'error';
        }
        else if ($msg->notice == 'success') {
          $notice = 'success';
        }

        $new_msg = trim(preg_replace('/\s+/', ' ', $msg->message));  

        $output .= "toastr['" . $notice . "']('" . strip_tags($new_msg) . "', 'Topup');";

        // if seen then update status to 1.
        $node = Node::load($msg->nid);
        $node->field_is_seen->value = 1;
        $node->save();
      }
    }

    return $output;
  }

  public function custom_general_logo_path() {
    return file_url_transform_relative(file_create_url(theme_get_setting('logo.url')));
  }

  public function custom_general_print_username() {
    $uid = \Drupal::currentUser()->id();

    $username = \Drupal::database()->query("SELECT name FROM users_field_data WHERE uid = " . $uid)->fetchField();

    return $username;
  }

  public function custom_general_get_current_uid() {
    return \Drupal::currentUser()->id();
  }

  public function custom_general_render_menu($menu_name) {
    $uid = \Drupal::currentUser()->id();

    $roles = \Drupal::currentUser()->getRoles();

    if (in_array('loan', $roles) && !in_array('administrator', $roles)) {
      $menu_name = 'loan-system';
    }

    $menu_tree = \Drupal::menuTree();

    // Build the typical default set of menu tree parameters.
    $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);

    // Load the tree based on this set of parameters.
    $tree = $menu_tree->load($menu_name, $parameters);
    
    // Transform the tree using the manipulators you want.
    $manipulators = array(
      // Only show links that are accessible for the current user.
      array('callable' => 'menu.default_tree_manipulators:checkAccess'),
      // Use the default sorting of menu links.
      array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
    );
    $tree = $menu_tree->transform($tree, $manipulators);

    // Finally, build a renderable array from the transformed tree.
    $menu = $menu_tree->build($tree);

    $menu['#attributes']['class'] = 'menu navbar-nav ' . $menu_name;

    return array('#markup' => drupal_render($menu));
  }

  public function custom_general_check_user_role($role) {
    return apiHelper::check_user_role($role);
  }

  public function custom_general_loan_action($nid) {
    $output = "";

    $status = \Drupal::database()->query("SELECT nfs.field_status_value AS status FROM node__field_status AS nfs WHERE nfs.bundle = 'loan_payable' AND nfs.entity_id = " . $nid)->fetchField();

    $loan_id = \Drupal::database()->query("SELECT nflr.field_loan_reference_target_id AS loan_id FROM node__field_loan_reference AS nflr WHERE nflr.bundle = 'loan_payable' AND nflr.entity_id = " . $nid)->fetchField();

    if ($status == 'unpaid') {
      $output .= "<a class='btn red btn-block' href='#loan_modal" . $nid . "' data-toggle='modal'><i class='fa fa-edit'></i> Update</a>";
      $output .= '<div class="modal fade" id="loan_modal' . $nid . '" tabindex="-1" role="basic" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                      <h4 class="modal-title">Please confirm</h4>
                    </div>
                    <div class="modal-body">
                       Amount of <span class="color_red">' . apiLoan::loan_amount_payable($nid) . '</span> will be paid.
                    </div>
                    <div class="modal-footer">
                      <a type="button" class="btn default" data-dismiss="modal">Close</a>
                      <a href="/user/loan/' . $loan_id . '/loan_payable/' . $nid . '/paid" class="btn blue">Submit</a>
                    </div>
                  </div>
                  <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
              </div>';
    }
    else {
      $output .= "-";
    }

    return $output;
  }

  /**
   * Loan balance.
   */
  public function custom_general_loan_balance($nid) {
    return apiLoan::loan_balance($nid);
  }

  /**
   * Loan paid.
   */
  public function custom_general_loan_paid($nid) {
    return apiLoan::loan_paid_total($nid);
  }

  /**
   * Return loan status.
   */
  public function custom_general_loan_status($nid) {
    $status = '';

    $query = \Drupal::database()->query("SELECT nfs.field_status_value AS status, nfpo.field_paid_on_value AS paid_date FROM node_field_data AS nfd
      LEFT JOIN node__field_status AS nfs ON nfs.entity_id = nfd.nid
      LEFT JOIN node__field_paid_on AS nfpo ON nfpo.entity_id = nfd.nid
      WHERE nfd.nid = " . $nid . " AND nfd.type = 'loan_payable'")->fetchAll();

    foreach ($query as $res) {
      if ($res->status == 'paid') {
        $status .= "Paid on " . date("Y-d-m", $res->paid_date);
      }
      else {
        $status .= "Unpaid";
      }
    }

    return $status;
  }

  /**
   * Loan amount payable.
   */
  public function custom_general_loan_amount_payable($amount) {
    return number_format($amount, 2);
  }
}