<?php
/*
Plugin Name: Custom Order Discount
Description: Set a custom order discount and show it in the cart and checkout page.
Version: 1.0
Author: Amin Azimi
Author URI: https://github.com/aminazimi72
Text Domain: custom-order-discount
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Check if WooCommerce is active
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    class Custom_Order_Discount {

        public function __construct() {
            // Load translations
            add_action('plugins_loaded', array($this, 'load_textdomain'));

            add_action('admin_menu', array($this, 'add_plugin_page'));
            add_action('admin_init', array($this, 'page_init'));
            add_action('woocommerce_cart_calculate_fees', array($this, 'apply_discount'));
            add_action('woocommerce_before_cart', array($this, 'display_notice'));
        }

        public function load_textdomain() {
            load_plugin_textdomain('custom-order-discount', false, dirname(plugin_basename(__FILE__)) . '/languages');
        }

        public function add_plugin_page() {
            add_submenu_page(
                'woocommerce',
                __('Custom Order Discount', 'custom-order-discount'),
                __('Custom Order Discount', 'custom-order-discount'),
                'manage_options',
                'custom-order-discount',
                array($this, 'create_admin_page')
            );
        }

        public function create_admin_page() {
            ?>
            <div class="wrap">
                <h2><?php echo esc_html__('Custom Order Discount Settings', 'custom-order-discount'); ?></h2>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('custom_order_discount_settings');
                    do_settings_sections('custom_order_discount');
                    submit_button();
                    ?>
                </form>
            </div>
            <?php
        }

        public function page_init() {
            register_setting(
                'custom_order_discount_settings',
                'custom_order_discount_enabled'
            );
            register_setting(
                'custom_order_discount_settings',
                'custom_order_discount_percent'
            );
            register_setting(
                'custom_order_discount_settings',
                'custom_order_discount_label'
            );
            register_setting(
                'custom_order_discount_settings',
                'custom_order_discount_notice'
            );

            add_settings_section(
                'custom_order_discount_section',
                __('Discount Settings', 'custom-order-discount'),
                array($this, 'print_section_info'),
                'custom_order_discount'
            );

            add_settings_field(
                'custom_order_discount_enabled',
                __('Enable Discount', 'custom-order-discount'),
                array($this, 'enable_discount_callback'),
                'custom_order_discount',
                'custom_order_discount_section'
            );

            add_settings_field(
                'custom_order_discount_percent',
                __('Discount Percent', 'custom-order-discount'),
                array($this, 'percent_callback'),
                'custom_order_discount',
                'custom_order_discount_section'
            );

            add_settings_field(
                'custom_order_discount_label',
                __('Discount Label', 'custom-order-discount'),
                array($this, 'label_callback'),
                'custom_order_discount',
                'custom_order_discount_section'
            );

            add_settings_field(
                'custom_order_discount_notice',
                __('Success Notice', 'custom-order-discount'),
                array($this, 'notice_callback'),
                'custom_order_discount',
                'custom_order_discount_section'
            );
        }

        public function print_section_info() {
            echo esc_html__('Enter your discount settings below:', 'custom-order-discount');
        }

        public function enable_discount_callback() {
            $enabled = (get_option('custom_order_discount_enabled',true) == 'on') ? 1 : 0;
            echo '<input type="checkbox" name="custom_order_discount_enabled" ' . checked(1, $enabled, false) . ' />';
        }

        public function percent_callback() {
            $percent = get_option('custom_order_discount_percent');
            echo '<input type="number" name="custom_order_discount_percent" value="' . esc_attr($percent) . '" />';
        }

        public function label_callback() {
            $label = get_option('custom_order_discount_label');
            echo '<input type="text" name="custom_order_discount_label" value="' . esc_attr($label) . '" />';
        }

        public function notice_callback() {
            $notice = get_option('custom_order_discount_notice');
            echo '<textarea name="custom_order_discount_notice" rows="3" cols="50">' . esc_attr($notice) . '</textarea>';
        }

        public function apply_discount() {
            $enabled = get_option('custom_order_discount_enabled');
            $percent = get_option('custom_order_discount_percent');

            if ($enabled && $percent > 0) {
                $discount = WC()->cart->subtotal * ($percent / 100);
                WC()->cart->add_fee(get_option('custom_order_discount_label'), -$discount);
            }
        }

        public function display_notice() {
            $enabled = get_option('custom_order_discount_enabled');
            $notice = get_option('custom_order_discount_notice');

            if ($enabled && !empty($notice)) {
                wc_print_notice($notice, 'success');
            }
        }
    }

    new Custom_Order_Discount();
} else {
    add_action('admin_notices', 'custom_order_discount_woocommerce_not_active');

    function custom_order_discount_woocommerce_not_active() {
        echo '<div class="error"><p>' . __('Custom Order Discount requires WooCommerce to be installed and active.', 'custom-order-discount') . '</p></div>';
    }
}
