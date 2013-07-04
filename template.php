<?php
/**
 * Add body classes if certain regions have content.
 */
function cites_theme_preprocess_html( &$variables ) {
	if ( ! empty( $variables['page']['featured'] ) ) {
		$variables['classes_array'][] = 'featured';
	}

	// Add conditional stylesheets for IE
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
}

/**
 * Performs alterations before a page is rendered.
 */
function cites_theme_page_alter( &$page ) {
	if ( ! drupal_is_front_page() ) {
		// Remove the second sidebar region from other pages than front page.
		unset( $page['sidebar_second'] );
	} else if ( empty( $page['sidebar_second'] ) ) {
		// Force the second sidebar region to render on the front page even if
		// it's empty.
		$page['sidebar_second'] = array( '' );
	}
}

/**
 * Override or insert variables into the page template.
 */
function cites_theme_process_page( &$variables ) {
	// Always print the site name and slogan, but if they are toggled off, we'll
	// just hide them visually.
	$variables['hide_site_name']   = theme_get_setting( 'toggle_name' ) ? FALSE : TRUE;
	$variables['hide_site_slogan'] = theme_get_setting( 'toggle_slogan' ) ? FALSE : TRUE;

	if ( $variables['hide_site_name'] ) {
		// If toggle_name is FALSE, the site_name will be empty, so we rebuild
		// it.
		$variables['site_name'] = filter_xss_admin( variable_get( 'site_name', 'Drupal' ) );
	}

	if ( $variables['hide_site_slogan']) {
		// If toggle_site_slogan is FALSE, the site_slogan will be empty, so we
		// rebuild it.
		$variables['site_slogan'] = filter_xss_admin( variable_get( 'site_slogan', '' ) );
	}

	// Since the title and the shortcut link are both block level elements,
	// positioning them next to each other is much simpler with a wrapper div.
	if ( ! empty( $variables['title_suffix']['add_or_remove_shortcut'] ) && $variables['title'] ) {
		// Add a wrapper div using the title_prefix and title_suffix render
		// elements.
		$variables['title_prefix']['shortcut_wrapper'] = array(
			'#markup' => '<div class="shortcut-wrapper clearfix">',
			'#weight' => 100,
		);

		$variables['title_suffix']['shortcut_wrapper'] = array(
			'#markup' => '</div>',
			'#weight' => -99,
		);

		// Make sure the shortcut link is the first item in title_suffix.
		$variables['title_suffix']['add_or_remove_shortcut']['#weight'] = -100;
	}
}

/**
 * Implements hook_preprocess_maintenance_page().
 */
function cites_theme_preprocess_maintenance_page( &$variables ) {
	// By default, site_name is set to Drupal if no db connection is available
	// or during site installation. Setting site_name to an empty string makes
	// the site and update pages look cleaner.
	// @see template_preprocess_maintenance_page
	if ( ! $variables['db_is_active'] ) {
		$variables['site_name'] = '';
	}

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
 * Override or insert variables into the node template.
 */
function cites_theme_preprocess_node( &$variables ) {
	if ( $variables['view_mode'] == 'full' && node_is_page($variables['node'] ) ) {
		$variables['classes_array'][] = 'node-full';
	}
}

/**
 * Override or insert variables into the block template.
 */
function cites_theme_preprocess_block( &$variables ) {
	// In the header and footer regions visually hide block titles.
	if ( in_array( $variables['block']->region, array( 'header', 'footer' ) ) ) {
		$variables['title_attributes_array']['class'][] = 'element-invisible';
	}
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

	// Render the label, if it's not hidden.
	if ( ! $variables['label_hidden'] ) {
		$output .= '<h3 class="field-label">' . $variables['label'] . ': </h3>';
	}

	// Render the items.
	$output .= ( $variables['element']['#label_display'] == 'inline' ) ? '<ul class="links inline">' : '<ul class="links">';

	foreach ( $variables['items'] as $delta => $item ) {
		$output .= '<li class="taxonomy-term-reference-' . $delta . '"' . $variables['item_attributes'][ $delta ] . '>' . drupal_render( $item ) . '</li>';
	}

	$output .= '</ul>';

	// Render the top-level DIV.
	$output = '<div class="' . $variables['classes'] . ( ! in_array( 'clearfix', $variables['classes_array'] ) ? ' clearfix' : '' ) . '"' . $variables['attributes'] .'>' . $output . '</div>';

	return $output;
}

/**
 * Performs alterations before a form is rendered.
 */
function cites_theme_form_alter( &$form, &$form_state, $form_id ) {
	// Alter the search block forms.
	if ( $form_id == 'search_block_form' ) {
		// Vissually hide the submit button.
		$form['actions']['#attributes']['class'][] = 'element-invisible';

		// Add the placeholder text.
		$form['search_block_form']['#attributes']['placeholder'] = t( 'Search CITES.org' );
	}
}

/**
 * Performs alterations before the language switcher is rendered.
 */
function cites_theme_language_switch_links_alter( &$links, $type, $path ) {
	global $language;

	// Remove the active language link.
	unset( $links[ $language->language ] );
}
