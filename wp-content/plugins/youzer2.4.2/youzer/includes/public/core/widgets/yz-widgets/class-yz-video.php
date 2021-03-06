<?php

class YZ_Video {

    /**
     * # Content.
     */
    function widget() {

        // Get Widget Data
        $video_url = esc_url( yz_data( 'wg_video_url' ) );

        if ( empty( $video_url ) ) {
            return;
        }

        // Init Vars.
        $video_desc  = sanitize_textarea_field( yz_data( 'wg_video_desc' ) );
        $video_title = sanitize_text_field( yz_data( 'wg_video_title' ) );
        
        ?>

        <div class="yz-video-content">
            <div class="fittobox">
                <?php
                    if ( false != filter_var( $video_url, FILTER_VALIDATE_URL )  ) {
                        echo apply_filters( 'the_content', $video_url );   
                    }
                ?>
            </div>

            <?php if ( ! empty( $video_title ) || ! empty( $video_desc ) ) : ?>
                <div class="yz-video-head">
                    <h2 class="yz-video-title"><?php echo $video_title; ?></h2>
                    <?php if ( $video_desc ) : ?>
                        <div class="yz-video-desc"><?php echo apply_filters( 'the_content', $video_desc ); ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>

        <?php

    }

}