<?php
/**
 * Main Plugin_MFBJOBSAPI Class
 *
 * @package Plugin MFBJOBSAPI
 */
class Plugin_MFBJOBSAPI {

    /**
	 * Call all Functions to setup the Plugin
	 *
	 * @uses Plugin_MFBJOBSAPI::constants() Setup the constants needed
	 * @uses Plugin_MFBJOBSAPI::includes() Include the required files
	 * @uses Plugin_MFBJOBSAPI::setup_actions() Setup the hooks and actions
	 * @return void
	 */
	static function setup() {
		// Setup Constants.
		self::constants();
		// Setup Translation.
		add_action( 'plugins_loaded', array( __CLASS__, 'translation' ) );
		// Include Files.
		self::includes();
		// Setup Action Hooks.
		self::setup_actions();
	}
    
	/**
	 * Setup plugin constants
	 *
	 * @return void
	 */
	static function constants() {
		// Define Plugin Name.
		define( 'PLUGIN_MFBJOBSAPI_NAME', 'Plugin MFBJOBSAPI' );
		// Define Version Number.
		define( 'PLUGIN_MFBJOBSAPI_VERSION', 1.0 );
		// Plugin Folder Path.
		define( 'PLUGIN_MFBJOBSAPI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		// Plugin Folder URL.
		define( 'PLUGIN_MFBJOBSAPI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		// Plugin Root File.
		define( 'PLUGIN_MFBJOBSAPI_PLUGIN_FILE', __FILE__ );
        
        define( 'PLUGIN_MFBJOBSAPI_DB', 'mfbjobsapi' );
        define( 'PLUGIN_MFBJOBSAPI_DBUSER', 'root' );
        define( 'PLUGIN_MFBJOBSAPI_DBPW', 'root' );
        define( 'PLUGIN_MFBJOBSAPI_DBHOST', 'localhost' );
	}
    
    
	/**
	 * Load Translation File
	 *
	 * @return void
	 */
	static function translation() {
		load_plugin_textdomain( 'plugin-MFBJOBSAPI', false, dirname( plugin_basename( PLUGIN_MFBJOBSAPI_PLUGIN_FILE ) ) . '/languages/' );
	}
    
    
	/**
	 * Include required files
	 *
	 * @return void
	 */
	static function includes() {
		// Include Admin Classes.
		//require_once PLUGIN_MFBJOBSAPI_PLUGIN_DIR . '/includes/admin/class-plugin-MFBJOBSAPI-settings.php';
		//require_once PLUGIN_MFBJOBSAPI_PLUGIN_DIR . '/includes/admin/class-plugin-MFBJOBSAPI-settings-page.php';
		// Include Plugin Classes.
		require_once PLUGIN_MFBJOBSAPI_PLUGIN_DIR . '/includes/class-mfbjobsapi-plugin.php';
        require_once PLUGIN_MFBJOBSAPI_PLUGIN_DIR . '/includes/class-mfbjobsapi-backend.php';
	}
    
    
	/**
	 * Setup Action Hooks
	 *
	 * @see https://codex.wordpress.org/Function_Reference/add_action WordPress Codex
	 * @return void
	 */
	static function setup_actions() {
		// Enqueue Stylesheet.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_styles' ) );
        // Enqueue Stylesheet.
		add_action( 'admin_init', array( __CLASS__, 'enqueue_styles' ) );
	}
    
	/**
	 * Enqueue Stylesheet
	 *
	 * @return void
	 */
	static function enqueue_styles() {
		// Enqueue Plugin Stylesheet.
		wp_enqueue_style( 'plugin-MFBJOBSAPI', PLUGIN_MFBJOBSAPI_PLUGIN_URL . 'assets/css/mfbjobsapi.css', array(), PLUGIN_MFBJOBSAPI_VERSION );
        wp_enqueue_script( 'mfbjobapi', PLUGIN_MFBJOBSAPI_PLUGIN_URL . 'assets/js/mfbjobsapi.js', array(), '1.0.0', true );
	}
    
  
    //Plugin_MFBJOBSAPI::setup();
} 
Plugin_MFBJOBSAPI::setup();
