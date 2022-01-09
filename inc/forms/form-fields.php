<?php
/**
 * Form-fields.php
 *
 * @package Recipes WordPress Theme
 */

/**
 * Builds forms for the front-end recipe submission. Displays fields which are enabled at Theme Options.
 *
 * @param  string $type Type of the form.
 */
function mytheme_build_form( $type ) {

	$optional_fields = mytheme_get_option( 'displayed_form_fields', array() );

	if ( 'submit_form_recipe' === $type ) {
		$primary_fields = array( 'recipe_title', 'description' );
	} elseif ( 'submit_form_url' === $type ) {
		$primary_fields = array( 'recipe_title', 'description', 'custom_meta_external_url', 'custom_meta_external_site' );
	}

	$edit_recipe_id = get_query_var( 'rcps_edit_recipe_id' );

	if ( ! empty( $edit_recipe_id ) ) {
		// Get already published recipe for editing.
		$sent_data   = mytheme_transform_post_to_form( $edit_recipe_id );
		$form_errors = get_transient( 'rcps_errors_edit_recipe_' . $edit_recipe_id );
	} else {
		// Get previously sent data from a transient.
		$sent_data   = get_transient( 'rcps_form_' . mytheme_get_user_key() );
		$form_errors = get_transient( 'rcps_errors_' . mytheme_get_user_key() );
	}

	$fields = array_merge( $primary_fields, $optional_fields );

	if ( ! empty( $fields ) ) {
		$form_fields_data = rcps_get_form_fields_data();

		foreach ( $fields as $field_name ) {
			$has_error = false;
			if ( ! empty( $form_errors ) && array_key_exists( $field_name, $form_errors ) ) {
				$has_error = true;
			}

			$field_values = ( ! empty( $form_fields_data[ $field_name ] ) ? $form_fields_data[ $field_name ] : array() );

			mytheme_build_form_field( $field_name, $field_values, $sent_data, $has_error );
		}
	}
}

/**
 * Outputs form fields for the front-end recipe submission.
 *
 * @param  string  $field_name       Form field name.
 * @param  array   $field_values     Form field values.
 * @param  array   $sent_data        Submitted form data.
 * @param  boolean $has_error        Has error.
 */
