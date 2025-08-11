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
        const roomId = $('#room_id').val();
        const moveInDate = $('#move_in_date').val();
        const moveOutDate = $('#move_out_date').val();
        const numAdults = $('#num_adults').val();
        const guestName = $('#guest_name').val().trim();
        const guestEmail = $('#guest_email').val().trim();
        
        if (!roomId) {
            showError('部屋を選択してください。');
            return false;
        }
        
        if (!moveInDate) {
            showError('入居日を選択してください。');
            return false;
        }
        
        if (!moveOutDate) {
            showError('退去日を選択してください。');
            return false;
        }
        
        if (moveInDate && moveOutDate && new Date(moveOutDate) <= new Date(moveInDate)) {
            showError('退去日は入居日より後の日付を選択してください。');
            return false;
        }
        
        
        if (!numAdults || numAdults < 1) {
            showError('大人の人数を選択してください。');
            return false;
        }
        
        if (!guestName) {
            showError('お名前を入力してください。');
            return false;
        }
        
        if (!guestEmail) {
            showError('メールアドレスを入力してください。');
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
        const $resultDiv = $('#estimate-result');
        
        let html = '<div class="estimate-summary">';
        html += '<h3>📊 見積結果</h3>';
        
        html += '<div class="estimate-section">';
        html += '<h4>📋 予約詳細</h4>';
        html += '<p><strong>プラン:</strong> ' + estimate.plan_name + '</p>';
        html += '<p><strong>入居日:</strong> ' + estimate.move_in_date + '</p>';
        html += '<p><strong>退去日:</strong> ' + estimate.move_out_date + '</p>';
        html += '<p><strong>滞在期間:</strong> ' + estimate.stay_days + '日間</p>';
        html += '<p><strong>利用人数:</strong> 大人' + estimate.num_adults + '名';
        if (estimate.num_children > 0) {
            html += ', 子ども' + estimate.num_children + '名';
        }
        html += '</p>';
        html += '</div>';
        
        html += '<div class="estimate-section">';
        html += '<h4>💰 料金内訳</h4>';
        
        if (estimate.original_daily_rent && estimate.original_daily_rent !== estimate.daily_rent) {
            html += '<div class="cost-item">';
            html += '<span>日割賃料（割引前） (' + formatCurrency(estimate.original_daily_rent) + '/日 × ' + estimate.stay_days + '日)</span>';
            html += '<span>' + formatCurrency(estimate.original_daily_rent * estimate.stay_days) + '</span>';
            html += '</div>';
            
            if (estimate.campaign_details && estimate.campaign_details.campaigns && estimate.campaign_details.campaigns.length > 0) {
                const campaign = estimate.campaign_details.campaigns[0];
                html += '<div class="cost-item campaign-discount">';
                html += '<span>' + campaign.campaign_name + '適用後 (' + formatCurrency(estimate.daily_rent) + '/日 × ' + estimate.stay_days + '日)</span>';
                html += '<span>' + formatCurrency(estimate.total_rent) + '</span>';
                html += '</div>';
            }
        } else {
            html += '<div class="cost-item">';
            html += '<span>日割賃料 (' + formatCurrency(estimate.daily_rent) + '/日 × ' + estimate.stay_days + '日)</span>';
            html += '<span>' + formatCurrency(estimate.total_rent) + '</span>';
            html += '</div>';
        }
        
        html += '<div class="cost-item">';
        html += '<span>共益費 (' + formatCurrency(estimate.daily_utilities) + '/日 × ' + estimate.stay_days + '日)</span>';
        html += '<span>' + formatCurrency(estimate.total_utilities) + '</span>';
        html += '</div>';
        
        html += '<div class="cost-item">';
        html += '<span>初期費用</span>';
        html += '<span>' + formatCurrency(estimate.initial_costs) + '</span>';
        html += '</div>';
        html += '<div class="cost-subitem">';
        html += '<span>　├ 清掃費</span><span>' + formatCurrency(estimate.cleaning_fee) + '</span>';
        html += '</div>';
        html += '<div class="cost-subitem">';
        html += '<span>　├ 鍵手数料</span><span>' + formatCurrency(estimate.key_fee) + '</span>';
        html += '</div>';
        html += '<div class="cost-subitem">';
        html += '<span>　└ 布団代</span><span>' + formatCurrency(estimate.bedding_fee) + '</span>';
        html += '</div>';
        
        if (estimate.person_additional_fee > 0) {
            html += '<div class="cost-item">';
            html += '<span>人数追加料金</span>';
            html += '<span>' + formatCurrency(estimate.person_additional_fee) + '</span>';
            html += '</div>';
            
            if (estimate.adult_additional_fee > 0) {
                html += '<div class="cost-subitem">';
                html += '<span>　├ 大人追加 (' + (estimate.num_adults - 1) + '名)</span>';
                html += '<span>' + formatCurrency(estimate.adult_additional_fee) + '</span>';
                html += '</div>';
                
                if (estimate.adult_additional_rent > 0) {
                    html += '<div class="cost-subitem-detail">';
                    html += '<span>　　├ 賃料 (' + (estimate.num_adults - 1) + '名 × ¥900/日 × ' + estimate.stay_days + '日)</span>';
                    html += '<span>' + formatCurrency(estimate.adult_additional_rent) + '</span>';
                    html += '</div>';
                }
                
                if (estimate.adult_additional_utilities > 0) {
                    html += '<div class="cost-subitem-detail">';
                    html += '<span>　　├ 共益費 (' + (estimate.num_adults - 1) + '名 × ¥200/日 × ' + estimate.stay_days + '日)</span>';
                    html += '<span>' + formatCurrency(estimate.adult_additional_utilities) + '</span>';
                    html += '</div>';
                }
                
                if (estimate.adult_bedding_fee > 0) {
                    html += '<div class="cost-subitem-detail">';
                    html += '<span>　　└ 布団代 (' + (estimate.num_adults - 1) + '名 × ¥1,100/日 × ' + estimate.stay_days + '日)</span>';
                    html += '<span>' + formatCurrency(estimate.adult_bedding_fee) + '</span>';
                    html += '</div>';
                }
            }
            
            if (estimate.children_additional_fee > 0) {
                html += '<div class="cost-subitem">';
                html += '<span>　└ 子ども追加 (' + estimate.num_children + '名)</span>';
                html += '<span>' + formatCurrency(estimate.children_additional_fee) + '</span>';
                html += '</div>';
                
                if (estimate.children_additional_rent > 0) {
                    html += '<div class="cost-subitem-detail">';
                    html += '<span>　　├ 賃料 (' + estimate.num_children + '名 × ¥450/日 × ' + estimate.stay_days + '日)</span>';
                    html += '<span>' + formatCurrency(estimate.children_additional_rent) + '</span>';
                    html += '</div>';
                }
                
                if (estimate.children_additional_utilities > 0) {
                    html += '<div class="cost-subitem-detail">';
                    html += '<span>　　├ 共益費 (' + estimate.num_children + '名 × ¥100/日 × ' + estimate.stay_days + '日)</span>';
                    html += '<span>' + formatCurrency(estimate.children_additional_utilities) + '</span>';
                    html += '</div>';
                }
                
                if (estimate.children_bedding_fee > 0) {
                    html += '<div class="cost-subitem-detail">';
                    html += '<span>　　└ 布団代 (' + estimate.num_children + '名 × ¥1,100/日 × ' + estimate.stay_days + '日)</span>';
                    html += '<span>' + formatCurrency(estimate.children_bedding_fee) + '</span>';
                    html += '</div>';
                }
            }
        }
        
        if (estimate.options_total > 0) {
            html += '<div class="cost-item">';
            html += '<span>オプション料金</span>';
            html += '<span>' + formatCurrency(estimate.options_total) + '</span>';
            html += '</div>';
            
            if (estimate.options_discount > 0) {
                html += '<div class="cost-item discount">';
                html += '<span>オプション同時購入割引';
                if (estimate.options_discount_eligible_count >= 2) {
                    html += ' (' + estimate.options_discount_eligible_count + '個選択)';
                }
                html += '</span>';
                html += '<span>-' + formatCurrency(estimate.options_discount) + '</span>';
                html += '</div>';
            }
        }
        
        if (estimate.campaign_discount > 0) {
            html += '<div class="cost-item discount">';
            html += '<span>キャンペーン割引';
            if (estimate.campaign_badge) {
                html += ' <span class="campaign-badge ' + (estimate.campaign_type || '') + '">' + estimate.campaign_badge + '</span>';
            }
            html += '</span>';
            html += '<span>-' + formatCurrency(estimate.campaign_discount) + '</span>';
            html += '</div>';
            
            if (estimate.campaign_details && estimate.campaign_details.length > 0) {
                estimate.campaign_details.forEach(function(campaign) {
                    html += '<div class="cost-subitem discount">';
                    html += '<span>　└ ' + campaign.name + ' (' + campaign.discount_value + '%割引)</span>';
                    html += '<span>-' + formatCurrency(campaign.discount_amount) + '</span>';
                    html += '</div>';
                });
            }
        }
        
        if (estimate.non_taxable_subtotal && estimate.taxable_subtotal) {
            html += '<div class="tax-separation-section">';
            html += '<h5>📊 税区分別内訳</h5>';
            
            html += '<div class="cost-item tax-breakdown">';
            html += '<span>非課税小計（賃料・共益費）</span>';
            html += '<span>' + formatCurrency(estimate.non_taxable_subtotal) + '</span>';
            html += '</div>';
            
            html += '<div class="cost-item tax-breakdown">';
            html += '<span>課税小計（税込）</span>';
            html += '<span>' + formatCurrency(estimate.taxable_subtotal) + '</span>';
            html += '</div>';
            
            if (estimate.tax_exclusive_amount && estimate.consumption_tax) {
                html += '<div class="cost-subitem tax-detail">';
                html += '<span>　├ 税抜金額</span>';
                html += '<span>' + formatCurrency(estimate.tax_exclusive_amount) + '</span>';
                html += '</div>';
                
                html += '<div class="cost-subitem tax-detail">';
                html += '<span>　└ 消費税（' + (estimate.tax_rate || 10) + '%）</span>';
                html += '<span>' + formatCurrency(estimate.consumption_tax) + '</span>';
                html += '</div>';
            }
            
            html += '</div>';
        }
        
        html += '<div class="cost-total">';
        html += '<span><strong>🎯 合計金額</strong></span>';
        html += '<span><strong>' + formatCurrency(estimate.final_total) + '</strong></span>';
        html += '</div>';
        
        if (estimate.tax_note) {
            html += '<p class="tax-note">' + estimate.tax_note + '</p>';
        }
        
        html += '</div>';
        
        if (estimate.selected_options && estimate.selected_options.length > 0) {
            html += '<div class="estimate-section">';
            html += '<h4>🛍️ 選択オプション詳細</h4>';
            estimate.selected_options.forEach(function(option) {
                html += '<div class="option-detail-item">';
                html += '<span>' + option.name + ' × ' + option.quantity;
                if (option.is_discount_target) {
                    html += ' <span class="discount-eligible">（割引対象）</span>';
                }
                html += '</span>';
                html += '<span>' + formatCurrency(option.total) + '</span>';
                html += '</div>';
            });
            html += '</div>';
        }
        
        html += '</div>';
        
        html += '<div class="booking-action-section">';
        html += '<div class="booking-confirmation">';
        html += '<p class="booking-notice">📋 <strong>この内容で仮予約を申し込みますか？</strong></p>';
        html += '<p class="booking-details">予約確定後、詳細な契約手続きのご案内をメールでお送りいたします。</p>';
        html += '</div>';
        html += '<div class="booking-buttons">';
        html += '<button type="button" id="submit-booking-btn" class="booking-submit-btn">✅ この内容で申し込む</button>';
        html += '<button type="button" id="modify-estimate-btn" class="booking-modify-btn">📝 見積もりを修正する</button>';
        html += '</div>';
        html += '</div>';
        
        html += '</div>';
        
        window.currentEstimateData = estimate;
        
        $resultDiv.html(html).show();
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
            room_id: $('#room_id').val(),
            move_in_date: $('#move_in_date').val(),
            move_out_date: $('#move_out_date').val(),
            num_adults: $('#num_adults').val(),
            num_children: $('#num_children').val(),
            selected_options: selectedOptions,
            guest_name: $('#guest_name').val().trim(),
            company_name: $('#company_name').val().trim(),
            guest_email: $('#guest_email').val().trim(),
            guest_phone: $('#guest_phone').val().trim(),
            special_requests: $('#special_requests').val().trim()
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
                    
                    const roomSelect = $('#room_id');
                    if (roomSelect.length) {
                        roomSelect.empty().append('<option value="">部屋を選択してください...</option>');
                        if (filters.rooms && filters.rooms.length > 0) {
                            filters.rooms.forEach(function(room) {
                                roomSelect.append('<option value="' + room.room_id + '">' + room.display_name + ' (¥' + parseInt(room.daily_rent).toLocaleString() + '/日)</option>');
                            });
                        }
                    }
                    
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
            }
        });
    }
    
    function searchProperties() {
        const moveInDate = $('#move_in_date').val();
        const moveOutDate = $('#move_out_date').val();
        
        if (!moveInDate || !moveOutDate) {
            alert(monthlyBookingAjax.selectDatesFirst);
            return;
        }
        
        $.ajax({
            url: monthlyBookingAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'search_properties',
                nonce: monthlyBookingAjax.nonce,
                start_date: moveInDate,
                end_date: moveOutDate,
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

    function determinePlanByDuration(moveInDate, moveOutDate) {
        const stayDays = calculateStayDuration(moveInDate, moveOutDate);
        const stayMonths = calculateStayMonths(moveInDate, moveOutDate);
        
        if (stayDays >= 7 && stayMonths < 1) {
            return { 
                code: 'SS', 
                name: 'SS Plan - スーパーショートプラン',
                duration: stayDays + '日間'
            };
        } else if (stayMonths >= 1 && stayMonths < 3) {
            return { 
                code: 'S', 
                name: 'S Plan - ショートプラン',
                duration: stayDays + '日間'
            };
        } else if (stayMonths >= 3 && stayMonths < 6) {
            return { 
                code: 'M', 
                name: 'M Plan - ミドルプラン',
                duration: stayDays + '日間'
            };
        } else if (stayMonths >= 6) {
            return { 
                code: 'L', 
                name: 'L Plan - ロングプラン',
                duration: stayDays + '日間'
            };
        } else {
            return { 
                code: '', 
                name: '滞在期間が短すぎます（最低7日間必要）',
                duration: stayDays + '日間'
            };
        }
    }
    
    function calculateStayDuration(moveInDate, moveOutDate) {
        if (!moveInDate || !moveOutDate) {
            return 0;
        }
        
        const checkIn = new Date(moveInDate);
        const checkOut = new Date(moveOutDate);
        
        if (checkOut <= checkIn) {
            return 0;
        }
        
        const timeDiff = checkOut.getTime() - checkIn.getTime();
        const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
        
        return daysDiff + 1;
    }
    
    function calculateStayMonths(moveInDate, moveOutDate) {
        if (!moveInDate || !moveOutDate) return 0;
        
        const checkIn = new Date(moveInDate);
        const checkOut = new Date(moveOutDate);
        
        let months = 0;
        let currentDate = new Date(checkIn);
        
        while (currentDate < checkOut) {
            const originalDay = currentDate.getDate();
            const nextMonth = new Date(currentDate);
            nextMonth.setMonth(nextMonth.getMonth() + 1);
            
            if (nextMonth.getDate() !== originalDay) {
                nextMonth.setDate(0);
            }
            
            if (nextMonth <= checkOut) {
                months++;
                currentDate = new Date(nextMonth);
            } else {
                const daysRemaining = Math.floor((checkOut - currentDate) / (1000 * 60 * 60 * 24));
                if (daysRemaining >= 30) { // Strict 30-day minimum for partial month
                    months++;
                }
                break;
            }
        }
        
        return months;
    }
    
    function updatePlanDisplay() {
        const moveInDate = $('#move_in_date').val();
        const moveOutDate = $('#move_out_date').val();
        
        if (moveInDate && moveOutDate) {
            const plan = determinePlanByDuration(moveInDate, moveOutDate);
            $('#auto-selected-plan').text(plan.name + ' (' + plan.duration + ')');
            $('#selected-plan-display').show();
            
            if (plan.code) {
                $('#selected-plan-display').removeClass('error-plan').addClass('valid-plan');
            } else {
                $('#selected-plan-display').removeClass('valid-plan').addClass('error-plan');
            }
            
            if ($('#estimate-result').is(':visible')) {
                setTimeout(calculateEstimate, 300);
            }
        } else {
            $('#selected-plan-display').hide();
        }
    }
    
    $('#move_in_date, #move_out_date').on('change', updatePlanDisplay);
    

    $(document).on('click', '#submit-booking-btn', function(e) {
        e.preventDefault();
        submitBooking();
    });
    
    $(document).on('click', '#modify-estimate-btn', function(e) {
        e.preventDefault();
        $('#estimate-result').hide();
        $('html, body').animate({
            scrollTop: $('#monthly-booking-estimate-form').offset().top
        }, 500);
    });
    
    function submitBooking() {
        if (!window.currentEstimateData) {
            showError('見積もりデータが見つかりません。再度見積もりを計算してください。');
            return;
        }
        
        const estimate = window.currentEstimateData;
        const $submitBtn = $('#submit-booking-btn');
        const $modifyBtn = $('#modify-estimate-btn');
        
        $submitBtn.prop('disabled', true).text('申し込み中...');
        $modifyBtn.prop('disabled', true);
        
        const bookingData = {
            action: 'submit_booking',
            nonce: monthlyBookingAjax.nonce,
            
            room_id: $('#room_id').val(),
            move_in_date: estimate.move_in_date,
            move_out_date: estimate.move_out_date,
            plan_type: estimate.plan,
            num_adults: estimate.num_adults,
            num_children: estimate.num_children,
            selected_options: getSelectedOptions().reduce((obj, id) => {
                obj[id] = 1;
                return obj;
            }, {}),
            
            daily_rent: estimate.daily_rent,
            total_rent: estimate.total_rent,
            daily_utilities: estimate.daily_utilities,
            total_utilities: estimate.total_utilities,
            cleaning_fee: estimate.cleaning_fee,
            key_fee: estimate.key_fee,
            bedding_fee: estimate.bedding_fee,
            initial_costs: estimate.initial_costs,
            person_additional_fee: estimate.person_additional_fee,
            options_total: estimate.options_total,
            options_discount: estimate.options_discount,
            total_price: estimate.subtotal,
            campaign_discount: estimate.campaign_discount,
            final_price: estimate.final_total,
            
            guest_name: $('#guest_name').val().trim(),
            guest_email: $('#guest_email').val().trim(),
            guest_phone: $('#guest_phone').val().trim(),
            company_name: $('#company_name').val().trim(),
            special_requests: $('#special_requests').val().trim()
        };
        
        $.ajax({
            url: monthlyBookingAjax.ajaxurl,
            type: 'POST',
            data: bookingData,
            success: function(response) {
                if (response.success) {
                    displayBookingSuccess(response.data);
                } else {
                    showError(response.data || '予約の申し込みに失敗しました。もう一度お試しください。');
                    $submitBtn.prop('disabled', false).text('✅ この内容で申し込む');
                    $modifyBtn.prop('disabled', false);
                }
            },
            error: function() {
                showError('ネットワークエラーが発生しました。もう一度お試しください。');
                $submitBtn.prop('disabled', false).text('✅ この内容で申し込む');
                $modifyBtn.prop('disabled', false);
            }
        });
    }
    
    function displayBookingSuccess(data) {
        const $resultDiv = $('#estimate-result');
        
        let html = '<div class="booking-success">';
        html += '<div class="success-header">';
        html += '<h3>🎉 仮予約完了</h3>';
        html += '<p class="success-message">ご予約ありがとうございます！仮予約が正常に完了いたしました。</p>';
        html += '</div>';
        
        html += '<div class="booking-details-section">';
        html += '<h4>📋 予約詳細</h4>';
        html += '<div class="booking-info">';
        html += '<p><strong>予約ID:</strong> ' + data.booking_id + '</p>';
        html += '<p><strong>お客様ID:</strong> ' + data.customer_id + '</p>';
        html += '<p><strong>予約日時:</strong> ' + new Date().toLocaleString('ja-JP') + '</p>';
        html += '</div>';
        html += '</div>';
        
        html += '<div class="next-steps-section">';
        html += '<h4>📧 今後の流れ</h4>';
        html += '<ol class="next-steps-list">';
        html += '<li>ご登録いただいたメールアドレスに確認メールをお送りします</li>';
        html += '<li>担当者より詳細な契約手続きのご案内をいたします</li>';
        html += '<li>必要書類の準備と提出をお願いします</li>';
        html += '<li>最終確認後、正式な契約となります</li>';
        html += '</ol>';
        html += '</div>';
        
        html += '<div class="contact-section">';
        html += '<h4>📞 お問い合わせ</h4>';
        html += '<p>ご不明な点がございましたら、お気軽にお問い合わせください。</p>';
        html += '</div>';
        
        html += '</div>';
        
        $resultDiv.html(html);
        
        $('html, body').animate({
            scrollTop: $resultDiv.offset().top
        }, 500);
    }

    loadOptions();
});
