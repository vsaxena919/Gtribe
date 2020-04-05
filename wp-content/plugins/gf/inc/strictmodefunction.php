<?php
function retrieve_orders_ids_from_a_product_id( $product_id ) {
    global $wpdb ;
    $p_id            = $product_id ;
    $cc              = $wpdb->prefix ;
    $orders_statuses = "'wc-completed', 'wc-processing', 'wc-on-hold'" ;
    $sql             = "SELECT DISTINCT woi.order_id FROM " . $cc . "woocommerce_order_itemmeta as woim, " . $cc . "woocommerce_order_items as woi, " . $cc
            . "posts as p WHERE woi.order_item_id = woim.order_item_id AND woi.order_id = p.ID AND p.post_status IN ($orders_statuses)"
            . " AND woim.meta_key like '_product_id' AND woim.meta_value = $p_id" ;
    $result          = $wpdb->get_results ( $sql , ARRAY_A ) ;
    $total           = 0 ;
    foreach ( $result as $rr ) {
        $order_id = $rr[ 'order_id' ] ;
        $total    += retrieve_product_id_from_a_order_id ( $order_id , $p_id ) ;
    }
    return $total;
}

function retrieve_product_id_from_a_order_id( $order_id, $product_id ) {
    global $wpdb ;
    $o_id       = $order_id ;
    $p_id       = $product_id;
    $cc         = $wpdb->prefix ;
    $sql        = "SELECT woim.meta_value FROM " . $cc . "woocommerce_order_itemmeta as woim, " . $cc . "woocommerce_order_items as woi "
            . "WHERE woi.order_id = $o_id AND woim.meta_key like ('_product_id') AND woim.order_item_id = woi.order_item_id" ;
    $result     = $wpdb->get_results ( $sql , ARRAY_A ) ;
    $each_total = retrieve_product_cost_from_a_product_id ( $p_id , $o_id ) ;
    return $each_total ;
}

function retrieve_product_cost_from_a_product_id( $product_id , $order_id ) {
    global $wpdb ;
    $p_id      = $product_id ;
    $o_id      = $order_id ;
    $cc        = $wpdb->prefix ;
    $sql      = "SELECT woim.order_item_id from " . $cc . "woocommerce_order_itemmeta as woim where woim.meta_key like '_product_id' AND meta_value like ($product_id)" ;
    $sql1      = "SELECT woi.order_item_id from " . $cc . "woocommerce_order_items as woi where woi.order_id = $o_id AND order_item_id in ($sql)";
    $sql2      = "SELECT woim.meta_value from " . $cc . "woocommerce_order_itemmeta as woim where woim.meta_key like '_line_total' AND order_item_id in ($sql1)" ;
    $result    = $wpdb->get_results ( $sql2 , ARRAY_A ) ;
    // var_dump($sql);
    $db_result = $result[ 0 ] ;
    return $db_result[ 'meta_value' ] ;
}
