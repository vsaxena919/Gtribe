<?php
/** That's all, stop editing from here * */
global $rtmedia_backbone;

$rtmedia_backbone = array(
    'backbone'        => false,
    'is_album'        => false,
    'is_edit_allowed' => false,
);

//todo: nonce verification
$rtmedia_backbone[ 'backbone' ] = filter_input( INPUT_POST, 'backbone', FILTER_VALIDATE_BOOLEAN );

$is_album = filter_input( INPUT_POST, 'is_album', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
if ( isset( $is_album[ 0 ] ) ) {
    $rtmedia_backbone[ 'is_album' ] = $is_album[ 0 ];
}

$is_edit_allowed = filter_input( INPUT_POST, 'is_edit_allowed', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
if ( isset( $is_edit_allowed[ 0 ] ) ) {
    $rtmedia_backbone[ 'is_edit_allowed' ] = $is_edit_allowed[ 0 ];
}
?>
<li class="photo-album-item-wrap rtmedia-list-item col-3-width" id="<?php echo rtmedia_id(); ?>">
    <div class="photo-album-item">

        <a href="<?php rtmedia_permalink(); ?>" title="<?php echo esc_attr( rtmedia_title() ); ?>">
            <div style="background-image: url(<?php rtmedia_image( 'rt_media_activity_image' ); ?>);" class="rtmedia-item-thumbnail photo-item">
                <div class="overlay overlay-dark">
                    <div class="rtmedia-album-media-count">
                        <?php echo rtmedia_album_mediacounter(); ?>
                        <svg><use xlink:href="<?php echo get_template_directory_uri(); ?>/images/icons.svg#olymp-camera-icon"></use></svg>
                    </div>
                </div>
            </div>
            <?php
            /**
             * Filter to hide or show media titles in gallery.
             *
             * @param bool true Default value is true.
             */
            if ( apply_filters( 'rtmedia_media_gallery_show_media_title', true ) ) {
                ?>
                <div class="content">
                    <h5 class="title"><?php echo esc_html( rtmedia_title() ); ?></h5>
                    <?php olympus_rtmedia_media_description(); ?>
                </div>
                <?php
            }
            ?>
        </a>
        <?php
        /**
         * Fires after album gallery item.
         */
        do_action( 'rtmedia_after_album_gallery_item' );
        ?>
    </div>
</li> <!-- End of .rtmedia-list-item -->
