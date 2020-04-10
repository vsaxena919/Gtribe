<?php

namespace Rtcl\Models;

use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;

class RtclCFGField {

	protected $_type;
	protected $_label;
	protected $_slug;
	protected $_placeholder;
	protected $_description;
	protected $_message;
	protected $_options = array();
	protected $_required;
	protected $_searchable;
	protected $_listable;
	protected $_default_value;
	protected $_validation;
	protected $_validation_message;
	protected $_rows;
	protected $_min;
	protected $_max;
	protected $_step_size;
	protected $_target;
	protected $_nofollow;
	protected $_field_id;
	protected $_meta_key;

	public function __construct( $field_id = 0 ) {
		if ( $post = get_post( $field_id ) ) {
			$this->_field_id           = $post->ID;
			$this->_type               = get_post_meta( $post->ID, '_type', true );
			$this->_label              = get_post_meta( $post->ID, '_label', true );
			$this->_slug               = get_post_meta( $post->ID, '_slug', true );
			$this->_description        = get_post_meta( $post->ID, '_description', true );
			$this->_message            = get_post_meta( $post->ID, '_message', true );
			$this->_placeholder        = get_post_meta( $post->ID, '_placeholder', true );
			$this->_options            = get_post_meta( $post->ID, '_options', true );
			$this->_validation         = get_post_meta( $post->ID, '_validation', true );
			$this->_validation_message = get_post_meta( $post->ID, '_validation_message', true );
			$this->_required           = get_post_meta( $post->ID, '_required', true );
			$this->_searchable         = get_post_meta( $post->ID, '_searchable', true );
			$this->_listable           = get_post_meta( $post->ID, '_listable', true );
			$this->_default_value      = get_post_meta( $post->ID, '_default_value', true );
			$this->_rows               = get_post_meta( $post->ID, '_rows', true );
			$this->_min                = get_post_meta( $post->ID, '_min', true );
			$this->_max                = get_post_meta( $post->ID, '_max', true );
			$this->_step_size          = get_post_meta( $post->ID, '_step_size', true );
			$this->_target             = get_post_meta( $post->ID, '_target', true );
			$this->_nofollow           = get_post_meta( $post->ID, '_nofollow', true );
			$types                     = array_keys( Options::get_field_list() );
			$this->_meta_key           = '_field_' . $post->ID;
			if ( ! $this->_type && ! in_array( $this->_type, $types ) ) {
				update_post_meta( $post->id, '_type', 'text' );
				$this->_type = "text";
			}
		} else {
			return false;
		}
	}

	public function getValue( $post_id ) {
		if ( ! Functions::meta_exist( $post_id, $this->getMetaKey() ) ) {
			$value = $this->getDefaultValue();
		} else {
			if ( $this->getType() == 'checkbox' ) {
				$value = get_post_meta( $post_id, $this->getMetaKey() );
			} else {
				$value = get_post_meta( $post_id, $this->getMetaKey(), true );
			}
		}

		return $value;
	}

	public function getCustomFieldFormattedValue( $post_id ) {

		$value = $this->getValue( $post_id );
		if ( 'url' == $this->getType() && ! filter_var( $value, FILTER_VALIDATE_URL ) === false ) {
			$value = esc_url( $value );
		} else if ( in_array( $this->getType(), array( 'select', 'radio' ) ) ) {
			$options = $this->getOptions();
			if ( ! empty( $options['choices'] ) && ! empty( $options['choices'][ $value ] ) ) {
				$value = $options['choices'][ $value ];
			}
			$value = esc_html( $value );
		} else if ( 'checkbox' == $this->getType() && is_array( $value ) ) {
			$options = $this->getOptions();
			$items   = array();
			if ( ! empty( $options['choices'] ) ) {
				foreach ( $value as $item ) {
					if ( ! empty( $options['choices'][ $item ] ) ) {
						$items[] = $options['choices'][ $item ];
					}
				}
			}
			if ( ! empty( $items ) ) {
				$value = implode( ", ", $items );
			}
		} else if ( 'text' == $this->getType() ) {
			$value = esc_html( $value );
		} else if ( 'textarea' == $this->getType() ) {
			$value = esc_html( $value );
		}

		return $value;
	}

