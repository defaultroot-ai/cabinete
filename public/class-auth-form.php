<?php
/**
 * Auth Form Handler
 * Renders login/register form via shortcode
 * 
 * @package MedicalBookingSystem
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MBS_Auth_Form {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_shortcode('mbs_auth', array($this, 'shortcode_auth'));
    }
    
    /**
     * Shortcode handler for auth form
     * Usage: [mbs_auth]
     */
    public function shortcode_auth($atts) {
        // Start output buffering
        ob_start();
        
        // Enqueue scripts and styles
        $this->enqueue_assets();
        
        // Render container
        ?>
        <div id="medical-auth-root" class="mbs-auth-container" style="width: 100%; margin: 0 auto;"></div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Enqueue assets for auth form
     */
    private function enqueue_assets() {
        // Enqueue React and ReactDOM from CDN
        wp_enqueue_script(
            'react',
            'https://unpkg.com/react@18/umd/react.production.min.js',
            array(),
            '18.2.0',
            true
        );
        
        wp_enqueue_script(
            'react-dom',
            'https://unpkg.com/react-dom@18/umd/react-dom.production.min.js',
            array('react'),
            '18.2.0',
            true
        );
        
        // Enqueue Babel for JSX transformation
        wp_enqueue_script(
            'babel-standalone',
            'https://unpkg.com/@babel/standalone/babel.min.js',
            array(),
            '7.22.0',
            true
        );
        
        // Enqueue Tailwind CSS
        wp_enqueue_style(
            'tailwindcss',
            'https://cdn.tailwindcss.com',
            array(),
            '3.3.0'
        );
        
        // Enqueue auth component
        wp_enqueue_script(
            'mbs-auth-component',
            MBS_PLUGIN_URL . 'assets/js/auth-component.js',
            array('react', 'react-dom', 'babel-standalone'),
            MBS_VERSION,
            true
        );
        
        // Add Babel type attribute
        add_filter('script_loader_tag', array($this, 'add_type_attribute'), 10, 3);
    }
    
    /**
     * Add type="text/babel" to auth component script
     */
    public function add_type_attribute($tag, $handle, $src) {
        if ('mbs-auth-component' === $handle) {
            $tag = '<script type="text/babel" src="' . esc_url($src) . '"></script>';
        }
        return $tag;
    }
}

