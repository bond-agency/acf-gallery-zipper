<?php
/**
 * Plugin Name: ACF Gallery Zipper
 * Plugin URI: https://github.com/bond-agency/acf-gallery-zipper
 * GitHub Plugin URI: https://github.com/bond-agency/acf-gallery-zipper
 * Description: Plugin creates a REST endpoint for zipping an ACF gallery field contents.
 * Version: 0.1.0
 * Author: Bond Agency
 * Author uri: https://bond-agency.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.html
 * Text Domain: acf-gallery-zipper
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

// Run installation function only once on activation.
register_activation_hook( __FILE__, [ 'ACF_Gallery_Zipper', 'on_activation' ] );
register_deactivation_hook( __FILE__, [ 'ACF_Gallery_Zipper', 'on_deactivation' ] );
add_action( 'plugins_loaded', [ 'ACF_Gallery_Zipper', 'init' ] );

class ACF_Gallery_Zipper {

  protected static $instance;
  protected static $version                 = '1.0.0'; // The current version of the plugin.
  protected static $min_wp_version          = '4.7.5'; // Minimum required WordPress version.
  protected static $min_php_version         = '7.0'; // Minimum required PHP version.
  protected static $class_dependencies      = [ 'acf' ]; // Class dependencies of the plugin.
  protected static $required_php_extensions = []; // PHP extensions required by the plugin.
  protected static $namespace               = 'acf-gallery-zipper';
  protected static $api_version             = 'v1';



  /**
   * Plugin hooks goes here.
   */
  public function __construct() {
    add_action( 'rest_api_init', array( $this, 'register_zip_endpoint' ) );
  }



  /**
   * Create instance of the plugin.
   */
  public static function init() {
    if ( is_null( self::$instance ) ) {
      self::$instance = new self();
    }
    return self::$instance;
  }



  /**
   * Register endpoint for zip creation
   */
  public function register_zip_endpoint() {
    register_rest_route(
      self::$namespace . '/' . self::$api_version, '/zip/(?P<id>\d+)', array(
          'methods'  => WP_REST_Server::READABLE,
          'callback' => array( $this, 'zip_post_gallery_field' ),
      )
    );
  }



  /**
   * Function creates zip file from given posts ACF gallery field.
   *
   * @param WP_REST_Request $request
   */
  public function zip_post_gallery_field( $request ) {
    $post_id = $request->get_param( 'id' );

    $default_post_type = 'post';
    $post_type         = apply_filters( 'acf_gallery_zipper_post_type', $default_post_type );

    $default_field_name = 'gallery';
    $field_name         = apply_filters( 'acf_gallery_zipper_field_name', $default_field_name );

    if ( get_post_type( $post_id ) !== $post_type ) {
      return new WP_Error(
        'invalid_post_type',
        'The defined post type didn\'t match with the given post id.',
        array( 'status' => 400 )
      );
    }

    $post_slug   = get_post_field( 'post_name', $post_id );
    $field       = get_field( $field_name, $post_id );
    $media_paths = $this->get_file_paths( $field );
    $zip_path    = $this->create_zip_file( $media_paths, $post_slug );

    header( 'Content-disposition: attachment; filename="' . basename( $zip_path ) . '"' );
    header( 'Content-type: application/zip' );
    readfile( $zip_path );
    unlink( $zip_path );
    return true;
  }



  /**
   * Function returns file paths from ACF gallery field contents.
   *
   * @param Array $gallery_field  - ACF gallery field
   * @return Array $paths         - List of file paths
   */
  protected function get_file_paths( $gallery_field ) {
    $paths = [];
    foreach ( $gallery_field as $media ) {
      $paths[] = get_attached_file( $media['id'] );
    }
    return $paths;
  }



  /**
   * Function creates zip file from list of file paths.
   *
   * @param Array $paths        - Array of file paths to zip.
   * @param String $filename    - Name for the zip file.
   * @return String $zip_path   - Path of the newly created zip file.
   */
  protected function create_zip_file( $paths, $filename ) {
    // create new zip object
    $zip      = new ZipArchive();
    $zip_path = plugin_dir_path( __FILE__ ) . 'zip_cache/' . $filename . '.zip';
    $zip->open( $zip_path, ZipArchive::CREATE );

    foreach ( $paths as $path ) {
      $zip->addFile( $path, basename( $path ) );
    }
    $zip->close();
    return $zip_path;
  }



  /**
   * Checks if plugin dependencies & requirements are met.
   */
  protected static function are_requirements_met() {
    // Check for WordPress version
    if ( version_compare( get_bloginfo( 'version' ), self::$min_wp_version, '<' ) ) {
      return false;
    }

    // Check the PHP version
    if ( version_compare( PHP_VERSION, self::$min_php_version, '<' ) ) {
      return false;
    }

    // Check PHP loaded extensions
    foreach ( self::$required_php_extensions as $ext ) {
      if ( ! extension_loaded( $ext ) ) {
        return false;
      }
    }

    // Check for required classes
    foreach ( self::$class_dependencies as $class_name ) {
      if ( ! class_exists( $class_name ) ) {
        return false;
      }
    }

    return true;
  }



  /**
   * Checks if plugin dependencies & requirements are met. If they are it doesn't
   * do anything if they aren't it will die.
   */
  public static function ensure_requirements_are_met() {
    if ( ! self::are_requirements_met() ) {
      deactivate_plugins( __FILE__ );
      $message  = "<p>Some of the plugin dependencies aren't met and the plugin can't be enabled. ";
      $message .= 'This plugin requires the followind dependencies:</p>';
      $message .= '<ul><li>Minimum WP version: ' . self::$min_wp_version . '</li>';
      $message .= '<li>Minimum PHP version: ' . self::$min_php_version . '</li>';
      $message .= '<li>Classes / plugins: ' . implode( ', ', self::$class_dependencies ) . '</li>';
      $message .= '<li>PHP extensions: ' . implode( ', ', self::$required_php_extensions ) . '</li></ul>';
      wp_die( $message );
    }
  }



  /**
   * A function that's run once when the plugin is activated. We just create
   * a scheduled run for the press release update.
   */
  public static function on_activation() {
    // Security stuff.
    if ( ! current_user_can( 'activate_plugins' ) ) {
      return;
    }

    $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
    check_admin_referer( "activate-plugin_{$plugin}" );

    // Check requirements.
    self::ensure_requirements_are_met();

    // Your activation code below this line.
  }



  /**
   * A function that's run once when the plugin is deactivated. We just delete
   * the scheduled run for the press release update.
   */
  public static function on_deactivation() {
    // Security stuff.
    if ( ! current_user_can( 'activate_plugins' ) ) {
      return;
    }

    $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
    check_admin_referer( "deactivate-plugin_{$plugin}" );

    // Your deactivation code below this line.
  }

} // Class ends
