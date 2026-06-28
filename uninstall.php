<?php
/**
 * Uninstall cleanup.
 *
 * @package RedirectManager
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'redirect-manager_options' );
delete_option( 'redirect-manager_404s' );