	public function getFormattedListableValue( $post_id ) {

		$value = $this->getValue( $post_id );
		if ( 'url' == $this->getType() && filter_var( $value, FILTER_VALIDATE_URL ) ) {
			$nofollow = ! empty( $this->getNofollow() ) ? ' rel="nofollow"' : '';
			$value    = sprintf( '<a href="%1$s" target="%2$s"%3$s>%1$s</a>', $value,
				$this->getTarget(),
				$nofollow );
		} else if ( in_array( $this->getType(), array( 'select', 'radio' ) ) ) {
			$options = $this->getOptions();
			if ( ! empty( $options['choices'] ) && ! empty( $options['choices'][ $value ] ) ) {
				$value = $options['choices'][ $value ];
			}
		} else if ( 'checkbox' == $this->getType() && is_array( $value ) ) {
			$options = $this->getOptions();
			$items   = array();
			if ( ! empty( $options['choices'] ) ) {
				foreach ( $value as $item ) {
					if ( ! empty( $options['choices'][ $item ] ) ) {
						$items[] = $options['choices'][ $item ];
					}
				}
			}
			if ( ! empty( $items ) ) {
				$value = implode( ", ", $items );
			}
		} else if ( 'text' == $this->getType() ) {
			$value = esc_attr( $value );
		} else if ( 'textarea' == $this->getType() ) {
			$value = esc_html( $value );
		}

		return $value;
	}

	public function get_field_data() {
		$html = null;
		// Set right ID if existing field
		$clasess = 'postbox';
		$id      = $this->_type . '-' . $this->_field_id;
		if ( $this->_slug ) {
			$clasess = 'closed ' . $clasess;
		}

		$icon   = Options::get_field_list()[ $this->_type ]['symbol'];
		$icon   = "<i class='rtcl-icon rtcl-icon-{$icon}'></i>";
		$title  = ! empty( $this->_label ) ? $this->_label : __( 'Untitled', 'classified-listing' );
		$title  = sprintf(
			'<span class="rtcl-legend-update">%s</span> <span class="description">(%s)</span>',
			stripslashes( $title ),
			Options::get_field_list()[ $this->_type ]['name']
		);
		$box_id = sprintf( 'rtcl-custom-field-%s', $id );
		$html   = sprintf(
			'<div id="%s" class="%s" data-id="%s"><div class="handlediv" title="%s"><br></div><h3 class="hndle ui-sortable-handle">%s%s</h3><div class="inside">%s</div></div>',
			esc_attr( $box_id ),
			esc_attr( $clasess ),
			$this->_field_id,
			esc_attr__( 'Click to toggle', 'classified-listing' ),
			$icon,
			$title,
			$this->render()
		);

		return $html;
	}

	private function render() {
		$html    = null;
		$field   = Options::get_field_list()[ $this->_type ];
		$options = Options::get_field_list()[ $this->_type ]['options'];
		if ( ! empty( $options ) ) {
			foreach ( $options as $optName => $option ) {
				$id   = $this->_type . '-' . rand();
				$html .= "<div class='rtcl-cfg-field-group'>";
				$html .= "<div class='rtcl-cfg-field-label'><label class='rtcl-cfg-label' for='{$id}'>{$option['label']}</label></div>";
				$html .= "<div class='rtcl-cfg-field'>" . $this->createField( $optName, $option, $id ) . "</div>";
				$html .= "</div>";
			}
		}

		$html .= '<span href="#" class="js-rtcl-field-remove rtcl-field-remove" data-message-confirm="' . __( "Are you sure?",
				"classified-listing" ) . '"><span class="dashicons dashicons-trash"></span> Remove field</a>';

		return $html;
	}

