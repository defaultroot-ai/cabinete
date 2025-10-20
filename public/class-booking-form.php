<?php
if (!defined('ABSPATH')) { exit; }

class MBS_Booking_Form {
    private static $instance = null;
    public static function get_instance() {
        if (null === self::$instance) { self::$instance = new self(); }
        return self::$instance;
    }
    private function __construct() {
        // Register shortcode on frontend
        add_action('init', array($this, 'register_shortcodes'));
    }

    public function register_shortcodes() {
        add_shortcode('medical_booking', array($this, 'shortcode_booking'));
    }

    public function shortcode_booking($atts = array(), $content = '') {
        // Enqueue React dependencies directly
        wp_enqueue_script('react', 'https://unpkg.com/react@18/umd/react.production.min.js', array(), '18', true);
        wp_enqueue_script('react-dom', 'https://unpkg.com/react-dom@18/umd/react-dom.production.min.js', array('react'), '18', true);
        wp_enqueue_script('babel-standalone', 'https://unpkg.com/@babel/standalone/babel.min.js', array(), null, true);
        wp_enqueue_script('tailwind-cdn', 'https://cdn.tailwindcss.com', array(), null, false);
        
        // Enqueue booking component
        wp_enqueue_script('mbs-booking-component', MBS_PLUGIN_URL . 'assets/js/booking-component.js', array('react', 'react-dom', 'babel-standalone'), MBS_VERSION, true);
        
        // Output full-width container
        ob_start();
        ?>
        <div id="medical-booking-root" style="width:100vw;max-width:100vw;margin-left:calc(50% - 50vw);margin-right:calc(50% - 50vw);box-sizing:border-box;"></div>
        <?php
        return ob_get_clean();
    }
}
