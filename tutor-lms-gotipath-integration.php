<?php

/**
 * Plugin Name: Tutor LMS Gotipath Integration
 * Plugin URI: https://github.com/GotipathTeam/tutor-lms-gotipath-integration
 * Description: Tutor LMS Gotipath integration allows you to host your lesson videos on Tutor LMS using Gotipath Stream. Use this integration to load up and play your meticulously crafted course videos to enhance the experience for students.
 * Author: Gotipath
 * Version: 1.0.0
 * Author URI: https://gotipath.com
 * Requires at least: 5.3
 * Tested up to: 6.4
 * License: GPLv3
 * Text Domain: tutor-lms-gotipath-integration
 * Domain Path: /languages
 *
 * @package TutorLMSGotipathIntegration
 */

use Tutor\GotipathIntegration\AdminNotice\AdminNotice;
use Tutor\GotipathIntegration\Integration\Gotipath;

if (!class_exists('TutorLMSGotipathIntegration')) {

	/**
	 * PluginStarter main class that trigger the plugin
	 */
	final class TutorLMSGotipathIntegration
	{

		/**
		 * Plugin meta data
		 *
		 * @since v1.0.0
		 *
		 * @var $plugin_data
		 */
		private static $meta_data = array();

		/**
		 * Plugin instance
		 *
		 * @since v1.0.0
		 *
		 * @var $instance
		 */
		public static $instance = null;

		/**
		 * Register hooks and load dependent files
		 *
		 * @since v1.0.0
		 *
		 * @return void
		 */
		public function __construct()
		{
			if (file_exists(__DIR__ . '/vendor/autoload.php')) {
				include_once __DIR__ . '/vendor/autoload.php';
			}
			add_action('plugins_loaded', array($this, 'load_packages'));
			add_action('init', array(__CLASS__, 'load_textdomain'));
		}

		/**
		 * Plugin meta data
		 *
		 * @since v1.0.0
		 *
		 * @return array  contains plugin meta data
		 */
		public static function meta_data()
		{
			self::$meta_data['tutor_req_ver'] = '2.1.2';
			self::$meta_data['url']           = plugin_dir_url(__FILE__);
			self::$meta_data['path']          = plugin_dir_path(__FILE__);
			self::$meta_data['basename']      = plugin_basename(__FILE__);
			self::$meta_data['templates']     = trailingslashit(plugin_dir_path(__FILE__) . 'templates');
			self::$meta_data['views']         = trailingslashit(plugin_dir_path(__FILE__) . 'views');
			self::$meta_data['assets']        = trailingslashit(plugin_dir_url(__FILE__) . 'assets');
			return self::$meta_data;
		}

		/**
		 * Create and return instance of this plugin
		 *
		 * @return self  instance of plugin
		 */
		public static function instance()
		{
			if (null === self::$instance) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Load packages
		 *
		 * @return void
		 */
		public function load_packages()
		{
			// If tutor is not active then load notice only.
			new AdminNotice();
			if (function_exists('tutor') && AdminNotice::is_tutor_core_has_req_version()) {
				new Gotipath();
			}
		}

		/**
		 * Load plugin text domain
		 *
		 * @return void
		 */
		public static function load_textdomain()
		{
			load_plugin_textdomain('easy-poll', false, plugin_dir_path(__FILE__) . 'languages/');
		}
	}
	// trigger.
	TutorLMSGotipathIntegration::instance();
}
