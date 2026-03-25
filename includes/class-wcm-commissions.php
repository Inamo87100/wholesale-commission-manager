<?php

add_action('woocommerce_order_status_completed', function($order_id){
    $order = wc_get_order($order_id);
    $commission_total = 0;

    foreach ($order->get_items() as $item){
        if ($item->get_product_id() == WCM_PRODUCT_ID){
            $commission_total += $item->get_total() * WCM_COMMISSION_RATE;
        }
    }

    if ($commission_total > 0){
        update_post_meta($order_id, '_wm_commission', $commission_total);
        update_post_meta($order_id, '_wm_commission_paid', 'no');
        wcm_notify_new_commission($order_id);
    }
});

// Notifica nuova commissione
if (!function_exists('wcm_notify_new_commission')) {
    function wcm_notify_new_commission($order_id){
        $order = wc_get_order($order_id);
        $user  = $order->get_user();
        if(!$user) return;
        $commission = get_post_meta($order_id, '_wm_commission', true);
        wp_mail(
            $user->user_email,
            __("Nuova commissione 💰", 'wholesale-commission-manager'),
            sprintf(__("Hai guadagnato €%s", 'wholesale-commission-manager'), number_format($commission,2))
        );
    }
}

// Shortcode visualizzazione dati+grafico (JSON)
add_shortcode('wm_commissioni_advanced', function(){
    $user_id = get_current_user_id();
    $month = isset($_GET['mese']) ? intval($_GET['mese']) : date('m');
    $year  = isset($_GET['anno']) ? intval($_GET['anno']) : date('Y');
    $orders = wc_get_orders([
        'customer_id' => $user_id,
        'status' => 'completed',
        'limit' => -1
    ]);
    $rows = [];
    $chart = [];
    foreach ($orders as $o){
        $date = $o->get_date_created();
        if ($date->date('m') != $month || $date->date('Y') != $year) continue;
        $c = get_post_meta($o->get_id(), '_wm_commission', true);
        if (!$c) continue;
        $paid = get_post_meta($o->get_id(), '_wm_commission_paid', true);
        $rows[] = [
            'order_id' => $o->get_id(),
            'date' => $date->date('d/m/Y'),
            'commission' => $c,
            'status' => $paid == 'yes' ? 'Pagata' : 'Da pagare'
        ];
        $day = $date->date('d');
        if (!isset($chart[$day])) $chart[$day] = 0;
        $chart[$day] += $c;
    }
    return json_encode([
        'rows' => $rows,
        'chart' => $chart
    ]);
});