function mytheme_build_form_field( $field_name, $field_values = array(), $sent_data, $has_error = false ) {

	if ( empty( $sent_data['description'] ) ) {
		$description  = esc_html__( 'Write a short description and instructions here. Use the Numbered list button above to format the instructions as a list.', 'recipes' ) . "\n\n";
		$description .= '<h2>' . esc_html__( 'Directions', 'recipes' ) . '</h2>' . "\n\n";
		$description .= '<ol><li>' . esc_html__( 'Preheat oven to 350 degrees.', 'recipes' ) . '</li><li>' . esc_html__( 'Sift together flour, and salt.', 'recipes' ) . '</li><li>' . esc_html__( 'Bake until golden around edges.', 'recipes' ) . '</li></ol>';

		$sent_data['description'] = wp_kses_post( $description );
	}

	$wp_editor_settings = array(
		'media_buttons' => false,
		'textarea_rows' => 14,
		'quicktags'     => false,
	);

	// Get names of taxonomies, so we can check if the field is for a taxonomy.
	$taxonomies_names = get_object_taxonomies( 'recipe', 'names' );
	?>

	<?php if ( 'image' === $field_name ) : ?>
		<fieldset class="rcps-fieldset">
			<?php echo ( $has_error ) ? '<div class="rcps-form-error"></div>' : ''; ?>
			<label class="rcps-label"><?php esc_html_e( 'Image', 'recipes' ); ?></label>
			<input class="rcps-text-input rcps-wide" type="file" name="image" accept="image/*">

			<?php if ( is_numeric( RCPS_MIN_IMAGE_WIDTH ) && is_numeric( RCPS_MIN_IMAGE_HEIGHT ) ) : ?>
				<?php // Translators: %1$s is the image width in pixels. %2$s is the image height in pixels. ?>
				<p class="rcps-form-description"><?php printf( esc_html__( 'Minimum size: %1$s by %2$s pixels.', 'recipes' ), absint( RCPS_MIN_IMAGE_WIDTH ), absint( RCPS_MIN_IMAGE_HEIGHT ) ); ?></p>
			<?php endif; ?>

			<?php if ( is_numeric( RCPS_MAX_IMAGE_FILESIZE ) ) : ?>
				<?php // Translators: %s is file size in megabytes. ?>
				<p class="rcps-form-description"><?php printf( esc_html__( 'Max file size: %s megabytes', 'recipes' ), absint( RCPS_MAX_IMAGE_FILESIZE ) ); ?></p>
			<?php endif; ?>
		</fieldset>

		<?php if ( ! empty( $sent_data['attachment_id'] ) && ! empty( wp_get_attachment_image( $sent_data['attachment_id'] ) ) ) : ?>
			<?php $attachment = wp_get_attachment_metadata( $sent_data['attachment_id'] ); ?>
			<fieldset class="rcps-fieldset">
				<label class="rcps-label"><?php esc_html_e( 'Current Image', 'recipes' ); ?></label>
				<?php echo wp_get_attachment_image( $sent_data['attachment_id'], 'img-280' ); ?>
				<p class="rcps-form-description"><?php echo esc_html( basename( $attachment['file'] ) ); ?></p>
				<p class="rcps-form-description"><?php esc_html_e( 'Current image is replaced if you upload a new image.', 'recipes' ); ?></p>
				<input type="hidden" name="attachment_id" value="<?php echo absint( $sent_data['attachment_id'] ); ?>">
			</fieldset>
		<?php endif; ?>

	<?php elseif ( 'description' === $field_name ) : ?>
		<fieldset class="rcps-fieldset">
			<?php echo ( $has_error ) ? '<div class="rcps-form-error"></div>' : ''; ?>
			<label class="rcps-label rcps-label-wide"><?php esc_html_e( 'Description and Directions', 'recipes' ); ?></label>
			<?php wp_editor( $sent_data['description'], sanitize_html_class( 'description_' . microtime( true ) ), $wp_editor_settings ); ?>
		</fieldset>

	<?php elseif ( in_array( $field_name, $taxonomies_names, true ) && 'recipe-tag' !== $field_name ) : ?>
		<?php
		$taxonomy = get_taxonomy( $field_name );

		$terms = get_terms( array(
			'taxonomy'   => $taxonomy->name,
			'hide_empty' => false,
			'orderby'    => 'name',
		) );
		?>

		<?php if ( ! empty( $terms ) ) : ?>
			<fieldset class="rcps-fieldset">
				<?php echo ( $has_error ) ? '<div class="rcps-form-error"></div>' : ''; ?>
				<h3 class="rcps-label"><?php echo esc_html( $taxonomy->labels->singular_name ); ?></h3>

				<div class="rcps-form-right">
					<?php foreach ( $terms as $term ) : ?>
						<?php
						$is_checked = false;
						if ( ! empty( $sent_data['tax'][ $taxonomy->name ] ) ) {
							if ( in_array( $term->slug, $sent_data['tax'][ $taxonomy->name ], true ) ) {
								$is_checked = true;
							}
						}
						?>
						<div class="rcps-submit-form-checkbox">
							<input type="checkbox" name="tax[<?php echo esc_attr( $taxonomy->name ); ?>][]" id="id-<?php echo esc_attr( $term->slug ); ?>" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( $is_checked, true, true ); ?>>
							<label for="id-<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_attr( $term->name ); ?></label>
						</div>
					<?php endforeach; ?>
				</div>
			</fieldset>
		<?php endif; ?>

	<?php elseif ( 'ingredient_lists' === $field_name ) : ?>
		<?php for ( $y = 1; $y <= 5; $y++ ) : ?>

			<?php
			if ( ! empty( $sent_data['ingredient_lists'][ $y ]['ingredients'] ) && count( $sent_data['ingredient_lists'][ $y ]['ingredients'] ) > 3 ) {
				$ingredient_rows = count( $sent_data['ingredient_lists'][ $y ]['ingredients'] );
			} else {
				$ingredient_rows = 3;
			}
			?>

			<div id="rcps-ingredient-list-wrapper-<?php echo absint( $y ); ?>" <?php echo ( $y > 1 && empty( $sent_data['ingredient_lists'][ $y ]['ingredients'] ) ? 'class="rcps-hidden"' : '' ); ?>>

				<fieldset class="rcps-fieldset rcps-fieldset-combined">
					<label class="rcps-label"><?php esc_html_e( 'Ingredients Title', 'recipes' ); ?></label>
					<input class="rcps-text-input rcps-wide" type="text" value="<?php echo ( ! empty( $sent_data['ingredient_lists'][ $y ]['ingredients_title'] ) ? esc_attr( $sent_data['ingredient_lists'][ $y ]['ingredients_title'] ) : '' ); ?>" name="ingredient_lists[<?php echo absint( $y ); ?>][ingredients_title]">
				</fieldset>

				<fieldset class="rcps-fieldset">
					<label class="rcps-label"><?php esc_html_e( 'Ingredients', 'recipes' ); ?></label>

					<table class="rcps-form-table rcps-form-right">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Ingredient', 'recipes' ); ?></th>
								<th><?php esc_html_e( 'Amount', 'recipes' ); ?></th>
							</tr>
						</thead>

						<tbody>
							<?php for ( $x = 0; $x <= $ingredient_rows - 1; $x++ ) : ?>
								<tr class="rcps-tr-ingredient">
									<td class="rcps-td-ingredient">
										<input class="rcps-text-input" type="text" value="<?php echo ( ! empty( $sent_data['ingredient_lists'][ $y ]['ingredients'][ $x ]['ingredient'] ) ? esc_attr( $sent_data['ingredient_lists'][ $y ]['ingredients'][ $x ]['ingredient'] ) : '' ); ?>" name="ingredient_lists[<?php echo absint( $y ); ?>][ingredients][<?php echo absint( $x ); ?>][ingredient]">
									</td>

									<td class="rcps-td-amount">
										<input class="rcps-text-input" type="text" value="<?php echo ( ! empty( $sent_data['ingredient_lists'][ $y ]['ingredients'][ $x ]['amount'] ) ? esc_attr( $sent_data['ingredient_lists'][ $y ]['ingredients'][ $x ]['amount'] ) : '' ); ?>" name="ingredient_lists[<?php echo absint( $y ); ?>][ingredients][<?php echo absint( $x ); ?>][amount]">
									</td>
								</tr>
							<?php endfor; ?>
						</tbody>
					</table>

					<p class="rcps-form-description"><a href="#" class="rcps-btn" id="rcps-add-field-<?php echo absint( $y ); ?>" data-js-action="rcps-add-field"><?php esc_html_e( 'Add field', 'recipes' ); ?></a>

					<?php if ( $y < 5 ) : ?>
						<a href="#" class="rcps-btn" data-js-action="rcps-add-ingredient-list"><?php esc_html_e( 'Add ingredient list', 'recipes' ); ?></a></p>
					<?php endif; ?>
				</fieldset>
			</div>
		<?php endfor; ?>
	<?php else : ?>
		<?php
		// If the field is not found, use the fallback function to build the HTML for the field.
		if ( ! empty( $field_values ) ) {
			mytheme_build_form_field_fallback( $field_name, $field_values, $sent_data, $has_error );
		}
		?>
	<?php endif; ?>
	<?php
}

