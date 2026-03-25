<?php
/**
 * Plugin Name: Wholesale Commission Manager ULTIMATE
 * Description: Gestione commissioni affiliati per WooCommerce in modo semplice ed efficace.
 * Version: 5.0
 * Author: Il tuo Nome
 * Text Domain: wholesale-commission-manager
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define('WCM_PRODUCT_ID', 456);
define('WCM_COMMISSION_RATE', 0.20);

foreach (['roles', 'commissions', 'dashboard', 'ajax', 'cron'] as $file) {
    require_once plugin_dir_path(__FILE__) . "includes/class-wcm-{$file}.php";
}
