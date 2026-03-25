<?php

add_action('admin_menu', function(){
    add_menu_page(
        'Commissioni',
        'Commissioni',
        'manage_woocommerce',
        'wm-dashboard',
        'wcm_admin_page'
    );
});

// Dashboard admin
if (!function_exists('wcm_admin_page')) {
function wcm_admin_page(){
    $orders = wc_get_orders(['status'=>'completed','limit'=>-1]);

    // Nonce per sicurezza AJAX
    $nonce = wp_create_nonce('wcm_admin');

    echo "<div class='wrap'><h1>Dashboard Commissioni</h1>";
    echo "<button id='markAllPaid' data-nonce='$nonce'>Segna tutte pagate</button> ";
    echo "<button onclick='window.location=\"?page=wm-dashboard&export=1\"'>Export CSV</button>";

    echo "<table class='widefat'><tr><th>Cliente</th><th>Ordine</th><th>Commissione</th><th>Stato</th><th></th></tr>";

    foreach ($orders as $o){
        $c = get_post_meta($o->get_id(), '_wm_commission', true);
        if (!$c) continue;
        $paid = get_post_meta($o->get_id(), '_wm_commission_paid', true);

        echo "<tr>
                <td>{$o->get_billing_email()}</td>
                <td>#{$o->get_id()}</td>
                <td>€".number_format($c,2)."</td>
                <td>".($paid=='yes'?'Pagata':'Da pagare')."</td>
                <td>";
        if ($paid!='yes'){
            echo "<button class='pay' data-id='".$o->get_id()."' data-nonce='$nonce'>Paga</button>";
        }
        echo "</td></tr>";
    }
    echo "</table></div>";
    ?>

<script>
document.querySelectorAll('.pay').forEach(function(btn) {
    btn.onclick = function() {
        fetch(ajaxurl, {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'action=wcm_pay&order_id='+btn.dataset.id+'&nonce='+btn.dataset.nonce
        }).then(()=>location.reload());
    }
});

document.getElementById('markAllPaid').onclick = function() {
    if(!confirm('Segnare tutte come pagate?')) return;
    fetch(ajaxurl, {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'action=wcm_pay_all&nonce='+this.dataset.nonce
    }).then(()=>location.reload());
};
</script>

<?php

    // CSV Export
    if (isset($_GET['export']) && current_user_can('manage_woocommerce')) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=commissioni.csv');

        echo "Ordine,Cliente,Commissione,Stato\n";
        foreach ($orders as $o){
            $c = get_post_meta($o->get_id(), '_wm_commission', true);
            if (!$c) continue;
            $paid = get_post_meta($o->get_id(), '_wm_commission_paid', true);
            echo "{$o->get_id()},{$o->get_billing_email()},{$c},{$paid}\n";
        }
        exit;
    }
}}
