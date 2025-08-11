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
                showFieldError('checkin_date', message);
                showFieldError('checkout_date', message);
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
    
    $('#checkin_date, #checkout_date').on('change', function() {
        const checkinDate = new Date($('#checkin_date').val());
        const checkoutDate = new Date($('#checkout_date').val());
        
        if (checkinDate && checkoutDate && checkinDate >= checkoutDate) {
            showFieldError('checkout_date', 'チェックアウト日はチェックイン日より後の日付を選択してください。');
        }
    });
});
