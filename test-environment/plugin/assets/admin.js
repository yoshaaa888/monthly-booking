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
    
    $('#room_select').on('change', function() {
        const roomId = $(this).val();
        const baseUrl = window.location.origin + window.location.pathname;
        const newUrl = baseUrl + '?page=monthly-room-booking-calendar&room_id=' + roomId;
        
        console.log('Room dropdown changed:', roomId);
        console.log('Redirecting to:', newUrl);
        
        if (roomId && roomId !== '0') {
            window.location.href = newUrl;
        }
    });
    
    console.log('Monthly Booking admin scripts loaded');
});
