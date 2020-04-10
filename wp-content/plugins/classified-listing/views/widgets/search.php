<p>
    <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'classified-listing' ); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
           name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
           value="<?php echo esc_attr( $instance['title'] ); ?>">
</p>
<p>
    <label for="<?php echo $this->get_field_id( 'style_label' ); ?>"> <?php _e( 'Style', 'classified-listing' ); ?> </label><br>
    <label for="<?php echo $this->get_field_id( 'style_vertical' ); ?>">
        <input class="" id="<?php echo $this->get_field_id( 'style_vertical' ); ?>"
               name="<?php echo $this->get_field_name( 'style' ); ?>" type="radio"
               value="vertical" <?php if ( $instance['style'] === 'vertical' ) {
			echo 'checked="checked"';
		} ?> />
		<?php _e( 'Vertical', 'classified-listing' ); ?>
    </label><br>
    <label for="<?php echo $this->get_field_id( 'style_inline' ); ?>">
        <input class="" id="<?php echo $this->get_field_id( 'style_inline' ); ?>"
               name="<?php echo $this->get_field_name( 'style' ); ?>" type="radio"
               value="inline" <?php if ( $instance['style'] === 'inline' ) {
			echo 'checked="checked"';
		} ?> />
		<?php _e( 'Inline', 'classified-listing' ); ?>
    </label>
</p>

<p>
    <input <?php checked( $instance['search_by_category'] ); ?>
            id="<?php echo $this->get_field_id( 'search_by_category' ); ?>"
            name="<?php echo $this->get_field_name( 'search_by_category' ); ?>" type="checkbox"/>
    <label for="<?php echo $this->get_field_id( 'search_by_category' ); ?>"><?php _e( 'Search by Category', 'classified-listing' ); ?></label>
</p>

<p>
    <input <?php checked( $instance['search_by_location'] ); ?>
            id="<?php echo $this->get_field_id( 'search_by_location' ); ?>"
            name="<?php echo $this->get_field_name( 'search_by_location' ); ?>" type="checkbox"/>
    <label for="<?php echo $this->get_field_id( 'search_by_location' ); ?>"><?php _e( 'Search by Location', 'classified-listing' ); ?></label>
</p>

<p>
    <input <?php checked( $instance['search_by_listing_types'] ); ?>
            id="<?php echo $this->get_field_id( 'search_by_listing_types' ); ?>"
            name="<?php echo $this->get_field_name( 'search_by_listing_types' ); ?>" type="checkbox"/>
    <label for="<?php echo $this->get_field_id( 'search_by_listing_types' ); ?>"><?php _e( 'Search by types', 'classified-listing' ); ?></label>
</p>

<p>
    <input <?php checked( $instance['search_by_price'] ); ?>
            id="<?php echo $this->get_field_id( 'search_by_price' ); ?>"
            name="<?php echo $this->get_field_name( 'search_by_price' ); ?>" type="checkbox"/>
    <label for="<?php echo $this->get_field_id( 'search_by_price' ); ?>"><?php _e( 'Search by Price', 'classified-listing' ); ?></label>
</p>