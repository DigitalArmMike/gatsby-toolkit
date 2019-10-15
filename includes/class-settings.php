<?php
/**
 * LittleBot settings pages
 *
 * @author      Justin W Hall
 * @category    Settings
 * @package     LittleBot Invoices/Settings
 * @version     0.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings & Settings page
 */
class GT_Settings {
	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Kick it off.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
		add_action( 'admin_notices', array( $this, 'maybe_show_no_hooks_notice' ) );
	}

	/**
	 * Create the options page
	 *
	 * @return void
	 */
	public function add_plugin_page() {
		add_options_page(
			'Settings Admin',
			'Gatsby Toolkit',
			'manage_options',
			'gatsby-toolkit',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Renders the plugin options page.
	 *
	 * @return void
	 */
	public function create_admin_page() {
		// Set class property.
		$this->options = get_option( 'gatsby_toolkit' );

		?>
		<div class="wrap">
			<h1>Gatsby Toolkit</h1>
			<form method="post" action="options.php">
			<?php
				// This prints out all hidden setting fields.
				settings_fields( 'build_group' );
				do_settings_sections( 'gatsby-toolkit' );
				submit_button();
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Registers plugins options.
	 *
	 * @return void
	 */
	public function page_init() {
		register_setting(
			'build_group',
			'gatsby_toolkit',
			array( $this, 'sanitize' )
		);

		add_settings_section(
			'setting_section_id',
			false,
			false,
			'gatsby-toolkit'
		);

		add_settings_field(
			'production_buildhook',
			'Build Hook',
			array( $this, 'prod_callback' ),
			'gatsby-toolkit',
			'setting_section_id'
		);
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys.
	 */
	public function sanitize( $input ) {
		$new_input = array();
		if ( isset( $input['production_buildhook'] ) ) {
			$new_input['production_buildhook'] = sanitize_text_field( $input['production_buildhook'] );
		}

		return $new_input;
	}

	/**
	 * Renders productions input option.
	 *
	 * @return void
	 */
	public function prod_callback() {
		printf(
			'<input type="text" id="prod_buildhook" name="gatsby_toolkit[production_buildhook]" value="%s" style="min-width:450px;"/>',
			isset( $this->options['production_buildhook'] ) ? esc_attr( $this->options['production_buildhook'] ) : ''
		);
	}

	/**
	 * Show warning if no hooks.
	 *
	 * @return void
	 */
	public function maybe_show_no_hooks_notice() {
		$options = get_option( 'gatsby_toolkit' );
		if ( $options && strlen( $options['production_buildhook'] ) ) {
			return;
		}

		$class        = 'notice notice-warning is-dismissible';
		$message      = __( 'No buildhook added. Your site will not deploy when publishing.', 'gatsby-toolkit' );
		$link_text    = __( 'You can add on one the options page', 'gatsby-toolkit' );
		$options_page = get_admin_url() . 'options-general.php?page=gatsby-toolkit';

		printf(
			'<div class="%1$s"><p>%2$s <a href="%3$s">%4$s</a></p></div>',
			esc_attr( $class ),
			esc_html( $message ),
			esc_html( $options_page ),
			esc_html( $link_text )
		);
	}

}
