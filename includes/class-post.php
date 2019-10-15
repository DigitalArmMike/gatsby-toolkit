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
 * Hooks saving and updating posts.
 */
class GT_Post {

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
		add_action( 'save_post', array( $this, 'save_post' ), 10, 3 );
		add_action( 'trash_post', array( $this, 'trash_post' ), 10, 1 );
		add_action( 'wp_insert_post_data', array( $this, 'insert_post' ), 10, 3 );
	}

	/**
	 * Updates "deploy" status on post update
	 *
	 * @param object $data the $_POST request.
	 * @param object $post the post being updated.
	 *
	 * @return object
	 */
	public function insert_post( $data, $post ) {
		if (
			isset( $post['post_status'] ) && 'auto-draft' === $post['post_status'] ||
			defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ||
			defined( 'DOING_AJAX' ) && DOING_AJAX
			) {
			return $data;
		}

		// If it's a deploy, make sure it's set to publish.
		if ( isset( $post['deploy'] ) ) {
			$data['post_status'] = 'publish';
		}

		return $data;
	}

	/**
	 * Save post callback
	 *
	 * @param int     $post_id The post ID.
	 * @param object  $post    The post object.
	 * @param boolean $update  Is this an update.
	 * @return void
	 */
	public function save_post( $post_id, $post, $update ) {
		GT_POST::update( $post );
	}

	/**
	 * Trash post callback
	 *
	 * @param int $post_id The post id.
	 * @return void
	 */
	public function trash_post( $post_id ) {
		$post = get_post( $post_id );
		GT_POST::update( $post );
	}

	/**
	 * Update post meta and call build hooks(s)
	 *
	 * @param object $post The post being updated.
	 * @return void
	 */
	public function update( $post ) {
		if (
			isset( $post->post_status ) && 'auto-draft' === $post->post_status ||
			defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ||
			defined( 'DOING_AJAX' ) && DOING_AJAX
			) {
			return;
		}

		$lb_netlifly    = get_option( 'gatsby_toolkit' );
		$has_prod_hook  = (bool) $lb_netlifly['production_buildhook'];

		// Prod.
		if ( $has_prod_hook ) {
			$netlifly = new GT_Netlifly( 'production' );
			$netlifly->call_build_hook();
		}
	}

}
