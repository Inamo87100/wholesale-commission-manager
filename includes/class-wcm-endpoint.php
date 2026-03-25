<?php
// 1. Aggiungi endpoint personalizzato "commissioni"
add_action('init', function() {
    add_rewrite_endpoint('commissioni', EP_ROOT | EP_PAGES);
});

// 2. Aggiungi "Commissioni" nel menu My Account, subito dopo "Ordini"
add_filter('woocommerce_account_menu_items', function($items) {
    $allowed = ['administrator', 'partner', 'affiliato'];
    if (!wcm_user_has_role($allowed))
        return $items;

    $new = [];
    foreach ($items as $key => $label) {
        $new[$key] = $label;
        if ($key === 'orders') {
            $new['commissioni'] = __('Commissioni','wholesale-commission-manager');
        }
    }
    return $new;
});

// 3. Utility per controllare i ruoli
if (!function_exists('wcm_user_has_role')) {
    function wcm_user_has_role($roles) {
        $user = wp_get_current_user();
        foreach((array)$roles as $role) {
            if (in_array($role, (array)$user->roles)) {
                return true;
            }
        }
        return false;
    }
}

// 4. Mostra contenuto endpoint "commissioni" solo per utenti autorizzati
add_action('woocommerce_account_commissioni_endpoint', function() {
    $allowed = ['administrator', 'partner', 'affiliato'];
    if (!wcm_user_has_role($allowed)) {
        echo '<p>' . __('Non sei autorizzato a visualizzare questa pagina.', 'wholesale-commission-manager') . '</p>';
        return;
    }
    echo '<h2>' . __('Le tue commissioni', 'wholesale-commission-manager') . '</h2>';

    // Replica la logica dello shortcode wm_commissioni_advanced per elaborare i dati
    $user_id = get_current_user_id();
    $month = isset($_GET['mese']) ? intval($_GET['mese']) : date('m');
    $year  = isset($_GET['anno']) ? intval($_GET['anno']) : date('Y');
    $orders = wc_get_orders([
        'customer_id' => $user_id,
        'status' => 'completed',
        'limit' => -1
    ]);
    $rows = [];
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
    }
    // Stampa una tabella HTML semplice
    if(empty($rows)) {
        echo '<p>' . __('Nessuna commissione trovata nel mese corrente.', 'wholesale-commission-manager') . '</p>';
    } else {
        echo '<table class="shop_table shop_table_responsive">';
        echo '<thead><tr><th>Ordine</th><th>Data</th><th>Commissione</th><th>Stato</th></tr></thead><tbody>';
        foreach($rows as $row) {
            echo '<tr>
                <td>#' . intval($row['order_id']) . '</td>
                <td>' . esc_html($row['date']) . '</td>
                <td>€ ' . number_format($row['commission'],2) . '</td>
                <td>' . esc_html($row['status']) . '</td>
                </tr>';
        }
        echo '</tbody></table>';
    }
});

// 5. Flush rewrite rules all'attivazione/disattivazione del plugin (importante!)
register_activation_hook(__FILE__, function() {
    add_rewrite_endpoint('commissioni', EP_ROOT | EP_PAGES);
    flush_rewrite_rules();
});
register_deactivation_hook(__FILE__, function(){
    flush_rewrite_rules();
});
