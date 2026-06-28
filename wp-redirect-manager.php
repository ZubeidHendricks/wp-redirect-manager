<?php
/**
 * Plugin Name:       Redirect Manager
 * Plugin URI:        https://zubeidhendricks.dev/wp-plugins/redirect-manager
 * Description:        Create and manage 301/302/410 redirects from one simple screen, and catch 404s before visitors hit them.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.2
 * Author:            Zubeid Hendricks
 * Author URI:        https://zubeidhendricks.dev
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       redirect-manager
 *
 * @package RedirectManager
 */

defined( 'ABSPATH' ) || exit;

define( 'REDIRECT_MANAGER_VERSION', '1.0.0' );

require_once __DIR__ . '/includes/factory-core.php';

/**
 * Redirect Manager.
 */
final class RedirectManager extends ZubFactory_Plugin {

	protected function configure() {
		$this->slug    = 'redirect-manager';
		$this->title   = 'Redirect Manager';
		$this->version = REDIRECT_MANAGER_VERSION;
	}

	protected function settings_fields() {
		return array(
			'rules' => array(
				'label' => __( 'Redirect rules', 'redirect-manager' ),
				'type'  => 'textarea',
				'desc'  => __( 'One per line:  /old-path | /new-path | 301   (type is optional, defaults to 301; use 410 to mark gone).', 'redirect-manager' ),
			),
			'log404' => array(
				'label'    => __( '404 logging', 'redirect-manager' ),
				'type'     => 'checkbox',
				'cb_label' => __( 'Log the most recent 404 URLs to fix later', 'redirect-manager' ),
				'default'  => 1,
			),
			'auto_slug' => array(
				'label'    => __( 'Auto-redirect', 'redirect-manager' ),
				'type'     => 'checkbox',
				'cb_label' => __( 'Automatically redirect when a post slug changes', 'redirect-manager' ),
				'pro'      => true,
			),
		);
	}

	protected function hooks() {
		add_action( 'template_redirect', array( $this, 'maybe_redirect' ), 1 );
		add_action( 'template_redirect', array( $this, 'log_404' ), 99 );
	}

	/** Parse the textarea into [ source => [target, type] ]. */
	private function rules() {
		$raw   = (string) $this->option( 'rules', '' );
		$rules = array();
		foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
			$line = trim( $line );
			if ( '' === $line || 0 === strpos( $line, '#' ) ) {
				continue;
			}
			$parts  = array_map( 'trim', explode( '|', $line ) );
			$source = isset( $parts[0] ) ? $parts[0] : '';
			$target = isset( $parts[1] ) ? $parts[1] : '';
			$type   = isset( $parts[2] ) ? (int) $parts[2] : 301;
			if ( '' === $source ) {
				continue;
			}
			$rules[ untrailingslashit( $source ) ] = array( $target, $type ?: 301 );
		}
		return $rules;
	}

	public function maybe_redirect() {
		$request = untrailingslashit( wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) );
		$rules   = $this->rules();
		if ( ! isset( $rules[ $request ] ) ) {
			return;
		}
		list( $target, $type ) = $rules[ $request ];

		if ( 410 === $type ) {
			status_header( 410 );
			nocache_headers();
			wp_die(
				esc_html__( 'This content has been permanently removed.', 'redirect-manager' ),
				esc_html__( 'Gone', 'redirect-manager' ),
				array( 'response' => 410 )
			);
		}

		if ( '' === $target ) {
			return;
		}
		$dest = ( 0 === strpos( $target, 'http' ) ) ? $target : home_url( $target );
		wp_safe_redirect( $dest, in_array( $type, array( 301, 302, 307 ), true ) ? $type : 301 );
		exit;
	}

	/** Keep the last 50 unique 404 paths in an option for review. */
	public function log_404() {
		if ( ! is_404() || ! $this->option( 'log404', 1 ) ) {
			return;
		}
		$path = untrailingslashit( wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) );
		if ( '' === $path ) {
			return;
		}
		$log = get_option( 'redirect-manager_404s', array() );
		$log[ $path ] = ( isset( $log[ $path ] ) ? (int) $log[ $path ] : 0 ) + 1;
		if ( count( $log ) > 50 ) {
			arsort( $log );
			$log = array_slice( $log, 0, 50, true );
		}
		update_option( 'redirect-manager_404s', $log, false );
	}
}

add_action(
	'plugins_loaded',
	function () {
		( new RedirectManager( __FILE__ ) )->boot();
	}
);
