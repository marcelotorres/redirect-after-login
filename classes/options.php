<?php
/**
 * MTRAL_Options class.
 *
 * Built settings page.
 *
 */
class MTRAL_Options {

	/**
	 * Settings tabs.
	 *
	 * @var array
	 */
	protected $tabs = array();

	/**
	 * Settings fields.
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Settings construct.
	 *
	 * @param string $id         Page id.
	 * @param string $title      Page title.
	 * @param string $capability User capability.
	 */
	public function __construct( $id, $title, $capability = 'manage_options' ) {
		$this->id         = $id;
		$this->title      = $title;
		$this->capability = $capability;

		// Actions.
		add_action( 'admin_menu', array( &$this, 'add_page' ) );
		add_action( 'admin_init', array( &$this, 'create_settings' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'scripts' ) );
	}

	/**
	 * Add Settings Theme page.
	 *
	 * @return void
	 */
	public function add_page() {
		add_submenu_page(
			'options-general.php',
			$this->title,
			$this->title,
			$this->capability,
			$this->id,
			array( &$this, 'settings_page' )
		);
	}

	/**
	 * Load options scripts.
	 *
	 * @return void
	 */
	function scripts() {
		// Checks if is the settings page.
		if ( isset( $_GET['page'] ) && $this->id == $_GET['page'] ) {			

			// Theme Options.
			wp_enqueue_style( 'mtral-admin', plugins_url('../assets/css/admin.css', __FILE__), array(), null, 'all' );
			//wp_enqueue_script( 'mtral-admin', plugins_url('../assets/js/admin.js', __FILE__), array( 'jquery' ), null, true );
			
		}
	}

	/**
	 * Set settings tabs.
	 *
	 * @param array $tabs Settings tabs.
	 */
	public function set_tabs( $tabs ) {
		$this->tabs = $tabs;
	}

	/**
	 * Set settings fields
	 *
	 * @param array $fields Settings fields.
	 */
	public function set_fields( $fields ) {
		$this->fields = $fields;
	}

	/**
	 * Get current tab.
	 *
	 * @return string Current tab ID.
	 */
	protected function get_current_tab() {
		if ( isset( $_GET['tab'] ) ) {
			$current_tab = $_GET['tab'];
		} else {
			$current_tab = $this->tabs[0]['id'];
		}

		return $current_tab;
	}

	/**
	 * Get the menu current URL.
	 *
	 * @return string Current URL.
	 */
	private function get_current_url() {
		$url = 'http';
		if ( isset( $_SERVER['HTTPS'] ) && 'on' == $_SERVER['HTTPS'] ) {
			$url .= 's';
		}

		$url .= '://';

		if ( '80' != $_SERVER['SERVER_PORT'] ) {
			$url .= $_SERVER['SERVER_NAME'] . ' : ' . $_SERVER['SERVER_PORT'] . $_SERVER['PHP_SELF'];
		} else {
			$url .= $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
		}

		return esc_url( $url );
	}

	/**
	 * Get tab navigation.
	 *
	 * @param  string $current_tab Current tab ID.
	 *
	 * @return string              Tab Navigation.
	 */
	protected function get_navigation( $current_tab ) {

		$html = '<h2 class="nav-tab-wrapper">';

		foreach ( $this->tabs as $tab ) {

			$current = ( $current_tab == $tab['id'] ) ? ' nav-tab-active' : '';

			$html .= sprintf( '<a href="%s?page=%s&amp;tab=%s" class="nav-tab%s">%s</a>', $this->get_current_url(), $this->id, $tab['id'], $current, $tab['title'] );
		}

		$html .= '</h2>';

		echo $html;
	}

	/**
	 * Built settings page.
	 *
	 * @return void
	 */
	public function settings_page() {
		// Get current tag.
		$current_tab = $this->get_current_tab();

		// Opens the wrap.
		echo '<div class="wrap">';

			// Display the navigation menu.
			$this->get_navigation( $current_tab );

			// Display erros.
			//settings_errors();

			// Creates the option form.
			echo '<form method="post" action="options.php">';
				foreach ( $this->tabs as $tabs ) {
					if ( $current_tab == $tabs['id'] ) {

						// Prints nonce, action and options_page fields.
						settings_fields( $tabs['id'] );

						// Prints settings sections and settings fields.
						do_settings_sections( $tabs['id'] );

						break;
					}
				}

				// Display submit button.
				submit_button();

			// Closes the form.
			echo '</form>';

		// Closes the wrap.
		echo '</div>';
	}

	/**
	 * Create settings.
	 *
	 * @return void
	 */
	public function create_settings() {

		// Register settings fields.
		foreach ( $this->fields as $section => $items ) {

			// Register settings sections.
			add_settings_section(
				$section,
				$items['title'],
				'__return_false',
				$items['tab']
			);

			foreach ( $items['fields'] as $option ) {

				$type = isset( $option['type'] ) ? $option['type'] : 'text';

				$args = array(
					'id'          => $option['id'],
					'tab'         => $items['tab'],
					'section'     => $section,
					'options'     => isset( $option['options'] ) ? $option['options'] : '',
					'default'     => isset( $option['default'] ) ? $option['default'] : '',
					'attributes'  => isset( $option['attributes'] ) ? $option['attributes'] : array(),
					'description' => isset( $option['description'] ) ? $option['description'] : ''
				);

				add_settings_field(
					$option['id'],
					$option['label'],
					array( &$this, 'callback_' . $type ),
					$items['tab'],
					$section,
					$args
				);
			}
		}

		// Register settings.
		foreach ( $this->tabs as $tabs ) {
			register_setting( $tabs['id'], $tabs['id'], array( &$this, 'validate_input' ) );
		}
	}

