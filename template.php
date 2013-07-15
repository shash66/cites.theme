<?php
/**
 * Adds body classes if certain regions have content.
 */
function cites_theme_preprocess_html( &$variables ) {
	if ( ! empty( $variables['page']['featured'] ) ) {
		$variables['classes_array'][] = 'featured';
	}

	// Adds conditional stylesheets for IE.
	drupal_add_css(
		path_to_theme() . '/css/ie.css',
		array(
			'group'    => CSS_THEME,
			'browsers' => array(
				'IE'  => 'lte IE 7',
				'!IE' => FALSE
			),
			'preprocess' => FALSE
		)
	);

	// Adds conditional stylesheets for IE.
	drupal_add_css(
		path_to_theme() . '/css/ie6.css',
		array(
			'group'    => CSS_THEME,
			'browsers' => array(
				'IE'  => 'IE 6',
				'!IE' => FALSE
			),
			'preprocess' => FALSE
		)
	);

	// Adds the Nice Menu required files if the module is enabled.
	if ( module_exists( 'nice_menus' ) ) {
		// Adds the Superfish JS library if enabled.
		if ( variable_get( 'nice_menus_js', 1 ) == 1 )
			drupal_add_library( 'nice_menus', 'nice_menus' );

		// Adds the main CSS functionality.
		drupal_add_css(
			drupal_get_path( 'module', 'nice_menus' ) . '/css/nice_menus.css',
			array(
				'basename' => 'nice_menus.css',
				'group'    => CSS_DEFAULT
			)
		);

		// Adds the default CSS layout.
		drupal_add_css(
			drupal_get_path( 'module', 'nice_menus' ) . '/css/nice_menus_default.css',
			array(
				'basename' => '/css/nice_menus_default.css',
				'group'    => CSS_DEFAULT
			)
		);
	}
}

/**
 * Performs alterations before a page is rendered.
 */
function cites_theme_page_alter( &$page ) {
	global $theme;

	$item = menu_get_item();

	if ( ! drupal_is_front_page() && $item['path'] != 'admin/structure/block/demo/' . $theme ) {
		// Removes the second sidebar region from other pages than front page
		// and block regions demonstration.
		unset( $page['sidebar_second'] );
	} else if ( empty( $page['sidebar_second'] ) ) {
		// Forces the second sidebar region to render on the front page even if
		// it's empty.
		$page['sidebar_second'] = array( '' );
	}
}

/**
 * Overrides or inserts variables into the page template.
 */
function cites_theme_process_page( &$variables ) {
	// Visually hides the site name and slogan if they are toggled off.
	$variables['hide_site_name']   = theme_get_setting( 'toggle_name' ) ? FALSE : TRUE;
	$variables['hide_site_slogan'] = theme_get_setting( 'toggle_slogan' ) ? FALSE : TRUE;

	if ( $variables['hide_site_name'] ) {
		// Rebuilds the site_name if toggle_name is FALSE.
		$variables['site_name'] = filter_xss_admin( variable_get( 'site_name', 'Drupal' ) );
	}

	if ( $variables['hide_site_slogan']) {
		// Rebuilds the site_slogan if toggle_site_slogan is FALSE.
		$variables['site_slogan'] = filter_xss_admin( variable_get( 'site_slogan', '' ) );
	}

	// Adds a wrapper div for positioning the title and the shortcut link next
	// to each other.
	if ( ! empty( $variables['title_suffix']['add_or_remove_shortcut'] ) && $variables['title'] ) {
		$variables['title_prefix']['shortcut_wrapper'] = array(
			'#markup' => '<div class="shortcut-wrapper clearfix">',
			'#weight' => 100,
		);

		$variables['title_suffix']['shortcut_wrapper'] = array(
			'#markup' => '</div>',
			'#weight' => -99,
		);

		// Forces the shortcut link to be the first item in title_suffix.
		$variables['title_suffix']['add_or_remove_shortcut']['#weight'] = -100;
	}
}

