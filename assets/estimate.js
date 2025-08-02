jQuery(document).ready(function($) {
    'use strict';
    
    const $form = $('#monthly-estimate-form');
    const $calculateBtn = $('#calculate-estimate');
    const $resultDiv = $('#estimate-result');
    const $loadingDiv = $('.estimate-loading');
    const $detailsDiv = $('.estimate-details');
    const $errorDiv = $('.estimate-error');
    
    let availableOptions = [];
    
    loadSearchFilters();
    
    function loadOptions() {
        if (typeof monthlyBookingAjax === 'undefined') {
            $('.options-loading').text('オプションの読み込みに失敗しました');
            return;
        }
        
        $.ajax({
            url: monthlyBookingAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_booking_options',
                nonce: monthlyBookingAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    availableOptions = response.data;
                    renderOptions();
                } else {
                    $('.options-loading').text('オプションの読み込みに失敗しました');
                }
            },
            error: function() {
                $('.options-loading').text('オプションの読み込みに失敗しました');
            }
        });
    }
    
    function renderOptions() {
        const $grid = $('#options-grid');
        $grid.empty();
        
        if (availableOptions.length === 0) {
            $grid.html('<div class="options-loading">利用可能なオプションがありません</div>');
            return;
        }
        
        availableOptions.forEach(function(option) {
            const isDiscountEligible = parseInt(option.is_discount_target) === 1;
            const $optionItem = $(`
                <div class="option-item ${isDiscountEligible ? 'discount-eligible' : ''}">
                    <input type="checkbox" id="option_${option.id}" name="options[]" value="${option.id}">
                    <label for="option_${option.id}" class="option-label">
                        <span class="option-name">${option.option_name}</span>
                        <span class="option-price">¥${parseInt(option.price).toLocaleString('ja-JP')}</span>
                        ${isDiscountEligible ? '<small style="color: #007cba;">割引対象</small>' : '<small style="color: #666;">割引対象外</small>'}
                    </label>
                </div>
            `);
            $grid.append($optionItem);
        });
        
        $grid.find('input[type="checkbox"]').on('change', function() {
            updateOptionsDiscount();
            if ($resultDiv.is(':visible')) {
                setTimeout(calculateEstimate, 300);
            }
        });
    }
    
    function updateOptionsDiscount() {
        const selectedOptions = getSelectedOptions();
        let discountEligibleCount = 0;
        
        selectedOptions.forEach(function(optionId) {
            const option = availableOptions.find(opt => opt.id == optionId);
            if (option && parseInt(option.is_discount_target) === 1) {
                discountEligibleCount++;
            }
        });
        
        const discount = calculateOptionDiscount(discountEligibleCount);
        const $discountDisplay = $('#options-discount-display');
        
        if (discount > 0) {
            $('#discount-amount').text('¥' + discount.toLocaleString('ja-JP'));
            $discountDisplay.show();
        } else {
            $discountDisplay.hide();
        }
    }
    
    function calculateOptionDiscount(count) {
        if (count < 2) return 0;
        
        let discount = 0;
        if (count >= 2) {
            discount += 500;
        }
        if (count >= 3) {
            discount += (count - 2) * 300;
        }
        return Math.min(discount, 2000);
    }
    
    function getSelectedOptions() {
        const selected = [];
        $('#options-grid input[type="checkbox"]:checked').each(function() {
            selected.push($(this).val());
        });
        return selected;
    }
    
    function validateForm() {
        const plan = $('input[name="plan"]:checked').val();
        const moveInDate = $('#move_in_date').val();
        const stayMonths = $('#stay_months').val();
        const numAdults = $('#num_adults').val();
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
        
        if (!numAdults || numAdults < 1) {
            showError('大人の人数を選択してください。');
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
        
        if (estimate.person_additional_fee > 0) {
            $('.person-fee-amount').text(formatCurrency(estimate.person_additional_fee));
            $('.person-fee-row').show();
        } else {
            $('.person-fee-row').hide();
        }
        
        if (estimate.options_total > 0) {
            $('.options-amount').text(formatCurrency(estimate.options_total));
            $('.options-row').show();
            
            if (estimate.options_discount > 0) {
                $('.options-discount-amount').text('-' + formatCurrency(estimate.options_discount));
                $('.options-discount-row').show();
            } else {
                $('.options-discount-row').hide();
            }
            
            if (estimate.selected_options && estimate.selected_options.length > 0) {
                let optionsHtml = '';
                estimate.selected_options.forEach(function(option) {
                    optionsHtml += '<div class="option-detail-item">';
                    optionsHtml += '<span>' + option.name + '</span>';
                    optionsHtml += '<span>' + formatCurrency(option.total) + '</span>';
                    optionsHtml += '</div>';
                });
                $('.options-list').html(optionsHtml);
                $('.options-details').show();
            } else {
                $('.options-details').hide();
            }
        } else {
            $('.options-row').hide();
            $('.options-discount-row').hide();
            $('.options-details').hide();
        }
        
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
        
        const selectedOptions = {};
        getSelectedOptions().forEach(function(optionId) {
            selectedOptions[optionId] = 1;
        });
        
        const formData = {
            action: 'calculate_estimate',
            nonce: monthlyBookingAjax.nonce,
            plan: $('input[name="plan"]:checked').val(),
            move_in_date: $('#move_in_date').val(),
            stay_months: $('#stay_months').val(),
            num_adults: $('#num_adults').val(),
            num_children: $('#num_children').val(),
            selected_options: selectedOptions,
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
        if ($(this).attr('name') === 'options[]') {
            updateOptionsDiscount();
        }
        if ($resultDiv.is(':visible')) {
            setTimeout(calculateEstimate, 500);
        }
    });
    
    $('#num_adults, #num_children').on('change', function() {
        if ($resultDiv.is(':visible')) {
            setTimeout(calculateEstimate, 300);
        }
    });
    
    function loadSearchFilters() {
        $.ajax({
            url: monthlyBookingAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_search_filters',
                nonce: monthlyBookingAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const filters = response.data;
                    
                    const stationSelect = $('#station_filter');
                    stationSelect.empty().append('<option value="">' + monthlyBookingAjax.selectStation + '</option>');
                    filters.stations.forEach(function(station) {
                        stationSelect.append('<option value="' + station + '">' + station + '</option>');
                    });
                    
                    const structureSelect = $('#structure_filter');
                    structureSelect.empty().append('<option value="">' + monthlyBookingAjax.selectStructure + '</option>');
                    filters.structures.forEach(function(structure) {
                        structureSelect.append('<option value="' + structure + '">' + structure + '</option>');
                    });
                    
                    const occupancySelect = $('#occupancy_filter');
                    occupancySelect.empty().append('<option value="">' + monthlyBookingAjax.maxOccupants + '</option>');
                    filters.occupancy_options.forEach(function(num) {
                        occupancySelect.append('<option value="' + num + '">' + num + '人</option>');
                    });
                }
            },
            error: function() {
                console.error('Failed to load search filters');
            }
        });
    }
    
    function searchProperties() {
        const moveInDate = $('#move_in_date').val();
        const stayMonths = $('#stay_months').val();
        
        if (!moveInDate || !stayMonths) {
            alert(monthlyBookingAjax.selectDatesFirst);
            return;
        }
        
        const endDate = new Date(moveInDate);
        endDate.setMonth(endDate.getMonth() + parseInt(stayMonths));
        
        $.ajax({
            url: monthlyBookingAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'search_properties',
                nonce: monthlyBookingAjax.nonce,
                start_date: moveInDate,
                end_date: endDate.toISOString().split('T')[0],
                station: $('#station_filter').val(),
                max_occupants: $('#occupancy_filter').val(),
                structure: $('#structure_filter').val()
            },
            success: function(response) {
                if (response.success) {
                    displayPropertyResults(response.data);
                } else {
                    $('#property_results').html('<p class="error">' + response.data + '</p>');
                }
            },
            error: function() {
                $('#property_results').html('<p class="error">' + monthlyBookingAjax.searchError + '</p>');
            }
        });
    }
    
    function displayPropertyResults(properties) {
        const resultsDiv = $('#property_results');
        
        if (properties.length === 0) {
            resultsDiv.html('<p>' + monthlyBookingAjax.noPropertiesFound + '</p>');
            return;
        }
        
        let html = '<div class="property-list"><h4>' + monthlyBookingAjax.availableProperties + '</h4>';
        
        properties.forEach(function(property) {
            html += '<div class="property-item">';
            html += '<h5>' + property.display_name + '</h5>';
            html += '<p><strong>' + monthlyBookingAjax.room + ':</strong> ' + property.room_name + '</p>';
            html += '<p><strong>' + monthlyBookingAjax.dailyRent + ':</strong> ¥' + parseInt(property.daily_rent).toLocaleString() + '</p>';
            html += '<p><strong>' + monthlyBookingAjax.maxOccupants + ':</strong> ' + property.max_occupants + '人</p>';
            
            if (property.access_info1) {
                html += '<p><strong>' + monthlyBookingAjax.access + ':</strong> ' + property.access_info1;
                if (property.access_info2) html += ', ' + property.access_info2;
                if (property.access_info3) html += ', ' + property.access_info3;
                html += '</p>';
            }
            
            if (property.room_amenities) {
                html += '<p><strong>' + monthlyBookingAjax.amenities + ':</strong> ' + property.room_amenities + '</p>';
            }
            
            html += '</div>';
        });
        
        html += '</div>';
        resultsDiv.html(html);
    }
    
    $('#search_properties').on('click', function() {
        searchProperties();
    });

    loadOptions();
});
