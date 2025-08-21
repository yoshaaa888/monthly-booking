jQuery(document).ready(function($) {
    'use strict';
    
    $('#reservation-form').on('submit', function(e) {
        e.preventDefault();
        
        clearErrors();
        
        const formData = new FormData(this);
        const reservationId = formData.get('reservation_id');
        const action = reservationId ? 'mbp_reservation_update' : 'mbp_reservation_create';
        
        formData.append('action', action);
        formData.append('_ajax_nonce', monthlyBookingAdmin.reservationsNonce);
        
        const submitButton = $(this).find('input[type="submit"]');
        const originalText = submitButton.val();
        submitButton.val(monthlyBookingAdmin.strings.saving).prop('disabled', true);
        
        $.ajax({
            url: monthlyBookingAdmin.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    if (window.MonthlyBookingCalendar) {
                        window.MonthlyBookingCalendar.refresh();
                    }
                    
                    showNotice('success', response.data.message || monthlyBookingAdmin.strings.saveSuccess);
                    
                    setTimeout(function() {
                        window.location.href = 'admin.php?page=monthly-room-booking-registration';
                    }, 1500);
                } else {
                    handleValidationErrors(response.data);
                    showNotice('error', response.data);
                }
            },
            error: function() {
                showNotice('error', monthlyBookingAdmin.strings.saveError);
            },
            complete: function() {
                submitButton.val(originalText).prop('disabled', false);
            }
        });
    });
    
    function clearErrors() {
        $('.error-message').empty();
        $('input, select, textarea').removeClass('error').removeAttr('aria-invalid');
    }
    
    function handleValidationErrors(message) {
        if (typeof message === 'string') {
            if (message.includes('部屋を選択')) {
                showFieldError('room_id', message);
            } else if (message.includes('ゲスト名')) {
                showFieldError('guest_name', message);
            } else if (message.includes('メールアドレス')) {
                showFieldError('guest_email', message);
            } else if (message.includes('チェックイン') || message.includes('チェックアウト')) {
                showFieldError('start_date', message);
                showFieldError('end_date', message);
            }
        }
    }
    
    function showFieldError(fieldId, message) {
        const field = $('#' + fieldId);
        const errorDiv = $('#' + fieldId + '_error');
        
        field.addClass('error').attr('aria-invalid', 'true');
        errorDiv.text(message);
    }
    
    function showNotice(type, message) {
        const notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap h1').after(notice);
        
        setTimeout(function() {
            notice.fadeOut();
        }, 5000);
    }
    
    $('#start_date, #end_date').on('change', function() {
        const checkinDate = new Date($('#start_date').val());
        const checkoutDate = new Date($('#end_date').val());
        
        if (checkinDate && checkoutDate && checkinDate >= checkoutDate) {
            showFieldError('end_date', 'チェックアウト日はチェックイン日より後の日付を選択してください。');
        }
    });
    
    $('#recalculate_estimate').on('click', function() {
        const roomId = $('#room_id').val();
        const moveIn = $('#checkin_date').val();
        const moveOut = $('#checkout_date').val();
        const guestName = $('#guest_name').val().trim();
        const guestEmail = $('#guest_email').val().trim();
        const $out = $('#estimate-result-admin');
        
        if (!roomId || !moveIn || !moveOut) {
            showNotice('error', '部屋・入居日・退去日を入力してください。');
            return;
        }
        if (!guestName || !guestEmail) {
            showNotice('error', 'ゲスト名とメールアドレスを入力してください。');
            return;
        }
        
        const payload = {
            action: 'calculate_estimate',
            nonce: monthlyBookingAjax.nonce,
            room_id: roomId,
            move_in_date: moveIn,
            move_out_date: moveOut,
            num_adults: 1,
            num_children: 0,
            selected_options: {},
            guest_name: guestName,
            company_name: '',
            guest_email: guestEmail,
            guest_phone: '',
            special_requests: ''
        };
        
        $out.html('計算中...').show();
        $.ajax({
            url: monthlyBookingAjax.ajaxurl,
            type: 'POST',
            data: payload,
            success: function(resp) {
                if (!resp || !resp.success) {
                    $out.html('<div class="notice notice-error"><p>' + (resp && resp.data ? resp.data : '計算に失敗しました') + '</p></div>');
                    return;
                }
                const e = resp.data;
                let html = '';
                html += '<div class="notice notice-info"><p>見積結果（簡易プレビュー）</p></div>';
                html += '<table class="widefat" style="max-width:600px">';
                html += '<tr><th>プラン</th><td>' + (e.plan_name || '') + '</td></tr>';
                html += '<tr><th>滞在日数</th><td>' + (e.stay_days || '') + '日</td></tr>';
                html += '<tr><th>日割賃料</th><td>¥' + Math.round(e.total_rent || 0).toLocaleString('ja-JP') + '</td></tr>';
                html += '<tr><th>共益費</th><td>¥' + Math.round(e.total_utilities || 0).toLocaleString('ja-JP') + '</td></tr>';
                html += '<tr><th>初期費用</th><td>¥' + Math.round(e.initial_costs || 0).toLocaleString('ja-JP') + '</td></tr>';
                if (e.campaign_discount && e.campaign_discount > 0) {
                    html += '<tr><th>キャンペーン割引</th><td>-¥' + Math.round(e.campaign_discount).toLocaleString('ja-JP') + '</td></tr>';
                }
                html += '<tr><th>合計</th><td><strong>¥' + Math.round(e.final_total || 0).toLocaleString('ja-JP') + '</strong></td></tr>';
                html += '</table>';
                $out.html(html);
            },
            error: function() {
                $out.html('<div class="notice notice-error"><p>通信エラーが発生しました。</p></div>');
            }
        });
    });

});
