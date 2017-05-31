<?php
/**
 * groups-login-redirect.php
 *
 * Copyright (c) 2011,2012 Antonio Blanco http://www.eggemplo.com
 *
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author Antonio Blanco ( @eggemplo )
 * @package groups-login-redirect
 * @since groups-login-redirect 1.0.0
 *
 * Plugin Name: Groups Login Redirect
 * Plugin URI: http://www.eggemplo.com
 * Description: Groups Login Redirect
 * Version: 1.0
 * Author: eggemplo
 * Author URI: http://www.eggemplo.com
 * License: GPLv3
 */

define( 'GROUPS_LOGIN_REDIRECT_DOMAIN', 'groups-login-redirect' );
define( 'GROUPS_LOGIN_REDIRECT_PLUGIN_NAME', 'groups-login-redirect' );

define( 'GROUPS_LOGIN_REDIRECT_FILE', __FILE__ );

define( 'GROUPS_LOGIN_REDIRECT_PLUGIN_URL', plugin_dir_url( GROUPS_LOGIN_REDIRECT_FILE ) );

class GroupsLoginRedirect_Plugin {

	public static function init() {

		load_plugin_textdomain( GROUPS_LOGIN_REDIRECT_DOMAIN, null, GROUPS_LOGIN_REDIRECT_PLUGIN_NAME . '/languages' );

		register_activation_hook( GROUPS_LOGIN_REDIRECT_FILE, array( __CLASS__, 'activate' ) );
		register_deactivation_hook( GROUPS_LOGIN_REDIRECT_FILE, array( __CLASS__, 'deactivate' ) );

		register_uninstall_hook( GROUPS_LOGIN_REDIRECT_FILE, array( __CLASS__, 'uninstall' ) );

		add_action( 'init', array( __CLASS__, 'wp_init' ) );

	}

	public static function wp_init() {

		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ), 40 );

		add_filter( 'login_redirect', array( __CLASS__, 'login_redirect' ), 10, 3 );

	}

	/**
	 * Adds the admin section.
	 */
	public static function admin_menu() {
		$admin_page = add_menu_page(
				__( 'Groups Login Redirect' ),
				__( 'Groups Login Redirect' ),
				'manage_options',
				'groups-login-redirect',
				array( __CLASS__, 'groups_login_redirect_menu_settings' )
		);
	}

	public static function groups_login_redirect_menu_settings () {
	?>
		<div class="wrap">
		<h2><?php echo __( 'Groups Login Redirect', GROUPS_LOGIN_REDIRECT_DOMAIN ); ?></h2>
		<?php

		$groups = Groups_Group::get_groups();

		$alert = "";

		if (isset ( $_POST ['submit'] )) {
			$alert = __ ( "Saved", GROUPS_LOGIN_REDIRECT_DOMAIN );

			if ( isset( $_POST["groups_login_redirect_url"] ) ) {
				update_option( "groups_login_redirect_url", $_POST["groups_login_redirect_url"] );
			} else {
				update_option( "groups_login_redirect_url", '' );
			}

			if ( sizeof( $groups ) > 0 ) {
				foreach ( $groups as $group ) {
					if ( isset( $_POST[ "groups_login_redirect_url_" . $group->group_id ] ) ) {
						update_option( "groups_login_redirect_url_" . $group->group_id, $_POST[ "groups_login_redirect_url_" . $group->group_id ] );
					} else {
						update_option( "groups_login_redirect_url_" . $group->group_id, '' );
					}
				}
			}

			if ($alert != "")
				echo '<div style="background-color: #ffffe0;border: 1px solid #993;padding: 1em;margin-right: 1em;">' . $alert . '</div>';
		}

		?>
		<div class="wrap" style="border: 1px solid #ccc; padding: 10px;">
		<h3><?php echo __( 'URL destinations', GROUPS_LOGIN_REDIRECT_DOMAIN ); ?></h3>
		<form method="post" action="">
			<table class="form-table">
				<tr valign="top">
				<th scope="row"><strong><?php echo __( 'Default URL', GROUPS_LOGIN_REDIRECT_DOMAIN ); ?></strong></th>
				<td>
					<?php
					$value = get_option ( "groups_login_redirect_url", '' );
					?>
					<input type="text" name="groups_login_redirect_url" value="<?php echo $value; ?>" />
				</td>
				</tr>

				<?php
				if ( sizeof( $groups ) > 0 ) {
					foreach ( $groups as $group ) {
					?>
					<tr valign="top">
					<th scope="row"><strong><?php echo $group->name; ?></strong></th>
					<td>
						<?php
						$value = get_option ( "groups_login_redirect_url_" . $group->group_id, '' );
						?>
						<input type="text" name="groups_login_redirect_url_<?php echo $group->group_id; ?>" value="<?php echo $value; ?>" />
					</td>
					</tr>
					<?php 
					}
				}
				?>
			</table>
			
			<?php submit_button( __( "Save", GROUPS_LOGIN_REDIRECT_DOMAIN ) ); ?>
			<?php settings_fields( 'groups-login-redirect' ); ?>
		</form>
		</div>
		</div>
		<?php
	}

	public static function login_redirect ( $redirect_to, $requested_redirect_to, $user ) {

		$groups_default_login_redirect = get_option( 'groups_login_redirect_url', get_admin_url() );
		$redirect_url = $groups_default_login_redirect;

		$groups_user = new Groups_User( $user->ID );
		$user_group_ids = $groups_user->group_ids;

		if ( sizeof( $user_group_ids ) > 0 ) {
			$user_group_id = end( $user_group_ids );
			$redirect_url = get_option( 'groups_login_redirect_url_' . $user_group_id, $groups_default_login_redirect );
		}

		return $redirect_url;
	}
}
GroupsLoginRedirect_Plugin::init();