	private function createField( $optName, $option = array(), $id ) {
		$html        = null;
		$type        = $option['type'];
		$placeholder = ! empty( $option['placeholder'] ) ? " placeholder='{$option['placeholder']}'" : null;
		$class       = ! empty( $option['class'] ) ? $option['class'] : null;
		switch ( $type ) {
			case 'true_false':
				$html .= "<input id='{$id}' value='1' class='widefat {$class}' type='checkbox' name='rtcl[fields][{$this->_field_id}][{$optName}]'>";
				break;
			case 'checkbox':
			case 'select':
				$html         .= "<div class='rtcl-select-options-wrap' data-type='{$type}'>";
				$html         .= "<table class='striped rtcl-select-options-table rtcl-fields-field-value-options'>
									<thead>
										<tr>
											<th> </th>
											<th>" . __( 'Display text', 'classified-listing' ) . "</th>
											<th>" . __( 'Value', 'classified-listing' ) . "</th>
											<th>" . __( 'Default', 'classified-listing' ) . "</th>
											<th> </th>
										</tr>
									</thead>";
				$html         .= "<tbody class='rtcl-fields-select-sortable'>";
				$default_name = "rtcl[fields][{$this->_field_id}][{$optName}][default]";
				$default_type = "radio";
				if ( $type == 'checkbox' ) {
					$default_name = "rtcl[fields][{$this->_field_id}][{$optName}][default][]";
					$default_type = 'checkbox';
				}
				if ( ! empty( $this->_options['choices'] ) ) {
					foreach ( $this->_options['choices'] as $optId => $option ) {
						$id      = uniqid();
						$checked = ! empty( $this->_options['default'] ) && $this->_options['default'] == $optId ? " checked='checked'" : null;
						if ( $type == 'checkbox' ) {
							$defaultValues = $this->_options['default'];
							$checked       = ! empty( $defaultValues ) && is_array( $defaultValues ) && in_array( $optId,
								$defaultValues ) == $optId ? " checked='checked'" : null;
						}
						$html .= "<tr>
												<td class='num'><span class='js-types-sort-button hndle dashicons dashicons-menu'></span></td>
												<td><input type='text' name='rtcl[fields][{$this->_field_id}][{$optName}][choices][{$id}][title]' value='{$option}' ></td>
												<td><input type='text' name='rtcl[fields][{$this->_field_id}][{$optName}][choices][{$id}][value]' value='{$optId}' ></td>
												<td class='num'><input type='{$default_type}' name='{$default_name}' {$checked} value='{$id}' ></td>
												<td class='num'><span class='rtcl-delete-option dashicons dashicons-trash'></span></td>
											</tr>";
					}
				} else {
					$id   = uniqid();
					$html .= "<tr>
											<td class='num'><span class='js-types-sort-button hndle dashicons dashicons-menu'></span></td>
											<td><input type='text' name='rtcl[fields][{$this->_field_id}][{$optName}][choices][{$id}][title]' value='Option title 1' ></td>
											<td><input type='text' name='rtcl[fields][{$this->_field_id}][{$optName}][choices][{$id}][value]' value='option-title-1' ></td>
											<td class='num'><input type='{$default_type}' name='{$default_name}' value='{$id}' ></td>
											<td class='num'><span class='rtcl-delete-option dashicons dashicons-trash'></span></td>
										</tr>";
				}
				$html .= "</tbody>";
				if ( $type == 'select' ) {
					$ndId      = 'select-radio-' . time();
					$ndChecked = empty( $this->_options['default'] ) ? " checked='checked'" : null;
					$html      .= "<tfoot><td> </td><td> </td><td><label for='{$ndId}'>" . __( "No Default",
							"classified-listing" ) . "</label></td><td><input id='$ndId' type='radio' name='{$default_name}' $ndChecked value='' ></td><td> </td><tfoot>";
				}
				$html .= "</table>";
				$html .= "<a class='button rtcl-add-new-option' data-name='rtcl[fields][{$this->_field_id}][{$optName}]'>" . __( "Add Option",
						"classified-listing" ) . "</a>";
				$html .= "</div>";
				break;
			case 'number':
				$html .= "<input $placeholder id='{$id}' value='{$this->$optName}' class='widefat {$class}' type='number' step='any' name='rtcl[fields][{$this->_field_id}][{$optName}]'>";
				break;
			case 'textarea':
				$html .= "<textarea rows='5' $placeholder name='rtcl[fields][{$this->_field_id}][{$optName}]' class='widefat {$class}' id='{$id}'>{$this->$optName}</textarea>";
				break;
			case 'radio':
				if ( ! empty( $option['options'] ) ) {
					$html .= "<ul class='rtcl-radio-list radio horizontal'>";
					foreach ( $option['options'] as $optId => $opt ) {
						$checked = $this->$optName == $optId ? " checked='checked'" : '';
						$html    .= "<li class='rtcl-radio-item'><label for='{$id}-{$opt}'><input type='radio' id='{$id}-{$opt}' {$checked} name='rtcl[fields][{$this->_field_id}][{$optName}]' value='{$optId}'> {$opt}</label></li>";
					}
					$html .= "</ul>";
				}
				break;
			default:
				$html .= "<input $placeholder id='{$id}' value='{$this->$optName}' class='widefat {$class}' type='text' name='rtcl[fields][{$this->_field_id}][{$optName}]'>";
				break;

		}

		return $html;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->_type;
	}