/**
 * Builds form field HTML if the function 'mytheme_build_form_field' did not have output for the field.
 *
 * @param  string  $field_name   Form field name.
 * @param  string  $field_values Form field values.
 * @param  array   $sent_data    Form data.
 * @param  boolean $has_error    Has error.
 */
function mytheme_build_form_field_fallback( $field_name, $field_values, $sent_data, $has_error = false ) {

	$attr = array(
		'class' => '',
	);

	// Used for all field types.
	if ( ! empty( $field_values['rcps_form_label'] ) ) {
		$field_values['name'] = $field_values['rcps_form_label'];
	}

	if ( ! empty( $field_values['attributes'] ) ) {
		foreach ( $field_values['attributes'] as $key => $value ) {
			$attr[ $key ] = $value;
		}
	}

	if ( ! empty( $field_values['rcps_form_extra_class'] ) ) {
		$attr['class'] = $attr['class'] . ' ' . $field_values['rcps_form_extra_class'];
	}

	$form_value = ( ! empty( $sent_data[ $field_name ] ) ? $sent_data[ $field_name ] : '' );
	$form_name  = $field_name;

	// Meta field.
	if ( ! empty( $field_values['id'] ) ) {
		$form_value = ( ! empty( $sent_data['meta'][ $field_values['id'] ] ) ? $sent_data['meta'][ $field_values['id'] ] : '' );
		$form_name  = 'meta[' . $field_values['id'] . ']';
	}

	// Recipe-tag field.
	if ( 'recipe-tag' === $field_name ) {
		$form_value = ( ! empty( $sent_data['tax']['recipe-tag'] ) ? $sent_data['tax']['recipe-tag'] : '' );
		$form_name  = 'tax[recipe-tag]';
	}

	if ( 'text' === $field_values['type'] || 'text_small' === $field_values['type'] || 'text_medium' === $field_values['type'] || 'textarea_small' === $field_values['type'] || 'number' === $field_values['type'] || 'oembed' === $field_values['type'] ) {
		$attr['type']  = 'text';
		$attr['class'] = 'rcps-text-input ' . $attr['class'];

		if ( 'number' === $field_values['type'] || ( ! empty( $field_values['attributes']['type'] ) && 'number' === $field_values['attributes']['type'] ) ) {
			$attr['type']  = 'number';
			$attr['class'] = $attr['class'] . ' rcps-narrow';
		}
		?>

		<fieldset class="rcps-fieldset">
			<?php echo ( $has_error ) ? '<div class="rcps-form-error"></div>' : ''; ?>

			<label class="rcps-label"><?php echo esc_html( $field_values['name'] ); ?></label>
			<input
			<?php
			foreach ( $attr as $key => $value ) {
				echo ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
			}
			?>
			value="<?php echo esc_attr( $form_value ); ?>" name="<?php echo esc_attr( $form_name ); ?>">

			<?php if ( ! empty( $field_values['desc'] ) ) : ?>
				<p class="rcps-form-description"><?php echo esc_html( $field_values['desc'] ); ?></p>
			<?php endif; ?>
		</fieldset>
		<?php
	}

	if ( 'textarea' === $field_values['type'] ) {
		$attr['class'] = 'rcps-textarea ' . $attr['class'];
		?>

		<fieldset class="rcps-fieldset">
			<?php echo ( $has_error ) ? '<div class="rcps-form-error"></div>' : ''; ?>

			<label class="rcps-label"><?php echo esc_html( $field_values['name'] ); ?></label>
			<textarea name="<?php echo esc_attr( $form_name ); ?>"
				<?php
				foreach ( $attr as $key => $value ) {
					echo ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
				}
				?>
				><?php echo esc_attr( $form_value ); ?></textarea>

			<?php if ( ! empty( $field_values['desc'] ) ) : ?>
				<p class="rcps-form-description"><?php echo esc_html( $field_values['desc'] ); ?></p>
			<?php endif; ?>
		</fieldset>
		<?php
	}
}

