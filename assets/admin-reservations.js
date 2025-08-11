jQuery(document).ready(function($) {
    'use strict';
    
    $('.mbp-reservation-delete').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm(monthlyBookingAdmin.strings.confirmDelete)) {
            return;
        }
        
        const reservationId = $(this).data('id');
        const row = $(this).closest('tr');
        
        $.ajax({
            url: monthlyBookingAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'mbp_reservation_delete',
                reservation_id: reservationId,
                _ajax_nonce: monthlyBookingAdmin.reservationsNonce
            },
            success: function(response) {
                if (response.success) {
                    if (window.MonthlyBookingCalendar) {
                        window.MonthlyBookingCalendar.refresh();
                    }
                    
                    row.fadeOut(function() {
                        $(this).remove();
                        
                        if ($('tbody tr').length === 0) {
                            location.reload();
                        }
                    });
                    
                    showNotice('success', response.data.message || monthlyBookingAdmin.strings.deleteSuccess);
                } else {
                    showNotice('error', response.data || monthlyBookingAdmin.strings.deleteError);
                }
            },
            error: function() {
                showNotice('error', monthlyBookingAdmin.strings.deleteError);
            }
        });
    });
    
    function showNotice(type, message) {
        const notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap h1').after(notice);
        
        setTimeout(function() {
            notice.fadeOut();
        }, 5000);
    }
});
