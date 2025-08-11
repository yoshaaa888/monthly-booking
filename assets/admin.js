jQuery(document).ready(function($) {
    'use strict';
    
    
    $('.toggle-campaign-status').on('click', function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const campaignId = $button.data('campaign-id');
        const isActive = $button.data('is-active');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'toggle_campaign',
                campaign_id: campaignId,
                is_active: isActive ? 0 : 1,
                nonce: monthlyBookingAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Network error occurred.');
            }
        });
    });
    function renderMessage(type, text) {
        const $box = $('#mbp-reservation-message');
        if (!$box.length) return;
        $box.removeClass('notice-success notice-error').addClass(type === 'error' ? 'notice notice-error' : 'notice notice-success');
        $box.text(text);
    }

    function setFieldInvalid($field, invalid, msg) {
        $field.attr('aria-invalid', invalid ? 'true' : 'false');
        if (invalid) {
            $field.addClass('error');
        } else {
            $field.removeClass('error');
        }
        if (msg) renderMessage('error', msg);
    }

    function validateReservationForm($form) {
        let ok = true;
        let firstInvalid = null;

        const $room = $form.find('#mbp-room-id');
        const $start = $form.find('#mbp-start-date');
        const $end = $form.find('#mbp-end-date');
        const $name = $form.find('#mbp-guest-name');
        const $email = $form.find('#mbp-guest-email');

        [$room, $start, $end, $name, $email].forEach($f => setFieldInvalid($f, false));

        if (!$room.val()) { setFieldInvalid($room, true); ok = false; firstInvalid = firstInvalid || $room; }
        if (!$start.val()) { setFieldInvalid($start, true); ok = false; firstInvalid = firstInvalid || $start; }
        if (!$end.val()) { setFieldInvalid($end, true); ok = false; firstInvalid = firstInvalid || $end; }
        if ($start.val() && $end.val()) {
            const s = new Date($start.val()); const e = new Date($end.val());
            if (!(e > s)) { setFieldInvalid($end, true, '終了日は開始日より後にしてください。'); ok = false; firstInvalid = firstInvalid || $end; }
        }
        if (!$name.val().trim()) { setFieldInvalid($name, true); ok = false; firstInvalid = firstInvalid || $name; }
        const emailVal = $email.val().trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailVal || !emailRegex.test(emailVal)) { setFieldInvalid($email, true); ok = false; firstInvalid = firstInvalid || $email; }

        if (!ok && firstInvalid) firstInvalid.trigger('focus');
        return ok;
    }

    function refreshReservations() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'mbp_reservation_list',
                nonce: monthlyBookingAdmin.nonce
            }
        }).done(function(resp) {
            if (resp && resp.success && resp.data && typeof resp.data.html === 'string') {
                $('#mbp-reservations-body').html(resp.data.html);
            }
        });
    }

    function resetReservationForm() {
        const $form = $('#mbp-reservation-form');
        $form[0].reset();
        $('#mbp-reservation-id').val('');
        $('#mbp-submit').text('登録');
        renderMessage('success', '');
        $form.find('[aria-invalid="true"]').attr('aria-invalid', 'false').removeClass('error');
    }

    $(document).on('click', '#mbp-reset', function(e) {
        e.preventDefault();
        resetReservationForm();
    });

    $(document).on('submit', '#mbp-reservation-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        if (!validateReservationForm($form)) return;

        const id = $('#mbp-reservation-id').val();
        const action = id ? 'mbp_reservation_update' : 'mbp_reservation_create';
        const payload = {
            action: action,
            nonce: monthlyBookingAdmin.nonce,
            reservation_id: id || ''
        };

        ['room_id','start_date','end_date','guest_name','guest_email','guest_phone','status','notes'].forEach(name => {
            const v = $form.find('[name="'+name+'"]').val();
            if (typeof v !== 'undefined') payload[name] = v;
        });

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: payload
        }).done(function(resp) {
            if (resp && resp.success) {
                renderMessage('success', id ? '予約を更新しました。' : '予約を登録しました。');
                if (!id) resetReservationForm();
                refreshReservations();
            } else {
                const msg = (resp && resp.data) ? resp.data : 'エラーが発生しました。';
                renderMessage('error', msg);
            }
        }).fail(function(xhr) {
            if (xhr && xhr.status === 409) {
                renderMessage('error', '別の予約と重複しています。');
                $('#mbp-start-date, #mbp-end-date').attr('aria-invalid', 'true').addClass('error');
                $('#mbp-start-date').trigger('focus');
            } else {
                renderMessage('error', 'ネットワークエラーが発生しました。');
            }
        });
    });

    $(document).on('click', '.mbp-reservation-edit', function(e) {
        e.preventDefault();
        const $btn = $(this);
        $('#mbp-reservation-id').val($btn.data('id'));
        $('#mbp-room-id').val($btn.data('room-id'));
        $('#mbp-start-date').val($btn.data('start'));
        $('#mbp-end-date').val($btn.data('end'));
        $('#mbp-guest-name').val($btn.data('guest-name'));
        $('#mbp-guest-email').val($btn.data('guest-email'));
        $('#mbp-guest-phone').val($btn.data('guest-phone') || '');
        $('#mbp-status').val($btn.data('status'));
        $('#mbp-notes').val($btn.data('notes') || '');
        $('#mbp-submit').text('更新');
        renderMessage('success', '編集モードに切り替えました。');
        $('#mbp-room-id').trigger('focus');
    });

    $(document).on('click', '.mbp-reservation-delete', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        if (!id) return;
        if (!window.confirm('この予約を削除しますか？')) return;

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'mbp_reservation_delete',
                nonce: monthlyBookingAdmin.nonce,
                reservation_id: id
            }
        }).done(function(resp) {
            if (resp && resp.success) {
                renderMessage('success', '予約を削除しました。');
                refreshReservations();
                const currentId = $('#mbp-reservation-id').val();
                if (currentId && parseInt(currentId, 10) === parseInt(id, 10)) {
                    resetReservationForm();
                }
            } else {
                renderMessage('error', '削除に失敗しました。');
            }
        }).fail(function() {
            renderMessage('error', 'ネットワークエラーが発生しました。');
        });
    });
    
    $('.monthly-booking-form').on('submit', function(e) {
        const $form = $(this);
        let isValid = true;
        
        $form.find('input[required], select[required], textarea[required]').each(function() {
            if (!$(this).val().trim()) {
                $(this).addClass('error');
                isValid = false;
            } else {
                $(this).removeClass('error');
            }
        });
        
        $form.find('input[type="email"]').each(function() {
            const email = $(this).val().trim();
            if (email && !isValidEmail(email)) {
                $(this).addClass('error');
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields correctly.');
        }
    });
    
    if ($.fn.datepicker) {
        $('.date-picker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
    }
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    $('<style>')
        .prop('type', 'text/css')
        .html('.error { border-color: #dc3232 !important; box-shadow: 0 0 2px rgba(220, 50, 50, 0.8); }')
        .appendTo('head');
});
