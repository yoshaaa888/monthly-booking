jQuery(document).ready(function($) {
    'use strict';
    
    const $form = $('#monthly-estimate-form');
    const $calculateBtn = $('#calculate-estimate');
    const $resultDiv = $('#estimate-result');
    const $loadingDiv = $('.estimate-loading');
    const $detailsDiv = $('.estimate-details');
    const $errorDiv = $('.estimate-error');
    
    function validateForm() {
        const plan = $('input[name="plan"]:checked').val();
        const moveInDate = $('#move_in_date').val();
        const stayMonths = $('#stay_months').val();
        const guestName = $('#guest_name').val().trim();
        const guestEmail = $('#guest_email').val().trim();
        
        if (!plan) {
            showError('Please select a plan.');
            return false;
        }
        
        if (!moveInDate) {
            showError('Please select a move-in date.');
            return false;
        }
        
        if (!stayMonths) {
            showError('Please select stay duration.');
            return false;
        }
        
        if (!guestName) {
            showError('Please enter your name.');
            return false;
        }
        
        if (!guestEmail) {
            showError('Please enter your email address.');
            return false;
        }
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(guestEmail)) {
            showError('Please enter a valid email address.');
            return false;
        }
        
        return true;
    }
    
    function showError(message) {
        $errorDiv.find('.error-message').text(message);
        $loadingDiv.hide();
        $detailsDiv.hide();
        $errorDiv.show();
        $resultDiv.show();
    }
    
    function formatCurrency(amount) {
        return '¥' + Math.round(amount).toLocaleString('ja-JP');
    }
    
    function displayResults(data) {
        const estimate = data.data;
        
        $('.plan-display').text(estimate.plan_name);
        $('.date-display').text(estimate.move_in_date);
        $('.duration-display').text(estimate.stay_months + ' months');
        
        $('.rent-amount').text(formatCurrency(estimate.total_rent));
        $('.utilities-amount').text(formatCurrency(estimate.total_utilities));
        $('.initial-costs-amount').text(formatCurrency(estimate.initial_costs));
        $('.subtotal-amount').text(formatCurrency(estimate.subtotal_with_tax));
        $('.total-amount').text(formatCurrency(estimate.final_total));
        
        if (estimate.campaign_discount > 0) {
            $('.campaign-discount').text('-' + formatCurrency(estimate.campaign_discount));
            $('.campaign-row').show();
            
            if (estimate.campaign_details && estimate.campaign_details.length > 0) {
                let campaignHtml = '';
                estimate.campaign_details.forEach(function(campaign) {
                    campaignHtml += '<div class="campaign-item">';
                    campaignHtml += '<strong>' + campaign.name + '</strong>';
                    if (campaign.description) {
                        campaignHtml += '<br><small>' + campaign.description + '</small>';
                    }
                    campaignHtml += '<br><span class="discount-amount">-' + formatCurrency(campaign.discount_amount) + '</span>';
                    campaignHtml += '</div>';
                });
                $('.campaign-list').html(campaignHtml);
                $('.campaign-details').show();
            }
        } else {
            $('.campaign-row').hide();
            $('.campaign-details').hide();
        }
        
        $loadingDiv.hide();
        $errorDiv.hide();
        $detailsDiv.show();
        $resultDiv.show();
    }
    
    function calculateEstimate() {
        if (!validateForm()) {
            return;
        }
        
        const formData = {
            action: 'calculate_estimate',
            nonce: monthlyBookingAjax.nonce,
            plan: $('input[name="plan"]:checked').val(),
            move_in_date: $('#move_in_date').val(),
            stay_months: $('#stay_months').val(),
            guest_name: $('#guest_name').val().trim(),
            company_name: $('#company_name').val().trim(),
            guest_email: $('#guest_email').val().trim()
        };
        
        $detailsDiv.hide();
        $errorDiv.hide();
        $loadingDiv.show();
        $resultDiv.show();
        
        $calculateBtn.prop('disabled', true).text('Calculating...');
        
        $.ajax({
            url: monthlyBookingAjax.ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    displayResults(response);
                } else {
                    showError(response.data || 'An error occurred while calculating the estimate.');
                }
            },
            error: function() {
                showError('Network error. Please try again.');
            },
            complete: function() {
                $calculateBtn.prop('disabled', false).text('Calculate Estimate');
            }
        });
    }
    
    $calculateBtn.on('click', function(e) {
        e.preventDefault();
        calculateEstimate();
    });
    
    $form.on('change', 'input, select', function() {
        if ($resultDiv.is(':visible')) {
            setTimeout(calculateEstimate, 500);
        }
    });
});
