<?php

add_action('wp_ajax_wcm_pay', function() {
    check_ajax_referer('wcm_admin', 'nonce');
    if (!current_user_can('manage_woocommerce')) wp_die();
    $order_id = intval($_POST['order_id']);
    update_post_meta($order_id, '_wm_commission_paid', 'yes');
    $order = wc_get_order($order_id);
    $user  = $order->get_user();
    if ($user){
        $c = get_post_meta($order_id, '_wm_commission', true);
        wp_mail($user->user_email, __("Commissione pagata", 'wholesale-commission-manager'), sprintf(__("Ricevuti €%s", 'wholesale-commission-manager'), number_format($c,2)));
    }
    wp_die();
});

add_action('wp_ajax_wcm_pay_all', function() {
    check_ajax_referer('wcm_admin', 'nonce');
    if (!current_user_can('manage_woocommerce')) wp_die();
    $orders = wc_get_orders(['limit'=>-1]);
    foreach ($orders as $o){
        if (get_post_meta($o->get_id(),'_wm_commission',true)){
            update_post_meta($o->get_id(), '_wm_commission_paid', 'yes');
        }
    }
    wp_die();
});
