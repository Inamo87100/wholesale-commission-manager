<?php
add_action('init', function() {
    add_role(
        'affiliato',
        __('Affiliato', 'wholesale-commission-manager'),
        ['read' => true]
    );
});
