<?php


namespace Rtcl\Controllers\Hooks;


use Rtcl\Helpers\Functions;
use Rtcl\Models\Payment;

class Comments
{

    static function init() {

        // Secure order notes.
        add_filter('comments_clauses', array(__CLASS__, 'exclude_payment_comments'), 10, 1);
        add_filter('comment_feed_where', array(__CLASS__, 'exclude_payment_comments_from_feed_where'));

        add_action('wp_ajax_rtcl_delete_payment_note', array(__CLASS__, 'delete_payment_note'));
        add_action('wp_ajax_rtcl_add_payment_note', array(__CLASS__, 'add_payment_note'));

    }


    /**
     * Delete an order note.
     *
     * @return void True on success, false on failure.
     * @since  1.4.0
     */
    static function add_payment_note() {
        if (!Functions::verify_nonce() || !isset($_POST['post_id'], $_POST['note'], $_POST['note_type'])) {
            wp_die(-1);
        }

        $post_id = absint($_POST['post_id']);
        $note = wp_kses_post(trim(wp_unslash($_POST['note'])));
        $note_type = Functions::clean(wp_unslash($_POST['note_type']));

        $is_customer_note = ('customer' === $note_type) ? 1 : 0;
        $html = '';
        if ($post_id > 0) {
            $payment = new Payment($post_id);
            $comment_id = $payment->add_note($note, $is_customer_note, true);
            $note = Functions::get_payment_note($comment_id);

            $note_classes = array('note');
            $note_classes[] = $is_customer_note ? 'customer-note' : '';
            $note_classes = apply_filters('rtcl_payment_note_class', array_filter($note_classes), $note);
            ob_start();
            ?>
            <li rel="<?php echo absint($note->id); ?>" class="<?php echo esc_attr(implode(' ', $note_classes)); ?>">
                <div class="note_content">
                    <?php echo wp_kses_post(wpautop(wptexturize(make_clickable($note->content)))); ?>
                </div>
                <p class="meta">
                    <abbr class="exact-date" title="<?php echo esc_attr($note->date_created->date('y-m-d h:i:s')); ?>">
                        <?php
                        /* translators: $1: Date created, $2 Time created */
                        printf(esc_html__('added on %1$s at %2$s', 'classified-listing'), esc_html($note->date_created->date_i18n(Functions::date_format())), esc_html($note->date_created->date_i18n(Functions:: time_format())));
                        ?>
                    </abbr>
                    <?php
                    if ('system' !== $note->added_by) :
                        /* translators: %s: note author */
                        printf(' ' . esc_html__('by %s', 'classified-listing'), esc_html($note->added_by));
                    endif;
                    ?>
                    <a href="#" class="delete_note"
                       role="button"><?php esc_html_e('Delete note', 'classified-listing'); ?></a>
                </p>
            </li>
            <?php
            $html = ob_get_clean();
        }
        wp_send_json(compact('html'));
    }

    /**
     * Delete an order note.
     *
     * @return void True on success, false on failure.
     * @since  1.4.0
     */
    static function delete_payment_note() {
        $success = false;
        if (Functions::verify_nonce()) {
            $note_id = isset($_POST['note_id']) ? absint($_POST['note_id']) : 0;
            if ($note_id > 0) {
                $success = wp_delete_comment($note_id, true);
            }
        }
        wp_send_json(compact('success'));
    }


    /**
     * @param array $clauses A compacted array of comment query clauses.
     *
     * @return array
     */
    public static function exclude_payment_comments($clauses) {
        $clauses['where'] .= ($clauses['where'] ? ' AND ' : '') . " comment_type != 'rtcl_payment_note' ";

        return $clauses;
    }

    /**
     * Exclude order comments from queries and RSS.
     *
     * @param string $where The WHERE clause of the query.
     *
     * @return string
     */
    public static function exclude_payment_comments_from_feed_where($where) {
        return $where . ($where ? ' AND ' : '') . " comment_type != 'rtcl_payment_note' ";
    }

}