<?php
/**
 * Plugin Name: WooCommerce Field Remover
 * Plugin URI: https://
 * Description: Removes state and phone number fields from WooCommerce checkout page.
 * Version: 1.0.0
 * Author: Nikolay Djemerenov
 * Author URI: https://nikweb.eu
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.0
 * Tested up to: 6.4
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 * Text Domain: wc-field-remover
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}

/**
 * Main plugin class
 */
class WC_Field_Remover {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Don't call init() here to avoid double initialization
        // Initialization will be handled by the plugins_loaded hook
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('wc-field-remover', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Hook into WooCommerce checkout fields with priority
        add_filter('woocommerce_checkout_fields', array($this, 'remove_checkout_fields'), 10);
        
        // Add admin notice if WooCommerce is not active with priority
        add_action('admin_notices', array($this, 'woocommerce_missing_notice'), 10);
    }
    
    /**
     * Remove state and phone fields from checkout
     *
     * @param array $fields Checkout fields array
     * @return array Modified fields array
     */
    public function remove_checkout_fields($fields) {
        if (!is_array($fields)) {
            return $fields;
        }
        
        // Remove billing state field
        if (isset($fields['billing']) && is_array($fields['billing']) && isset($fields['billing']['billing_state'])) {
            unset($fields['billing']['billing_state']);
        }
        
        // Remove billing phone field
        if (isset($fields['billing']) && is_array($fields['billing']) && isset($fields['billing']['billing_phone'])) {
            unset($fields['billing']['billing_phone']);
        }
        
        // Remove shipping state field (if exists)
        if (isset($fields['shipping']) && is_array($fields['shipping']) && isset($fields['shipping']['shipping_state'])) {
            unset($fields['shipping']['shipping_state']);
        }
        
        return $fields;
    }
    
    /**
     * Display admin notice if WooCommerce is not active
     */
    public function woocommerce_missing_notice() {
        // Check if user has capability to see admin notices
        if (!current_user_can('manage_options')) {
            return;
        }
        
        if (!class_exists('WooCommerce')) {
            $class = 'notice notice-error is-dismissible';
            $message = __('WooCommerce Field Remover requires WooCommerce to be installed and active.', 'wc-field-remover');
            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
        }
    }
}

/**
 * Initialize the plugin
 */
function wc_field_remover_init() {
    new WC_Field_Remover();
}
add_action('plugins_loaded', 'wc_field_remover_init');

/**
 * Plugin activation hook
 */
register_activation_hook(__FILE__, 'wc_field_remover_activate');
function wc_field_remover_activate() {
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('This plugin requires WooCommerce to be installed and active.', 'wc-field-remover'));
    }
}

/**
 * Plugin deactivation hook
 */
register_deactivation_hook(__FILE__, 'wc_field_remover_deactivate');
function wc_field_remover_deactivate() {
    // Clean up if needed
    // Currently no cleanup required
}
