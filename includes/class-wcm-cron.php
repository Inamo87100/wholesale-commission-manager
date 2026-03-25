<?php

add_action('init', function(){
    if (!wp_next_scheduled('wcm_daily_report')){
        wp_schedule_event(time(), 'daily', 'wcm_daily_report');
    }
});

add_action('wcm_daily_report', function(){
    $users = get_users(['role'=>'affiliato']);
    foreach ($users as $user){
        $orders = wc_get_orders([
            'customer_id'=>$user->ID,
            'status'=>'completed',
            'limit'=>-1
        ]);
        $tot=0;
        foreach ($orders as $o){
            $c=get_post_meta($o->get_id(),'_wm_commission',true);
            $p=get_post_meta($o->get_id(),'_wm_commission_paid',true);
            if($c && $p!='yes') $tot+=$c;
        }
        if($tot>0){
            wp_mail($user->user_email, __("Report commissioni","wholesale-commission-manager"), sprintf(__("Totale: €%s","wholesale-commission-manager"), number_format($tot,2)));
        }
    }
});