	/**
	 * Get Option.
	 *
	 * @param  string $tab     Tab that the option belongs
	 * @param  string $id      Option ID.
	 * @param  string $default Default option.
	 *
	 * @return array           Item options.
	 */
	protected function get_option( $tab, $id, $default = '' ) {
		$options = get_option( $tab );

		if ( isset( $options[ $id ] ) ) {
			$default = $options[ $id ];
		}

		return $default;

	}

	/**
	 * Build field attributes.
	 *
	 * @param  array $attrs Attributes as array.
	 *
	 * @return string       Attributes as string.
	 */
	protected function build_field_attributes( $attrs ) {
		$attributes = '';

		if ( ! empty( $attrs ) ) {
			foreach ( $attrs as $key => $attr ) {
				$attributes .= ' ' . $key . '="' . $attr . '"';
			}
		}

		return $attributes;
	}
	
	/**
	 * Redirect after login fields callback.
	 *
	 * @param array $args Arguments from the option.
	 *
	 * @return array fields HTML.
	 */
	public function callback_mtral( $args ) {
	
		global $wp_roles, $menu, $submenu;
	
		$tab   = $args['tab'];
		$id    = $args['id'];
		$attrs = $args['attributes'];
		
		$roles = $wp_roles->roles;
		$html = null;
		
		$html .= '<fieldset class="mtral '.get_locale().'">';		
		foreach($roles as $role_slug => $role_options){
		
			//Get saved options
			$setting = get_option('mtral_settings');
			$admin_pages = $setting[$id.'_'.$role_slug];
			$admin_custom_pages = $setting[$id.'_custom_url_'.$role_slug];
					
			$html .= '<div class="block">';
			$html .= sprintf( '<h4>%s</h4>', translate_user_role($role_options['name']) );
			$html .= sprintf( '<label for="%1$s_%2$s"><span>%3$s</span>', $id, $role_slug, __('Choose the page', 'mtral') );
			$html .= sprintf( '<select id="%1$s_%4$s" name="%2$s[%1$s_%4$s]"%3$s>', $id, $tab, $this->build_field_attributes( $attrs ), $role_slug );
			
			//List menu items
			foreach( $menu as $menu_number => $menu_data ) {
				
				$page_name = $menu_data[0];
				$page_cap = $menu_data[1];
				$page_link = $menu_data[2];
				
				
					if (!(in_array("wp-menu-separator", $menu_data))){
						$menu_selected = ($admin_pages == $page_link) ? 'selected' : '';
						
						if (array_key_exists($page_cap, $role_options['capabilities'])){
							$html .= sprintf( '<option value="%1$s"%2$s>%3$s</option>', $page_link, $menu_selected, $page_name );
						}
						
						$i = 0;
						//List submenu items
						foreach( $submenu as $submenu_parent => $submenu_data ) {						
							if(!empty($submenu[$page_link]) && $i == 0){
								foreach($submenu[$page_link] as $submenu_number => $submenu_data ) {
								
								$subpage_name = $submenu_data[0];
								$subpage_cap = $submenu_data[1];
								$subpage_link = $submenu_data[2];
								
								$submenu_selected = ($admin_pages == $subpage_link) ? 'selected' : '';
									if (array_key_exists($subpage_cap, $role_options['capabilities'])){
										$html .= sprintf( '<option value="%s"%s>- %s</option>', $subpage_link, $submenu_selected, $page_name.' &raquo; '.$subpage_name );
									}
								}
							}
						$i++;
						}						
					}
			}
			$html .= '</select></label>';			
			$html .= sprintf( '<label class="custom" for="%1$s_custom_url_%3$s"><span>%2$s</span>', $id, __('Or type a custom URL', 'mtral'), $role_slug );
			$html .= sprintf( '<input type="text" id="%1$s_custom_url_%4$s" name="%2$s[%1$s_custom_url_%4$s]"%3$s value="%5$s" /></label>', $id, $tab, $this->build_field_attributes( $attrs ), $role_slug, $admin_custom_pages );
			$html .= '</div>';		
		}
		$html .= '</fieldset>';
		
		// Displays the description.
		if ( $args['description'] ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}
		
		echo $html;
	}

	/**
	 * Sanitization fields callback.
	 *
	 * @param  string $input The unsanitized collection of options.
	 *
	 * @return string        The collection of sanitized values.
	 */
	public function validate_input( $input ) {

		// Create our array for storing the validated options.
		$output = array();

		// Loop through each of the incoming options.
		foreach ( $input as $key => $value ) {

			// Check to see if the current option has a value. If so, process it.
			if ( isset( $input[ $key ] ) ) {
				$output[ $key ] = apply_filters( 'lr_theme_options_validate_' . $this->id, $value, $key );
			}

		}

		return $output;
	}
}