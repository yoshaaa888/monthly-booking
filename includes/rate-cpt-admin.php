<?php
if (!defined('ABSPATH')) {
    exit;
}
add_action('init', function () {
    register_post_meta('mrb_rate', 'mrb_room_id', array('type' => 'integer', 'single' => true, 'show_in_rest' => false, 'auth_callback' => '__return_true'));
    register_post_meta('mrb_rate', 'mrb_rate_type', array('type' => 'string', 'single' => true, 'show_in_rest' => false, 'auth_callback' => '__return_true'));
    register_post_meta('mrb_rate', 'mrb_price_yen', array('type' => 'integer', 'single' => true, 'show_in_rest' => false, 'auth_callback' => '__return_true'));
    register_post_meta('mrb_rate', 'mrb_valid_from', array('type' => 'string', 'single' => true, 'show_in_rest' => false, 'auth_callback' => '__return_true'));
    register_post_meta('mrb_rate', 'mrb_valid_to', array('type' => 'string', 'single' => true, 'show_in_rest' => false, 'auth_callback' => '__return_true'));
    register_post_meta('mrb_rate', 'mrb_is_active', array('type' => 'boolean', 'single' => true, 'show_in_rest' => false, 'auth_callback' => '__return_true'));

});



function mrb_rate_render_details_box($post) {
    wp_nonce_field('mrb_rate_save', 'mrb_rate_nonce');
    $room = (int) get_post_meta($post->ID, 'mrb_room_id', true);
    $type = (string) get_post_meta($post->ID, 'mrb_rate_type', true);
    $price = (int) get_post_meta($post->ID, 'mrb_price_yen', true);
    $from = (string) get_post_meta($post->ID, 'mrb_valid_from', true);
    $to = (string) get_post_meta($post->ID, 'mrb_valid_to', true);
    $active = (int) get_post_meta($post->ID, 'mrb_is_active', true);
    ?>
    <table class="form-table">
        <tr>
            <th><?php _e('部屋ID', 'monthly-booking'); ?></th>
            <td><input type="number" name="mrb_room_id" value="<?php echo esc_attr($room); ?>" required></td>
        </tr>
        <tr>
            <th><?php _e('rate_type', 'monthly-booking'); ?></th>
            <td><input type="text" name="mrb_rate_type" value="<?php echo esc_attr($type); ?>" required></td>
        </tr>
        <tr>
            <th><?php _e('価格(円)', 'monthly-booking'); ?></th>
            <td><input type="number" name="mrb_price_yen" value="<?php echo esc_attr($price); ?>" required></td>
        </tr>
        <tr>
            <th><?php _e('開始日', 'monthly-booking'); ?></th>
            <td><input type="date" name="mrb_valid_from" value="<?php echo esc_attr($from); ?>" required></td>
        </tr>
        <tr>
            <th><?php _e('終了日', 'monthly-booking'); ?></th>
            <td><input type="date" name="mrb_valid_to" value="<?php echo esc_attr($to); ?>"></td>
        </tr>
        <tr>
            <th><?php _e('有効', 'monthly-booking'); ?></th>
            <td><label><input type="checkbox" name="mrb_is_active" <?php checked($active, 1); ?>> <?php _e('有効', 'monthly-booking');?></label></td>
        </tr>
    </table>
    <?php
}
add_action('add_meta_boxes_mrb_rate', function () {
    add_meta_box('mrb_rate_details', __('料金詳細', 'monthly-booking'), 'mrb_rate_render_details_box', 'mrb_rate', 'normal', 'default');
});

