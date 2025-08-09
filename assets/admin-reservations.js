jQuery(document).ready(function($) {
    'use strict';
    
    $('.delete-reservation').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm(monthlyBookingReservations.strings.confirmDelete)) {
            return;
        }
        
        const reservationId = $(this).data('id');
        const row = $(this).closest('tr');
        
        $.ajax({
            url: monthlyBookingReservations.ajaxurl,
            type: 'POST',
            data: {
                action: 'mbp_reservation_delete',
                reservation_id: reservationId,
                _ajax_nonce: monthlyBookingReservations.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (typeof window.MonthlyBookingCalendar !== 'undefined' && window.MonthlyBookingCalendar.refresh) {
                        window.MonthlyBookingCalendar.refresh();
                    }
                    
                    row.fadeOut(function() {
                        $(this).remove();
                        
                        if ($('tbody tr').length === 0) {
                            location.reload();
                        }
                    });
                    
                    showNotice('success', response.data.message || monthlyBookingReservations.strings.deleteSuccess);
                } else {
                    showNotice('error', response.data || monthlyBookingReservations.strings.deleteError);
                }
            },
            error: function() {
                showNotice('error', monthlyBookingReservations.strings.deleteError);
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
