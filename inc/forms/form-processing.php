<?php
/**
 * Form-processing.php.
 *
 * @package Recipes WordPress Theme
 */

/**
 * Creates a new post or saves the edited post if the form validates.
 */
function rcps_post_recipe_form() {

	// If editing a recipe.
	$edit_recipe_id = ( ! empty( filter_input( INPUT_POST, 'edit_recipe_id' ) ) ? filter_input( INPUT_POST, 'edit_recipe_id' ) : false );

	// Validate form.
	$validate_form = rcps_validate_form( $edit_recipe_id );

	$form        = $validate_form['form'];
	$form_errors = $validate_form['form_errors'];

	// If there are no errors, add the form input to $new_post array.
	if ( empty( $form_errors ) ) {

		$new_post = array(
			'post_type'    => 'recipe',
			'post_title'   => $form['recipe_title'],
			'post_content' => $form['description'],
		);

		if ( ! empty( $form['meta'] ) ) {
			$new_post['meta_input'] = $form['meta'];
		}

		if ( $edit_recipe_id ) {
			// Update the post.
			$new_post['ID']          = $edit_recipe_id;
			$new_post['post_status'] = 'publish';

			$new_post_id = wp_update_post( $new_post );
		} else {
			// Insert new post.
			$new_post['post_status'] = 'pending';

			$new_post_id = wp_insert_post( $new_post );
		}

		// Set the image as the thumbnail and attach it to the recipe.
		if ( ! empty( $form['attachment_id'] ) && is_numeric( $form['attachment_id'] ) ) {
			update_post_meta( $new_post_id, '_thumbnail_id', $form['attachment_id'] );
			set_post_thumbnail( $new_post_id, $form['attachment_id'] );
			wp_update_post( array(
				'ID'          => $form['attachment_id'],
				'post_parent' => $new_post_id,
			) );
		}

		// Add terms.
		// NOTE: Must use wp_set_object_terms because tax_input doesn't work for users without capability to work with taxonomies.
		if ( ! empty( $form['tax'] ) ) {
			foreach ( $form['tax'] as $taxonomy_name => $values ) {

				// Convert tags into array.
				if ( 'recipe-tag' === $taxonomy_name ) {
					$values = str_replace( ', ', ',', $values );
					$values = explode( ',', $values );
				}

				wp_set_object_terms( $new_post_id, $values, $taxonomy_name );
			}
		}

		if ( $edit_recipe_id ) {
			// If the post was updated successfully, delete transients for errors and form data.
			delete_transient( 'rcps_form_edit_' . $edit_recipe_id );
			delete_transient( 'rcps_errors_edit_recipe_' . $edit_recipe_id );
		} else {
			// Send email to administrator.
			$headers = 'From: <' . get_option( 'admin_email' ) . '>' . "\r\n";
			$message = __( 'Your site just had a new recipe submitted! View the recipe:', 'recipes' ) . ' ' . admin_url() . 'post.php?post=' . $new_post_id . '&action=edit';
			wp_mail( get_option( 'admin_email' ), __( 'New recipe', 'recipes' ) . ': ' . $form['recipe_title'], $message, $headers );

			// If the post was created successfully, delete transients for errors and form data.
			delete_transient( 'rcps_errors_' . mytheme_get_user_key() );
			delete_transient( 'rcps_form_' . mytheme_get_user_key() );
		}

		$original_url = wp_get_referer( remove_query_arg( 'success' ) );
		wp_safe_redirect( add_query_arg( 'success', 'true', $original_url ) );
	} elseif ( ! empty( $form_errors ) ) {

		if ( $edit_recipe_id ) {
			// Set transient for form data, so the form fields can be filled.
			set_transient( 'rcps_form_edit_' . $edit_recipe_id, $form, HOUR_IN_SECONDS );

			// Set transient for errors.
			set_transient( 'rcps_errors_edit_recipe_' . $edit_recipe_id, $form_errors, HOUR_IN_SECONDS );
		} else {
			// Set transient for form data, so the form fields can be filled.
			set_transient( 'rcps_form_' . mytheme_get_user_key(), $form, HOUR_IN_SECONDS );

			// Set transient for errors.
			set_transient( 'rcps_errors_' . mytheme_get_user_key(), $form_errors, HOUR_IN_SECONDS );
		}

		$original_url = wp_get_referer();
		wp_safe_redirect( remove_query_arg( 'success', $original_url ) );
	}

	exit;
}
add_action( 'admin_post_nopriv_rcps_submit_form', 'rcps_post_recipe_form' );
add_action( 'admin_post_rcps_submit_form', 'rcps_post_recipe_form' );

