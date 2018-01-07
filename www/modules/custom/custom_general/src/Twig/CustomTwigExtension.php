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
      new \Twig_SimpleFunction('custom_general_check_user_role', [$this, 'custom_general_check_user_role']),
      new \Twig_SimpleFunction('custom_general_logo_path', [$this, 'custom_general_logo_path']),
      new \Twig_SimpleFunction('custom_general_print_username', [$this, 'custom_general_print_username']),
      new \Twig_SimpleFunction('custom_general_get_current_uid', [$this, 'custom_general_get_current_uid']),
      new \Twig_SimpleFunction('custom_general_render_menu', [$this, 'custom_general_render_menu']),
    ];
  }

  public function getName() {
    return 'custom_general.twig_extension';
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
}