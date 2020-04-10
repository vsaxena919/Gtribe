<?php

namespace Rtcl\Controllers\Hooks;

use Rtcl\Helpers\Functions;

class Filters {

    public static function init()
    {
        add_filter('comments_open', array(__CLASS__, 'comment_open'), 20, 2);
        add_filter('get_comments_number', array(__CLASS__, 'get_comments_number'), 20, 2);
    }

    public static function get_comments_number($count, $post_id)
    {
        if (is_singular(rtcl()->post_type)) {
            $count = Functions::get_option_item('rtcl_moderation_settings', 'has_comment_form', false, 'checkbox') ? $count : 0;
        }

	    return apply_filters('rtcl_get_comments_number', $count, $post_id);
    }

    public static function comment_open($open, $post_id)
    {
	    if (rtcl()->post_type === get_post_type($post_id)) {
		    $open = false;
		    if(Functions::get_option_item('rtcl_moderation_settings', 'has_comment_form', false, 'checkbox')){
			    $open = true;
		    }
	    }

	    return apply_filters('rtcl_has_comment_form', $open, $post_id);
    }

    public static function beforeUpload()
    {
        add_filter('upload_dir', array(__CLASS__, 'custom_upload_dir'));
    }

    public static function afterUpload()
    {
        remove_filter('upload_dir', array(__CLASS__, 'custom_upload_dir'));
    }

    public static function custom_upload_dir($dirs)
    {
        $custom_dir = '/' . rtcl()->upload_directory . $dirs['subdir'];
        $dirs['subdir'] = $custom_dir;
        $dirs['path'] = $dirs['basedir'] . $custom_dir;
        $dirs['url'] = $dirs['baseurl'] . $custom_dir;
        return $dirs;
    }

}