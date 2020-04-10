<?php

namespace Rtcl\Models;

use Rtcl\Resources\Options;

class Pricing
{
    protected $id;
    protected $price;
    protected $title;
    protected $description;
    protected $type;
    protected $visible;
    protected $featured;
    protected $top;

    function __construct($pricing_id) {
        $post = get_post($pricing_id);
        if (is_object($post) && $post->post_type == rtcl()->post_type_pricing) {
            $this->setData($post);
        } else {
            return false;
        }
    }


    /**
     * Course is exists if the post is not empty
     *
     * @return bool
     */
    public function exists() {
        return rtcl()->post_type_pricing === get_post_type($this->getId());
    }

    private function setData($post) {
        $this->id = $post->ID;
        $this->title = $post->post_title;
        $this->price = get_post_meta($this->id, 'price', true);
        $this->description = get_post_meta($this->id, 'description', true);
        $this->type = get_post_meta($this->id, 'pricing_type', true);
        $this->visible = absint(get_post_meta($this->id, 'visible', true));
        $this->featured = get_post_meta($this->id, 'featured', true);
        $this->top = get_post_meta($this->id, '_top', true);
    }

    /**
     * @return mixed
     */
    public function getPrice() {
        return $this->price;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getType() {
        $types = Options::get_pricing_types();
        return in_array($this->type, array_keys($types)) ? $this->type : 'regular';
    }

    /**
     * @return mixed
     */
    public function getVisible() {
        return $this->visible;
    }

    /**
     * @return mixed
     */
    public function getFeatured() {
        return $this->featured;
    }

    /**
     * @return mixed
     */
    public function getTop() {
        return $this->top;
    }

}