/**
 * Implements hook_preprocess_maintenance_page().
 *
 * @see template_preprocess_maintenance_page
 */
function cites_theme_preprocess_maintenance_page( &$variables ) {
	// Sets the site_name to an empty string if no database connection is
	// available or during site installation.
	if ( ! $variables['db_is_active'] )
		$variables['site_name'] = '';

	// Adds the maintenance CSS layout.
	drupal_add_css( drupal_get_path( 'theme', 'cites_theme' ) . '/css/maintenance-page.css' );
}

/**
 * Override or insert variables into the maintenance page template.
 */
function cites_theme_process_maintenance_page( &$variables ) {
	// Always print the site name and slogan, but if they are toggled off, we'll
	// just hide them visually.
	$variables['hide_site_name']   = theme_get_setting( 'toggle_name' ) ? FALSE : TRUE;
	$variables['hide_site_slogan'] = theme_get_setting( 'toggle_slogan' ) ? FALSE : TRUE;

	if ( $variables['hide_site_name'] ) {
		// If toggle_name is FALSE, the site_name will be empty, so we rebuild it.
		$variables['site_name'] = filter_xss_admin(variable_get( 'site_name', 'Drupal' ) );
	}

	if ( $variables['hide_site_slogan'] ) {
		// If toggle_site_slogan is FALSE, the site_slogan will be empty, so we rebuild it.
		$variables['site_slogan'] = filter_xss_admin( variable_get( 'site_slogan', '' ) );
	}
}

/**
 * Overrides or inserts variables into the node template.
 */
function cites_theme_preprocess_node( &$variables ) {
	if ( $variables['view_mode'] == 'full' && node_is_page( $variables['node'] ) )
		$variables['classes_array'][] = 'node-full';
}

/**
 * Overrides or inserts variables into the block template.
 */
function cites_theme_preprocess_block( &$variables ) {
	// Visually hides block titles in the header and footer regions.
	if ( in_array( $variables['block']->region, array( 'header', 'footer' ) ) )
		$variables['title_attributes_array']['class'][] = 'element-invisible';
}

/**
 * Implements theme_menu_tree().
 */
function cites_theme_menu_tree( $variables ) {
	return '<ul class="menu clearfix">' . $variables['tree'] . '</ul>';
}

/**
 * Implements theme_field__field_type().
 */
function cites_theme_field__taxonomy_term_reference( $variables ) {
	$output = '';

	// Renders the label, if it's not hidden.
	if ( ! $variables['label_hidden'] )
		$output .= '<h3 class="field-label">' . $variables['label'] . ': </h3>';

	// Renders the items.
	$output .= ( $variables['element']['#label_display'] == 'inline' ) ? '<ul class="links inline">' : '<ul class="links">';

	foreach ( $variables['items'] as $delta => $item )
		$output .= '<li class="taxonomy-term-reference-' . $delta . '"' . $variables['item_attributes'][ $delta ] . '>' . drupal_render( $item ) . '</li>';

	$output .= '</ul>';

	// Renders the top-level DIV.
	$output = '<div class="' . $variables['classes'] . ( ! in_array( 'clearfix', $variables['classes_array'] ) ? ' clearfix' : '' ) . '"' . $variables['attributes'] .'>' . $output . '</div>';

	return $output;
}

/**
 * Performs alterations before a form is rendered.
 */
function cites_theme_form_alter( &$form, &$form_state, $form_id ) {
	// Alters the search block forms.
	if ( $form_id == 'search_block_form' ) {
		// Vissually hides the submit button.
		$form['actions']['#attributes']['class'][] = 'element-invisible';

		// Adds the placeholder text.
		$form['search_block_form']['#attributes']['placeholder'] = t( 'Search' );
	}
}

/**
 * Performs alterations before the language switcher is rendered.
 */
function cites_theme_language_switch_links_alter( &$links, $type, $path ) {
	global $language;

	// Removes the active language link.
	unset( $links[ $language->language ] );
}
