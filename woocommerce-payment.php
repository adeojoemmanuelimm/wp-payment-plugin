<?php
/**
* Plugin Name: test-plugin
* Plugin URI: https://www.your-site.com/
* Description: Test.
* Version: 0.1
* Author: your-name
* Author URI: https://www.your-site.com/
**/


if (!defined('ABSPATH')) {
  exit;
}

define('MPT_WC_PLUGIN_FILE', __FILE__);
define('MPT_WC_DIR_PATH', plugin_dir_path(MPT_WC_PLUGIN_FILE));



function mpt_woocommerce_payment_init()
{

  if (!class_exists('WC_Payment_Gateway'))
    return;

  require_once(MPT_WC_DIR_PATH . 'includes/class.mpt_wc_payment_gateway.php');

  // include subscription if exists
  if (class_exists('WC_Subscriptions_Order') && class_exists('WC_Payment_Gateway_CC')) {

    require_once(MPT_WC_DIR_PATH . 'includes/class.mpt_wc_subscription_payment.php');

  }

  add_filter('woocommerce_payment_gateways', 'mpt_woocommerce_add_payment_gateway', 99);
}
add_action('plugins_loaded', 'mpt_woocommerce_payment_init', 99);

/**
 * Add the Settings link to the plugin
 *
 * @param  Array $links Existing links on the plugin page
 *
 * @return Array          Existing links with our settings link added
 */
function mpt_plugin_action_links($links)
{

  $payment_settings_url = esc_url(get_admin_url(null, 'admin.php?page=wc-settings&tab=checkout&section=payment'));
  array_unshift($links, "<a title='Payment Settings Page' href='$payment_settings_url'>Settings</a>");

  return $links;

}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'mpt_plugin_action_links');

/**
 * Add the Gateway to WooCommerce
 *
 * @param  Array $methods Existing gateways in WooCommerce
 *
 * @return Array          Gateway list with our gateway added
 */
function mpt_woocommerce_add_payment_gateway($methods)
{

  if (class_exists('WC_Subscriptions_Order') && class_exists('WC_Payment_Gateway_CC')) {

    $methods[] = 'MPT_WC_Payment_Gateway_Subscriptions';

  }
  else {

    $methods[] = 'MPT_WC_Payment_Gateway';
  }

  return $methods;

}


?>
