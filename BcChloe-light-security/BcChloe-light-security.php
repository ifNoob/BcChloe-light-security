<?php
/*
Plugin Name: BcChloe light security
Plugin URI: https://github.com/ifNoob/BcChloe-protection
Description: WordPress light security | nosniff header | xss protection header | not iframe | Protected Page Cookie | Disable Pingbacks | Self Pingbacks
Author: BcChloe
Author URI: https://bcchloe.jp
Text Domain: bcchloe-light-security
Version: 1.0
License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

// Exit If Accessed Directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**==========================
* Define Constants
===========================*/
define( 'BC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BC_PLUGIN_URL', plugins_url( '' , __FILE__) );
//global $foo;

	add_action( 'init', array( 'BcChloe_Light_Security', 'bcchloe_light_security_init' ) );

class BcChloe_Light_Security {

    protected $nonce_name = 'bcchloe-light-security';
    protected $post_url = '';

		public $plugin_version = '1.0';

    public static function bcchloe_light_security_init() {
        new self;
    }

	public function __construct() {
    add_action( 'init', array( &$this, 'bc_nosniff_header' ));
    add_action( 'init', array( &$this, 'bc_xss_header' ));
    add_action( 'init', array( &$this, 'bc_not_iframe' ));
    add_action( 'init', array( &$this, 'bc_protected_cookie' ));
    add_action( 'init', array( &$this, 'bc_pingbacks' ));
    add_action( 'init', array( &$this, 'bc_self_pingbacks' ));
	}


/**----------------
* Add nosniff header
-----------------*/
 function bc_nosniff_header() {
   header('X-Content-Type-Options: nosniff');
 }

/**----------------
* Add xss protection header
-----------------*/
 function bc_xss_header() {
  header('X-XSS-Protection: 1; mode=block');
 }

/**----------------
* Prevent embedding inside an iframe
-----------------*/
  function bc_not_iframe() {

    header('X-Frame-Options: SAMEORIGIN');
    add_action('wp_head', array($this, 'oldMethod'));
  }
  function oldMethod() {
    if (!is_preview()) {
    echo "\n<script type=\"text/javascript\">";
    echo "\n<!--";
    echo "\nif (parent.frames.length > 0) { parent.location.href = location.href; }";
    echo "\n-->";
    echo "\n</script>\n\n";
    }
  }

/**----------------
* Expire Protected Page Cookie
-----------------*/
 function bc_protected_cookie() {
  add_action( 'wp', array( $this, '_do' ) );
 }
 function _do() {
    if ( isset( $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] ) ) {
      setcookie( 'wp-postpass_' . COOKIEHASH, '', 0, COOKIEPATH );
    }
  }

/**----------------
* Disable Pingbacks
-----------------*/
  function bc_pingbacks() {
    add_filter('xmlrpc_enabled', '__return_false');
    add_filter( 'xmlrpc_methods', array($this, 'Remove_Pingback_Method') );
    if(!function_exists('remove_pingback_url')) {
      function remove_pingback_url( $output, $show ) {
        if ( $show == 'pingback_url' ) {
          $output = '';
        }
        return $output;
      }
    }
    add_filter( 'bloginfo_url', 'remove_pingback_url', 10, 2 );
    add_filter('wp_headers', array($this, 'remove_pingback'));
  }
  function remove_pingback( $headers ) {
    unset($headers['X-Pingback']);
    return $headers;
  }
  function Remove_Pingback_Method( $methods ) {
    unset( $methods['pingback.ping'] );
    unset( $methods['pingback.extensions.getPingbacks'] );
    return $methods;
  }

/**----------------
* isable Self Pingbacks
-----------------*/
  function bc_self_pingbacks() {
    add_action( 'pre_ping', array(&$this, 'bc_do') );
  }
  function bc_do( &$links ) {
    $home = get_option( 'home' );
    foreach ( $links as $l => $link ) {
      if ( 0 === strpos( $link, $home ) ) {
        unset( $links[ $l ] );
      }
    }
  }

} // end BcChloe_Protection

if ( class_exists( 'BcChloe_Light_Security' ) ) {
  new BcChloe_Light_Security();
}

?>