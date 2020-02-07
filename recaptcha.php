<?php
/**
 * Plugin Name: WooCommerce Checkout reCaptcha
 * Plugin URI:
 * Description: Adds a Google no captcha reCaptcha to the Woocommerce Checkout
 * Version: 1.0
 * Author: George Arnall
 * Author URI: https://garnall.co.uk/
 * Requires at least: 4.6
 * Tested up to: 4.6
 *
 * Text Domain: woocommerce
 * Domain Path: /i18n/languages/
 *
 * @package WooCommerce
 * @category Add On
 * @author George Arnall
 */
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define("SITE_KEY", "XXX");
define("SECRET_KEY", "XXX");

/**
 * Check if WooCommerce is active
 **/
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    if (true) {
        add_action('wp_head', 'hook_recaptcha_js');
        function hook_recaptcha_js()
        {
            echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
        }
    }

    add_action('woocommerce_after_checkout_billing_form', 'woocommerce_checkout_recaptcha_field');

    function woocommerce_checkout_recaptcha_field()
    {
        echo '<div class="g-recaptcha" data-sitekey="'.SITE_KEY.'"></div>';
    }

    // validation
    add_action('woocommerce_checkout_process', 'my_custom_checkout_field_process');

    function my_custom_checkout_field_process()
    {
        // Check if set, if its not set add an error.
        if (! $_POST['g-recaptcha-response']) {
            wc_add_notice(__('reCaptcha not accepted! Please verify you are not a robot.'), 'error');
            return;
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';

        $fields_string = [
            'secret' => SECRET_KEY,
            'response' => $_POST['g-recaptcha-response'],
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];

        //open connection
        $ch = curl_init($url);

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        //execute post
        $ch_response = curl_exec($ch);


        //close connection
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($ch_response, 0, $header_size);
        $body = substr($ch_response, $header_size);

        curl_close($ch);


        $response = json_decode($body, true);

        if ($response['success'] == false) {
            wc_add_notice(__('reCaptcha not accepted! Please verify you are not a robot.'), 'error');
        }
    }
}
