<?php
/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package olympus
 */
$layout       = get_query_var( 'sidebar-conf', array( 'position' => 'full' ) );
$column_class = olympus_get_column_classes( $layout );
$column_class[]	 = 'may-contain-custom-bg';

$olympus       = Olympus_Options::get_instance();
$post_elements = $olympus->get_option_final( 'blog_post_elements', array() );

$img_width  = 401;
$img_height = '';

$column_class[] = 'sorting-item';

$the_terms = get_the_terms( get_the_ID(), 'category' );
if ( $the_terms && !is_wp_error( $the_terms ) ) :
    foreach ( $the_terms as $the_term ) {
        $column_class[] = esc_html( $the_term->slug );
    }
endif;
?>

<div class="<?php echo esc_attr( implode( ' ', $column_class ) ) ?>">
    <div class="ui-block">
        <!-- Post -->
        <article id="post-<?php the_ID(); ?>" <?php post_class( 'blog-post blog-post-v2' ); ?>>
            <?php if ( has_post_thumbnail() ) { ?>
                <div class="post-thumb">
                    <?php olympus_generate_thumbnail( $img_width, $img_height, false ); ?>
                    <a href="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'full' ) ); ?>" class="post-type-icon js-zoom-image">
                        <?php echo olympus_svg_icon( 'olymp-camera-icon' ) ?>
                    </a>
                </div>
            <?php } ?>
            <div class="post-content">
                <?php
                if ( 'yes' === olympus_akg( 'blog_post_categories', $post_elements, 'yes' ) ) {
                    echo olympus_post_category_list( get_the_ID(), ' ', true );
                }
                ?>

                <?php the_title( '<h4 class="post-title entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h4>' ); ?>

                <?php
                if ( 'yes' === olympus_akg( 'blog_post_excerpt/value', $post_elements, 'yes' ) ) {
                    $excerpt_length = olympus_akg( 'blog_post_excerpt/yes/length', $post_elements, '20' );
                    echo olympus_html_tag( 'p', array(), olympus_generate_short_excerpt( get_the_ID(), $excerpt_length, false ) );
                }
                ?>
                <?php
                wp_link_pages( array(
                    'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'olympus' ),
                    'after'  => '</div>',
                ) );
                ?>

                <?php if ( 'yes' === olympus_akg( 'blog_post_meta', $post_elements, 'yes' ) ) { ?>

                    <div class="post__author author vcard inline-items">
                        <?php $author_id = get_the_author_meta( 'ID' ); ?>
                        <?php echo get_avatar( $author_id, 28 ); ?>

                        <div class="author-date not-uppercase">
                            <?php olympus_post_author( false ); ?>
                            <?php olympus_posted_time(); ?>
                        </div>
                    </div>
                <?php } ?>
                <div class="post-additional-info inline-items">
                    <?php
                    if ( 'yes' === olympus_akg( 'blog_post_reactions', $post_elements, 'yes' ) ) {
                        echo olympus_get_post_reactions( 'compact' );
                    }
                    ?>
                    <div class="comments-shared">
                        <?php olympus_comments_count(); ?>
                    </div>
                </div>
            </div>
        </article><!-- #post-<?php the_ID(); ?> -->
    </div>
</div>
