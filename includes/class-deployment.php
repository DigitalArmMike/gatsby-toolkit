<?php
/**
 * LittleBot Netlifly
 *
 * A class for all plugin metaboxs.
 *
 * @version   0.9.0
 * @category  Class
 * @package   LittleBotNetlifly
 * @author    Justin W Hall
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles deployment
 */
class GT_Deployment {

	/**
	 * Parent plugin class.
	 *
	 * @var object
	 */
	protected $plugin = null;

	/**
	 * Kick it off.
	 *
	 * @param object $plugin the parent class.
	 */
	function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Attach hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_bar_menu', array( $this, 'add_deployment_buttons' ), 100 );
		add_action( 'init', array( $this, 'maybe_deploy' ) );
	}

	/**
	 * Add deployment buttons
	 */
	public function add_deployment_buttons( $admin_bar ) {
		$admin_bar->add_menu( array(
			'id' => 'deploy-production',
			'title' => 'Deploy Production',
			'href' => add_query_arg( 'deploy_production', 1, admin_url( 'options-general.php?page=gatsby-toolkit' ) ),
		));

		$admin_bar->add_menu( array(
			'id' => 'deploy-staging',
			'title' => 'Deploy Staging',
			'href' => add_query_arg( 'deploy_staging', 1, admin_url( 'options-general.php?page=gatsby-toolkit' ) ),
		));
	}

	/**
	 * maybe_deploy
	 *
	 * @return void
	 */
	public function maybe_deploy() {
		if ( ! is_admin() ) {
			return;
		}

		$lb_netlifly    = get_option( 'gatsby_toolkit' );
		$has_prod_hook  = (bool) $lb_netlifly['production_buildhook'];
		$has_staging_hook  = (bool) $lb_netlifly['staging_buildhook'];

		// Prod.
		if ( ! empty( $_GET['deploy_production'] ) && $has_prod_hook ) {
			$netlifly = new GT_Netlifly( 'production' );
			$netlifly->call_build_hook();
		}

		// Staging.
		if ( ! empty( $_GET['deploy_staging'] ) && $has_staging_hook ) {
			$netlifly = new GT_Netlifly( 'staging' );
			$netlifly->call_build_hook();
		}
	}

}