add_action('save_post_mrb_rate', function ($post_id, $post, $update) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (!isset($_POST['mrb_rate_nonce']) || !wp_verify_nonce($_POST['mrb_rate_nonce'], 'mrb_rate_save')) return;

    $room = isset($_POST['mrb_room_id']) ? (int) $_POST['mrb_room_id'] : 0;
    $type = isset($_POST['mrb_rate_type']) ? sanitize_text_field($_POST['mrb_rate_type']) : '';
    $price = isset($_POST['mrb_price_yen']) ? (int) $_POST['mrb_price_yen'] : 0;
    $from = isset($_POST['mrb_valid_from']) ? sanitize_text_field($_POST['mrb_valid_from']) : '';
    $to = isset($_POST['mrb_valid_to']) ? trim((string) $_POST['mrb_valid_to']) : '';
    $active = isset($_POST['mrb_is_active']) ? 1 : 0;

    if ($room <= 0 || $type === '' || $price < 0 || $from === '') {
        return;
    }

    $infTo = $to === '' ? '9999-12-31' : $to;

    $q = new WP_Query(array(
        'post_type' => 'mrb_rate',
        'post_status' => array('publish', 'draft', 'pending'),
        'posts_per_page' => 100,
        'post__not_in' => array($post_id),
        'meta_query' => array(
            'relation' => 'AND',
            array('key' => 'mrb_room_id', 'value' => $room, 'compare' => '=', 'type' => 'NUMERIC'),
            array('key' => 'mrb_rate_type', 'value' => $type, 'compare' => '=', 'type' => 'CHAR'),
            array('key' => 'mrb_valid_from', 'value' => $infTo, 'compare' => '<', 'type' => 'CHAR'),
        ),
        'fields' => 'ids',
    ));

    $overlap = false;
    foreach ($q->posts as $pid) {
        $oFrom = get_post_meta($pid, 'mrb_valid_from', true);
        if ($oFrom === '') $oFrom = '0000-01-01';
        $oTo = get_post_meta($pid, 'mrb_valid_to', true);
        if ($oTo === '') $oTo = '9999-12-31';
        if ($from < $oTo && $oFrom < $infTo) {
            $overlap = true;
            break;
        }
    }

    if ($overlap) {
        update_post_meta($post_id, 'mrb_is_active', 0);
        add_filter('redirect_post_location', function ($loc) {
            return add_query_arg('mrb_rate_error', 'overlap', $loc);
        });
        return;
    }

    update_post_meta($post_id, 'mrb_room_id', $room);
    update_post_meta($post_id, 'mrb_rate_type', $type);
    update_post_meta($post_id, 'mrb_price_yen', $price);
    update_post_meta($post_id, 'mrb_valid_from', $from);
    update_post_meta($post_id, 'mrb_valid_to', $to);
    update_post_meta($post_id, 'mrb_is_active', $active);
}, 10, 3);

add_action('admin_notices', function () {
    if (isset($_GET['mrb_rate_error']) && $_GET['mrb_rate_error'] === 'overlap') {
        echo '<div class="notice notice-error"><p>' . esc_html__('同一部屋（期間）に重複する料金が存在します。', 'monthly-booking') . '</p></div>';
    }
});



add_filter('manage_edit-mrb_rate_columns', function ($cols) {
    $new = array();
    $new['cb'] = isset($cols['cb']) ? $cols['cb'] : '<input type="checkbox" />';
    $new['title'] = isset($cols['title']) ? $cols['title'] : __('Title');
    $new['mrb_room_id'] = __('部屋', 'monthly-booking');
    $new['mrb_price_yen'] = __('価格', 'monthly-booking');
    $new['mrb_period'] = __('期間', 'monthly-booking');
    $new['mrb_active'] = __('有効', 'monthly-booking');
    $new['date'] = isset($cols['date']) ? $cols['date'] : __('Date');
    return $new;
}, 20);

