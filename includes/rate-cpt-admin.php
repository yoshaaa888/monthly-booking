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

add_action('add_meta_boxes', function () {
    add_meta_box('mrb_rate_details', __('料金詳細', 'monthly-booking'), function ($post) {
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
    }, 'mrb_rate', 'normal', 'default');
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
    $new_cols = array();
    $new_cols['cb'] = isset($cols['cb']) ? $cols['cb'] : '<input type="checkbox" />';
    $new_cols['title'] = __('Title');
    $new_cols['mrb_room_id'] = __('部屋', 'monthly-booking');
    $new_cols['mrb_price_yen'] = __('価格', 'monthly-booking');
    $new_cols['mrb_period'] = __('期間', 'monthly-booking');
    $new_cols['mrb_active'] = __('有効', 'monthly-booking');
    $new_cols['date'] = isset($cols['date']) ? $cols['date'] : __('Date');
    return $new_cols;
}, 20);

add_action('manage_mrb_rate_posts_custom_column', function ($col, $post_id) {
    if ($col === 'mrb_room_id') {
        echo (int) get_post_meta($post_id, 'mrb_room_id', true);
        return;
    }
    if ($col === 'mrb_price_yen') {
        echo '¥' . number_format((int) get_post_meta($post_id, 'mrb_price_yen', true));
        return;
    }
    if ($col === 'mrb_period') {
        $from = (string) get_post_meta($post_id, 'mrb_valid_from', true);
        $to = (string) get_post_meta($post_id, 'mrb_valid_to', true);
        echo esc_html($from . ' 〜 ' . ($to !== '' ? $to : '-'));
        return;
    }
    if ($col === 'mrb_active') {
        echo get_post_meta($post_id, 'mrb_is_active', true) ? '✓' : '';
        return;
    }
}, 10, 2);