/**
 * Builds an array of form fields.
 *
 * @return array
 */
function rcps_get_form_fields_data() {

	$return = array(
		'recipe_title' => array(
			'name'                  => esc_html__( 'Recipe Title', 'recipes' ),
			'rcps_form_extra_class' => 'rcps-wide',
			'type'                  => 'text',
		),
		'recipe-tag'   => array(
			'name'                  => esc_html__( 'Tags', 'recipes' ),
			'rcps_form_extra_class' => 'rcps-wide',
			'type'                  => 'text',
			'desc'                  => esc_html__( 'Separate tags with commas. For example: healthy, paleo, gluten-free', 'recipes' ),
		),
	);

	if ( class_exists( '\CMB2_Boxes' ) ) {
		$meta_boxes = \CMB2_Boxes::get_all();

		if ( ! empty( $meta_boxes ) ) {
			foreach ( $meta_boxes as $meta_box ) {
				foreach ( $meta_box->meta_box['fields'] as $meta_key => $values ) {
					if ( ! empty( $values['rcps_form_display'] ) && true === $values['rcps_form_display'] ) {
						$return[ $meta_key ] = $values;
					}
				}
			}
		}
	}

	return $return;
}

/**
 * Builds error messages for the form fields.
 *
 * @return array
 */
function rcps_get_form_error_messages() {

	$return = array();

	// Define error messages for the default form fields.
	$fields = array(
		'recipe_title' => __( 'Recipe Title', 'recipes' ),
		'description'  => __( 'Description and Directions', 'recipes' ),
		'ingredients'  => __( 'Ingredients', 'recipes' ),
	);

	// Add meta fields.
	$form_fields_data = rcps_get_form_fields_data();

	if ( ! empty( $form_fields_data ) ) {
		foreach ( $form_fields_data as $key => $values ) {
			// Use the form label if set, otherwise use the field name.
			$label = ( ! empty( $values['rcps_form_label'] ) ? $values['rcps_form_label'] : $values['name'] );

			$fields[ $key ] = $label;
		}
	}

	// Add taxonomies.
	$taxonomies = get_object_taxonomies( 'recipe', 'objects' );

	if ( ! empty( $taxonomies ) ) {
		foreach ( $taxonomies as $tax => $values ) {
			$fields[ $tax ] = $values->labels->singular_name;
		}
	}

	// Build an array of error messages.
	foreach ( $fields as $field => $field_label ) {
		// Translators: %s is the field label.
		$return[ $field ] = sprintf( esc_html_x( 'Please check field: %s', 'Form error message. %s is the field label', 'recipes' ), $field_label );
	}

	$return = apply_filters( 'rcps_filter_form_error_messages', $return );

	return $return;
}
