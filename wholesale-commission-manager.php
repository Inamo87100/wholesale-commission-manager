<?php
/**
 * Plugin Name: Wholesale Commission Manager ULTIMATE
 * Description: Gestione commissioni affiliati per WooCommerce in modo semplice ed efficace.
 * Version: 5.0
 * Author: Il tuo Nome
 * Text Domain: wholesale-commission-manager
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Costanti configurabili
define('WCM_PRODUCT_ID', 456);         // Cambia questo ID col tuo Product ID!
define('WCM_COMMISSION_RATE', 0.20);   // 0.20 = 20%

// Inclusioni dei moduli principali
foreach (['roles', 'commissions', 'dashboard', 'ajax', 'cron', 'endpoint'] as $file) {
    $path = plugin_dir_path(__FILE__) . "includes/class-wcm-{$file}.php";
    if (file_exists($path)) require_once $path;
}

/**
 * (Facoltativo/Consigliato)
 * Carica i file di traduzione se vuoi localizzare:
 */
add_action('plugins_loaded', function() {
    load_plugin_textdomain('wholesale-commission-manager', false, dirname(plugin_basename(__FILE__)) . '/languages/');
});
