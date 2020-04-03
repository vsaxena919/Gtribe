<?php
$sa_content_config = $this->get_config();
?>
<div class="sa-field-front sa-field-content" style="width: calc(<?php echo $this->get_width();?> - 30px)">

    <div class="editor-container">
        <span class="titlelabel"><?php echo $sa_content_config['field_config']['label']; ?></span>
        <?php
        $editor_id = $this->slug;
        $settings = array(
            'quicktags'     => false,
            'textarea_rows' => 15,
            'media_buttons' => false,
            'teeny' => true,
            'theme' => "simple"

        );
        wp_editor( $this->get_data($article_id, $_POST), $editor_id, $settings);
        ?>
    </div>
</div>