/**
 * Validates form.
 *
 * @param int|boolean $edit_recipe_id Recipe Post ID if editing a recipe.
 *
 * @return array
 */
function rcps_validate_form( $edit_recipe_id ) {

	// Verify security nonce.
	$nonce = filter_input( INPUT_POST, 'nonce_new_post' );
	if ( empty( $nonce ) || false === wp_verify_nonce( $nonce, 'new_post_action' ) ) {
		wp_die( esc_html__( 'Nonce did not validate.', 'recipes' ) );
	}

	$post = $_POST;

	$error_messages = rcps_get_form_error_messages();

	$required_form_fields = array( 'recipe_title', 'description' );

	// Add always required fields to the array of required fields.
	if ( ! empty( mytheme_get_option( 'required_form_fields' ) ) ) {
		$required_form_fields = array_merge( $required_form_fields, mytheme_get_option( 'required_form_fields' ) );
	}

	// Insert taxonomies even if empty. If there are no checked checkboxes, the taxonomy is not posted with the form.
	$taxonomies = get_object_taxonomies( 'recipe', 'objects' );
	foreach ( $taxonomies as $tax => $values ) {
		if ( empty( $post['tax'][ $tax ] ) ) {
			if ( 'recipe-tag' === $tax ) {
				$post['tax'][ $tax ] = '';
			} else {
				$post['tax'][ $tax ] = array();
			}
		}
	}

	$form_errors = array();

	$form = array(
		'tax'       => array(),
		'meta'      => array(),
		'form_type' => $post['form_type'],
	);

	if ( ! empty( $post['attachment_id'] ) ) {
		$form['attachment_id'] = $post['attachment_id'];
	}

	foreach ( $post as $field_name => $field_values ) {
		if ( in_array( $field_name, array( 'form_type', 'ingredient_lists', 'image', 'attachment_id' ), true ) ) {
			continue;
		}

		// Description.
		if ( strpos( $field_name, 'description_' ) !== false ) {
			$form['description'] = wp_kses_post( $field_values );

			if ( empty( $form['description'] ) ) {
				$form_errors['description'] = $error_messages['description'];
			}
		}

		if ( 'tax' !== $field_name && 'meta' !== $field_name ) {
			$form[ $field_name ] = '';

			if ( ! empty( $post[ $field_name ] ) ) {
				$form[ $field_name ] = wp_strip_all_tags( $post[ $field_name ] );
			}

			if ( empty( $post[ $field_name ] ) && in_array( $field_name, $required_form_fields, true ) ) {
				$form_errors[ $field_name ] = $error_messages[ $field_name ];
			}
		}

		// Validate meta.
		if ( 'meta' === $field_name ) {
			foreach ( $post['meta'] as $meta_key => $values ) {
				$form['meta'][ $meta_key ] = '';

				if ( ! empty( $post['meta'][ $meta_key ] ) ) {
					$form['meta'][ $meta_key ] = wp_strip_all_tags( $post['meta'][ $meta_key ] );
				}

				if ( empty( $post['meta'][ $meta_key ] ) && in_array( $meta_key, $required_form_fields, true ) ) {
					$form_errors[ $meta_key ] = $error_messages[ $meta_key ];
				}
			}
		}

		// Validate taxonomy.
		if ( 'tax' === $field_name ) {
			foreach ( $post['tax'] as $taxonomy => $term_slugs ) {
				if ( 'recipe-tag' !== $taxonomy ) {

					$valid_values = array();

					if ( ! empty( $term_slugs ) ) {
						$terms = get_terms(
							$taxonomy,
							array(
								'hide_empty' => false,
								'fields'     => 'id=>slug',
							)
						);

						foreach ( $term_slugs as $term_slug ) {
							if ( in_array( $term_slug, $terms, true ) ) {
								$valid_values[] = $term_slug;
							}
						}
					}

					if ( in_array( $taxonomy, $required_form_fields, true ) && empty( $valid_values ) ) {
						$form_errors[ $taxonomy ] = $error_messages[ $taxonomy ];
					}

					$form['tax'][ $taxonomy ] = $valid_values;

				} elseif ( 'recipe-tag' === $taxonomy ) {
					// Validate recipe tags.
					if ( in_array( 'recipe-tag', $required_form_fields, true ) && empty( $post['tax']['recipe-tag'] ) ) {
						$form_errors[ $taxonomy ] = $error_messages[ $taxonomy ];
					} else {
						$form['tax']['recipe-tag'] = wp_strip_all_tags( $post['tax']['recipe-tag'] );
					}
				}
			}
		}
	}

	if ( ! empty( $post['form_type'] ) && 'submit_form_url' === $post['form_type'] ) {
		if ( empty( $post['meta']['custom_meta_external_url'] ) || filter_var( $post['meta']['custom_meta_external_url'], FILTER_VALIDATE_URL ) === false ) {
			$form_errors['custom_meta_external_url']  = $error_messages['custom_meta_external_url'];
			$form['meta']['custom_meta_external_url'] = $post['meta']['custom_meta_external_url'];
		} elseif ( ! empty( $post['custom_meta_external_url'] ) ) {
			$form['meta']['custom_meta_external_url'] = $post['meta']['custom_meta_external_url'];
		}

		if ( ! empty( $post['meta']['custom_meta_external_site'] ) ) {
			$form['meta']['custom_meta_external_site'] = wp_strip_all_tags( $post['meta']['custom_meta_external_site'] );
		} else {
			$form_errors['custom_meta_external_site'] = $error_messages['custom_meta_external_site'];
		}
	}
	//Ingredients
	if ( ! empty( $post['form_type'] ) && 'submit_form_recipe' === $post['form_type'] ) {
		if ( ! empty( $post['ingredient_lists'] ) ) {
			foreach ( $post['ingredient_lists'] as $key => $ingredient_list ) {
				$list_number = ( 1 === $key ) ? '' : $key;

				$form['ingredient_lists'][ $key ] = array();

				if ( $edit_recipe_id ) {
					delete_post_meta( $edit_recipe_id, 'custom_meta_ingredient_group' . $list_number . '_title' );
					delete_post_meta( $edit_recipe_id, 'custom_meta_ingredient_group' . $list_number );
				}

				if ( ! empty( $ingredient_list['ingredients_title'] ) ) {
					$form['ingredient_lists'][ $key ]['ingredients_title'] = $ingredient_list['ingredients_title'];

					$form['meta'][ 'custom_meta_ingredient_group' . $list_number . '_title' ] = $ingredient_list['ingredients_title'];
				}

				foreach ( $ingredient_list['ingredients'] as $ingredient ) {
					if ( empty( $ingredient['ingredient'] ) && empty( $ingredient['amount'] ) ) {
						continue;
					}

					if ( empty( $ingredient['ingredient'] ) ) {
						$form_errors['ingredient'] = $error_messages['ingredients'];
					}

					$form['ingredient_lists'][ $key ]['ingredients'][] = $ingredient;
					$form['meta'][ 'custom_meta_ingredient_group' . $list_number ][] = $ingredient;
				}
			}
		}

		if ( in_array( 'ingredient_lists', $required_form_fields, true ) && empty( $form['ingredient_lists'][1]['ingredients'] ) && empty( $form['ingredient_lists'][2]['ingredients'] ) && empty( $form['ingredient_lists'][3]['ingredients'] ) && empty( $form['ingredient_lists'][4]['ingredients'] ) && empty( $form['ingredient_lists'][5]['ingredients'] ) ) {
			$form_errors['ingredient'] = $error_messages['ingredients'];
		}
	}

	//Mise it
	if ( ! empty( $post['form_type'] ) && 'submit_form_recipe' === $post['form_type'] ) {
		if ( ! empty( $post['miseit_lists'] ) ) {
			foreach ( $post['miseit_lists'] as $key => $miseit_list ) {
				$list_number = ( 1 === $key ) ? '' : $key;

				$form['miseit_lists'][ $key ] = array();

				if ( $edit_recipe_id ) {
					delete_post_meta( $edit_recipe_id, 'custom_meta_miseit_group' . $list_number . '_title' );
					delete_post_meta( $edit_recipe_id, 'custom_meta_miseit_group' . $list_number );
				}

				if ( ! empty( $miseit_list['miseit_title'] ) ) {
					$form['miseit_lists'][ $key ]['miseit_title'] = $miseit_list['miseit_title'];

					$form['meta'][ 'custom_meta_miseit_group' . $list_number . '_title' ] = $miseit_list['miseit_title'];
				}

				foreach ( $miseit_list['miseits'] as $items ) {
					if ( empty( $items['ingredient'] ) && empty( $items['instruction'] ) ) {
						continue;
					}

					if ( empty( $items['ingredient'] ) ) {
						$form_errors['miseit'] = $error_messages['miseit'];
					}

					$form['miseit_lists'][ $key ]['ingredient'][] = $items;
					$form['meta'][ 'custom_meta_miseit_group' . $list_number ][] = $items;
				}
			}
		}

	}
	
	

	// Image validation.
	if ( ! empty( $_FILES['image'] ) && $_FILES['image']['size'] > 0 ) {
		$file     = $_FILES['image'];
		$image_ok = true;

		$min_width  = RCPS_MIN_IMAGE_WIDTH;
		$min_height = RCPS_MIN_IMAGE_HEIGHT;

		list( $width, $height, $type, $attr ) = getimagesize( $file['tmp_name'] );

		if ( is_numeric( $min_width ) && is_numeric( $min_height ) && ( $width < $min_width || $height < $min_height ) ) {
			// Translators: %1$s is the image width in pixels. %2$s is the image height in pixels.
			$form_errors['image'] = sprintf( __( 'Image is too small (minimum size: %1$s by %2$s pixels)', 'recipes' ), $min_width, $min_height );

			$image_ok = false;
		}

		if ( is_numeric( RCPS_MAX_IMAGE_FILESIZE ) && $file['size'] > ( RCPS_MAX_IMAGE_FILESIZE * 1024 * 1024 ) ) {
			// Translators: %s is file size in megabytes.
			$form_errors['image'] = sprintf( __( 'Image file size is too big (max file size: %s megabytes)', 'recipes' ), absint( RCPS_MAX_IMAGE_FILESIZE ) );

			$image_ok = false;
		}

		$arr_file_type = wp_check_filetype( basename( $file['name'] ) );

		$uploaded_file_type = $arr_file_type['type'];

		if ( ! in_array( $uploaded_file_type, array( 'image/jpg', 'image/jpeg' ), true ) ) {
			$form_errors['image'] = __( 'Please upload a JPG file', 'recipes' );

			$image_ok = false;
		}

		// Insert media attachments.
		if ( true === $image_ok ) {
			foreach ( $_FILES as $file => $array ) {
				$attachment_id = mytheme_insert_attachment( $file, 0, false );
				if ( ! is_wp_error( $attachment_id ) ) {
					$form['attachment_id'] = $attachment_id;
				} elseif ( is_wp_error( $attachment_id ) ) {
					$form_errors['image'] = __( 'Image upload error! Please try again.', 'recipes' );
				}
			}
		}

		// Remove the tmp file when the file is uploaded or has errors.
		if ( ! empty( $_FILES['tmp_name'] ) ) {
			wp_delete_file( $_FILES['tmp_name'] );
		}
	}

	if ( empty( $form['attachment_id'] ) && in_array( 'image', $required_form_fields, true ) ) {
		$form_errors['image'] = __( 'Please add an image', 'recipes' );
	}

	return array(
		'form'        => $form,
		'form_errors' => $form_errors,
	);
}