	/**
	 * @return mixed
	 */
	public function getLabel() {
		return $this->_label;
	}

	/**
	 * @return mixed
	 */
	public function getSlug() {
		return $this->_slug;
	}

	/**
	 * @return mixed
	 */
	public function getPlaceholder() {
		return $this->_placeholder;
	}

	/**
	 * @return mixed
	 */
	public function getDescription() {
		return $this->_description;
	}

	/**
	 * @return mixed
	 */
	public function getMessage() {
		return $this->_message;
	}

	/**
	 * @return array
	 */
	public function getOptions() {
		return $this->_options;
	}

	/**
	 * @return mixed
	 */
	public function getRequired() {
		return $this->_required;
	}

	/**
	 * @return mixed
	 */
	public function isSearchable() {
		return $this->_searchable;
	}

	/**
	 * @return mixed
	 */
	public function getListable() {
		return $this->_listable;
	}

	/**
	 * @return mixed
	 */
	public function getDefaultValue() {
		$default_value = null;
		if ( in_array( $this->getType(), array( 'checkbox', 'select', 'radio' ) ) ) {
			$options = $this->getOptions();
			if ( $this->getType() == 'checkbox' ) {
				$default_value = ! empty( $options['default'] ) && is_array( $options['default'] ) ? $options['default'] : array();
			} else {
				$default_value = ! empty( $options['default'] ) ? trim( $options['default'] ) : null;
			}
		} else {
			$default_value = $this->_default_value;
		}

		return $default_value;
	}

	/**
	 * @return mixed
	 */
	public function getValidation() {
		return $this->_validation;
	}

	/**
	 * @return mixed
	 */
	public function getValidationMessage() {
		return $this->_validation_message;
	}

	/**
	 * @return mixed
	 */
	public function getMin() {
		return $this->_min;
	}

	/**
	 * @return mixed
	 */
	public function getMax() {
		return $this->_max;
	}

	/**
	 * @return mixed
	 */
	public function getStepSize() {
		return $this->_step_size;
	}

	/**
	 * @return mixed
	 */
	public function getFieldId() {
		return $this->_field_id;
	}

	/**
	 * @return mixed
	 */
	public function getRows() {
		return $this->_rows;
	}

	/**
	 * @return mixed
	 */
	public function getTarget() {
		return $this->_target ? '_blank' : null;
	}

	/**
	 * @return mixed
	 */
	public function getNofollow() {
		return $this->_nofollow;
	}

	/**
	 * @return mixed
	 */
	public function getMetaKey() {
		return $this->_meta_key;
	}

	public function getSanitizedValue( $value ) {
		switch ( $this->getType() ) {
			case 'textarea' :
				$value = esc_textarea( $value );
				break;
			case 'select' :
			case 'radio'  :
			case 'text' :
				$value = sanitize_text_field( $value );
				break;
			case 'checkbox' :
				$value = is_array( $value ) ? $value : array();
				$value = array_map( 'esc_attr', $value );
				break;
			case 'url' :
				$value = esc_url_raw( $value );
				break;
			default :
				$value = sanitize_text_field( $value );
		}

		return $value;
	}

	public function saveSanitizedValue( $post_id, $value ) {
		$post_id = $post_id ? absint( $post_id ) : get_the_ID();
		$value   = $this->getSanitizedValue( $value );
		if ( $this->getType() == 'checkbox' ) {
			delete_post_meta( $post_id, $this->getMetaKey() );
			if ( ! empty( $value ) && is_array( $value ) ) {
				foreach ( $value as $val ) {
					if ( $val ) {
						add_post_meta( $post_id, $this->getMetaKey(), $val );
					}
				}
			}
		} else {
			update_post_meta( $post_id, $this->getMetaKey(), $value );
		}
	}


}