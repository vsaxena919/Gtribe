<?php

namespace Rtcl\Models;

class Roles {

	public function add_default_caps() {
		global $wp_roles;

		if ( class_exists( '\WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {

			// Add the "administrator" capabilities
			$capabilities = $this->get_core_caps();
			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->add_cap( 'administrator', $cap );
				}
			}

			// Add Default caps
			$role_caps = $this->get_roles_default_caps();

			foreach ( $role_caps as $role => $caps ) {
				if ( is_array( $caps ) && ! empty( $caps ) ) {
					foreach ( $caps as $cap ) {
						$wp_roles->add_cap( $role, $cap );
					}
				}
				// Add extra role caps with specific role
				do_action( 'rtcl_roles_add_default_caps_' . $role, $wp_roles, rtcl()->post_type );
			}

			// Add extra work when rtcl role is
			do_action( 'rtcl_roles_add_default_caps', $wp_roles, rtcl()->post_type );
		}
	}

	public function get_core_caps() {

		$capabilities         = array();
		$capabilities['core'] = array( 'manage_rtcl_options' );
		$capability_types     = array( rtcl()->post_type );

		foreach ( $capability_types as $capability_type ) {

			$capabilities[ $capability_type ] = array(
				"add_{$capability_type}",
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",
			);
		}

		return apply_filters( 'rtcl_roles_get_core_caps', $capabilities, $this );
	}

	public function get_roles_default_caps() {

		$caps = [
			'editor'      => [
				'add_' . rtcl()->post_type,
				'edit_' . rtcl()->post_type . 's',
				'edit_others_' . rtcl()->post_type . 's',
				'publish_' . rtcl()->post_type . 's',
				'read_private_' . rtcl()->post_type . 's',
				'delete_' . rtcl()->post_type . 's',
				'delete_private_' . rtcl()->post_type . 's',
				'delete_published_' . rtcl()->post_type . 's',
				'delete_others_' . rtcl()->post_type . 's',
				'edit_private_' . rtcl()->post_type . 's',
				'edit_published_' . rtcl()->post_type . 's',
			],
			'author'      => [
				'add_' . rtcl()->post_type,
				'edit_' . rtcl()->post_type . 's',
				'publish_' . rtcl()->post_type . 's',
				'delete_' . rtcl()->post_type . 's',
				'delete_published_' . rtcl()->post_type . 's',
				'edit_published_' . rtcl()->post_type . 's',
			],
			'contributor' => [
				'add_' . rtcl()->post_type,
				'edit_' . rtcl()->post_type . 's',
				'publish_' . rtcl()->post_type . 's',
				'delete_' . rtcl()->post_type . 's',
				'delete_published_' . rtcl()->post_type . 's',
				'edit_published_' . rtcl()->post_type . 's',
			],
			'subscriber'  => [
				'add_' . rtcl()->post_type,
				'edit_' . rtcl()->post_type . 's',
				'publish_' . rtcl()->post_type . 's',
				'delete_' . rtcl()->post_type . 's',
				'delete_published_' . rtcl()->post_type . 's',
				'edit_published_' . rtcl()->post_type . 's',
			]
		];

		return apply_filters( 'rtcl_roles_get_roles_default_caps', $caps, $this );
	}

	public function get_default_caps() {
		$caps = [
			'add_' . rtcl()->post_type,
			'edit_' . rtcl()->post_type . 's',
			'publish_' . rtcl()->post_type . 's',
			'delete_' . rtcl()->post_type . 's',
			'delete_published_' . rtcl()->post_type . 's',
			'edit_published_' . rtcl()->post_type . 's',
		];

		return apply_filters( 'rtcl_roles_get_default_caps', $caps, $this );
	}

	/**
	 * @param array | string $roles
	 */
	public function add_custom_caps( $roles ) {
		if ( ! $roles ) {
			return;
		}

		$roles = is_array( $roles ) ? $roles : [ $roles ];

		global $wp_roles;

		if ( class_exists( '\WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {
			$caps = $this->get_default_caps();
			foreach ( $roles as $role ) {
				if ( is_array( $caps ) && ! empty( $caps ) ) {
					foreach ( $caps as $cap ) {
						$wp_roles->add_cap( $role, $cap );
					}
				}
			}
		}

	}


	/**
	 * @param array | string $roles
	 */
	public function remove_custom_caps( $roles ) {
		if ( ! $roles ) {
			return;
		}

		$roles = is_array( $roles ) ? $roles : [ $roles ];

		global $wp_roles;

		if ( class_exists( '\WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {
			$caps = $this->get_default_caps();
			foreach ( $roles as $role ) {
				if ( is_array( $caps ) && ! empty( $caps ) ) {
					foreach ( $caps as $cap ) {
						$wp_roles->remove_cap( $role, $cap );
					}
				}
			}
		}

	}

	public function meta_caps( $caps, $cap, $user_id, $args ) {

		// If editing, deleting, or reading a listing, get the post and post type object.
		if ( 'edit_' . rtcl()->post_type == $cap || 'delete_' . rtcl()->post_type == $cap || 'read_' . rtcl()->post_type == $cap ) {
			$post      = get_post( $args[0] );
			$post_type = get_post_type_object( $post->post_type );

			// Set an empty array for the caps.
			$caps = array();
		}

		// If editing a listing, assign the required capability.
		if ( 'edit_' . rtcl()->post_type == $cap ) {
			if ( $user_id == $post->post_author ) {
				$caps[] = $post_type->cap->edit_listings;
			} else {
				$caps[] = $post_type->cap->edit_others_listings;
			}
		} // If deleting a listing, assign the required capability.
		else if ( 'delete_' . rtcl()->post_type == $cap ) {
			if ( $user_id == $post->post_author ) {
				$caps[] = $post_type->cap->delete_listings;
			} else {
				$caps[] = $post_type->cap->delete_others_listings;
			}
		} // If reading a private listing, assign the required capability.
		else if ( 'read_' . rtcl()->post_type == $cap ) {
			if ( 'private' != $post->post_status ) {
				$caps[] = 'read';
			} elseif ( $user_id == $post->post_author ) {
				$caps[] = 'read';
			} else {
				$caps[] = $post_type->cap->read_private_listings;
			}
		}

		// Return the capabilities required by the user.
		return $caps;

	}

	public function remove_default_caps() {

		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {

			// Remove the "administrator" Capabilities
			$capabilities = $this->get_core_caps();

			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->remove_cap( 'administrator', $cap );
				}
			}

			// Remove Default caps
			$role_caps = $this->get_roles_default_caps();

			foreach ( $role_caps as $role => $caps ) {
				if ( is_array( $caps ) && ! empty( $caps ) ) {
					foreach ( $caps as $cap ) {
						$wp_roles->remove_cap( $role, $cap );
					}
				}
				// Remove extra role caps with specific role
				do_action( 'rtcl_roles_remove_default_caps_' . $role, $wp_roles, rtcl()->post_type );
			}

			// Remove extra work when rtcl role is remove cap
			do_action( 'rtcl_roles_remove_default_caps', $wp_roles, rtcl()->post_type );
		}
	}
}