add_action('manage_mrb_rate_posts_custom_column', function ($col, $post_id) {
    if ($col === 'mrb_room_id') {
        echo (int) get_post_meta($post_id, 'mrb_room_id', true);
    } elseif ($col === 'mrb_price_yen') {
        echo '¥' . number_format((int) get_post_meta($post_id, 'mrb_price_yen', true));
    } elseif ($col === 'mrb_period') {
        $from = (string) get_post_meta($post_id, 'mrb_valid_from', true);
        $to = (string) get_post_meta($post_id, 'mrb_valid_to', true);
        echo esc_html($from . ' 〜 ' . ($to !== '' ? $to : '-'));
    } elseif ($col === 'mrb_active') {
        echo get_post_meta($post_id, 'mrb_is_active', true) ? '✓' : '';
    }
}, 10, 2);
add_action('restrict_manage_posts', function () {
    global $typenow;
    if ($typenow !== 'mrb_rate') return;
    $min_price = isset($_GET['mrb_min_price']) ? (int)$_GET['mrb_min_price'] : '';
    $max_price = isset($_GET['mrb_max_price']) ? (int)$_GET['mrb_max_price'] : '';
    $from_from = isset($_GET['mrb_from_from']) ? sanitize_text_field($_GET['mrb_from_from']) : '';
    $from_to   = isset($_GET['mrb_from_to']) ? sanitize_text_field($_GET['mrb_from_to']) : '';
    $room_id   = isset($_GET['mrb_room_id']) ? (int)$_GET['mrb_room_id'] : '';
    $active    = isset($_GET['mrb_active']) ? sanitize_text_field($_GET['mrb_active']) : '';
    $rate_type = isset($_GET['mrb_rate_type']) ? sanitize_text_field($_GET['mrb_rate_type']) : '';
    echo '<input type="number" placeholder="最小価格" name="mrb_min_price" value="' . esc_attr($min_price) . '" style="width:100px;margin-right:6px;" />';
    echo '<input type="number" placeholder="最大価格" name="mrb_max_price" value="' . esc_attr($max_price) . '" style="width:100px;margin-right:6px;" />';
    echo '<input type="date" placeholder="開始日(自)" name="mrb_from_from" value="' . esc_attr($from_from) . '" style="margin-right:6px;" />';
    echo '<input type="date" placeholder="開始日(至)" name="mrb_from_to" value="' . esc_attr($from_to) . '" style="margin-right:6px;" />';
    echo '<input type="number" placeholder="部屋ID" name="mrb_room_id" value="' . esc_attr($room_id) . '" style="width:100px;margin-right:6px;" />';
    echo '<select name="mrb_active" style="margin-right:6px;"><option value="">有効(すべて)</option><option value="1"' . selected($active, '1', false) . '>有効のみ</option><option value="0"' . selected($active, '0', false) . '>無効のみ</option></select>';
    echo '<input type="text" placeholder="rate_type" name="mrb_rate_type" value="' . esc_attr($rate_type) . '" style="width:120px;margin-right:6px;" />';
    $nonce = wp_create_nonce('mrb_rate_export');
    $export_url = add_query_arg(array_merge($_GET, array('action' => 'mrb_rate_export', '_wpnonce' => $nonce)), admin_url('admin-post.php'));
    echo '<a href="' . esc_url($export_url) . '" class="button">CSVエクスポート</a>';
});
add_action('pre_get_posts', function ($q) {
    if (!is_admin() || !$q->is_main_query()) return;
    if ($q->get('post_type') !== 'mrb_rate') return;
    $meta = array('relation' => 'AND');
    if (isset($_GET['mrb_min_price']) && $_GET['mrb_min_price'] !== '') {
        $meta[] = array('key' => 'mrb_price_yen', 'value' => (int)$_GET['mrb_min_price'], 'compare' => '>=', 'type' => 'NUMERIC');
    }
    if (isset($_GET['mrb_max_price']) && $_GET['mrb_max_price'] !== '') {
        $meta[] = array('key' => 'mrb_price_yen', 'value' => (int)$_GET['mrb_max_price'], 'compare' => '<=', 'type' => 'NUMERIC');
    }
    if (isset($_GET['mrb_rate_type']) && $_GET['mrb_rate_type'] !== '') {
        $meta[] = array('key' => 'mrb_rate_type', 'value' => sanitize_text_field($_GET['mrb_rate_type']), 'compare' => '=', 'type' => 'CHAR');
    }
    $qfrom = !empty($_GET['mrb_from_from']) ? sanitize_text_field($_GET['mrb_from_from']) : '';
    $qto   = !empty($_GET['mrb_from_to']) ? sanitize_text_field($_GET['mrb_from_to']) : '';
    if ($qfrom !== '' || $qto !== '') {
        $qfromDef = $qfrom !== '' ? $qfrom : '0000-01-01';
        $qtoDef   = $qto !== '' ? $qto : '9999-12-31';
        $meta[] = array('key' => 'mrb_valid_from', 'value' => $qtoDef, 'compare' => '<', 'type' => 'CHAR');
        $meta[] = array(
            'relation' => 'OR',
            array('key' => 'mrb_valid_to', 'value' => '', 'compare' => '='),
            array('key' => 'mrb_valid_to', 'value' => $qfromDef, 'compare' => '>', 'type' => 'CHAR'),
        );
    }
    if (isset($_GET['mrb_room_id']) && $_GET['mrb_room_id'] !== '') {
        $meta[] = array('key' => 'mrb_room_id', 'value' => (int)$_GET['mrb_room_id'], 'compare' => '=', 'type' => 'NUMERIC');
    }
    if (isset($_GET['mrb_active']) && ($_GET['mrb_active'] === '1' || $_GET['mrb_active'] === '0')) {
        $meta[] = array('key' => 'mrb_is_active', 'value' => (int)$_GET['mrb_active'], 'compare' => '=', 'type' => 'NUMERIC');
    }
    if (count($meta) > 1) {
        $q->set('meta_query', $meta);
    }
});
add_action('admin_post_mrb_rate_export', function () {
    if (!current_user_can('edit_posts')) wp_die('forbidden');
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'mrb_rate_export')) wp_die('invalid nonce');
    $args = array(
        'post_type' => 'mrb_rate',
        'post_status' => array('publish','draft','pending'),
        'posts_per_page' => -1,
        'fields' => 'ids',
    );
    $meta = array('relation' => 'AND');
    if (isset($_GET['mrb_min_price']) && $_GET['mrb_min_price'] !== '') {
        $meta[] = array('key' => 'mrb_price_yen', 'value' => (int)$_GET['mrb_min_price'], 'compare' => '>=', 'type' => 'NUMERIC');
    }
    if (isset($_GET['mrb_max_price']) && $_GET['mrb_max_price'] !== '') {
        $meta[] = array('key' => 'mrb_price_yen', 'value' => (int)$_GET['mrb_max_price'], 'compare' => '<=', 'type' => 'NUMERIC');
    }
    if (isset($_GET['mrb_rate_type']) && $_GET['mrb_rate_type'] !== '') {
        $meta[] = array('key' => 'mrb_rate_type', 'value' => sanitize_text_field($_GET['mrb_rate_type']), 'compare' => '=', 'type' => 'CHAR');
    }
    $qfrom = !empty($_GET['mrb_from_from']) ? sanitize_text_field($_GET['mrb_from_from']) : '';
    $qto   = !empty($_GET['mrb_from_to']) ? sanitize_text_field($_GET['mrb_from_to']) : '';
    if ($qfrom !== '' || $qto !== '') {
        $qfromDef = $qfrom !== '' ? $qfrom : '0000-01-01';
        $qtoDef   = $qto !== '' ? $qto : '9999-12-31';
        $meta[] = array('key' => 'mrb_valid_from', 'value' => $qtoDef, 'compare' => '<', 'type' => 'CHAR');
        $meta[] = array(
            'relation' => 'OR',
            array('key' => 'mrb_valid_to', 'value' => '', 'compare' => '='),
            array('key' => 'mrb_valid_to', 'value' => $qfromDef, 'compare' => '>', 'type' => 'CHAR'),
        );
    }
    if (isset($_GET['mrb_room_id']) && $_GET['mrb_room_id'] !== '') {
        $meta[] = array('key' => 'mrb_room_id', 'value' => (int)$_GET['mrb_room_id'], 'compare' => '=', 'type' => 'NUMERIC');
    }
    if (isset($_GET['mrb_active']) && ($_GET['mrb_active'] === '1' || $_GET['mrb_active'] === '0')) {
        $meta[] = array('key' => 'mrb_is_active', 'value' => (int)$_GET['mrb_active'], 'compare' => '=', 'type' => 'NUMERIC');
    }
    if (count($meta) > 1) $args['meta_query'] = $meta;
    $q = new WP_Query($args);
    nocache_headers();
    header('Content-Type: text/csv; charset=UTF-8');
    $fname = 'monthly-rates-' . gmdate('Ymd-Hi') . '.csv';
    header('Content-Disposition: attachment; filename="' . $fname . '"');
    $out = fopen('php://output', 'w');
    fputcsv($out, array('ID','Title','RoomID','RateType','PriceYen','ValidFrom','ValidTo','Active','Date'));
    foreach ($q->posts as $pid) {
        $title = get_the_title($pid);
        $room = (int)get_post_meta($pid,'mrb_room_id',true);
        $type = (string)get_post_meta($pid,'mrb_rate_type',true);
        $price = (int)get_post_meta($pid,'mrb_price_yen',true);
        $from = (string)get_post_meta($pid,'mrb_valid_from',true);
        $to   = (string)get_post_meta($pid,'mrb_valid_to',true);
        $active = get_post_meta($pid,'mrb_is_active',true) ? 1 : 0;
        $date = get_post_field('post_date', $pid);
        fputcsv($out, array($pid,$title,$room,$type,$price,$from,$to,$active,$date));
    }
    fclose($out);
    exit;
});
