jQuery(document).ready(function($) {
    'use strict';
    
    
    $('.toggle-campaign-status').on('click', function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const campaignId = $button.data('campaign-id');
        const isActive = $button.data('is-active');
        
        $.ajax({
            url: monthlyBookingAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'toggle_campaign',
                campaign_id: campaignId,
                is_active: isActive ? 0 : 1,
                nonce: monthlyBookingAdmin.reservationsNonce
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
        const $start = $form.find('#checkin_date');
        const $end = $form.find('#checkout_date');
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
    function escapeHtml(s){ return String(s).replace(/[&<>\"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]); }); }
    function escapeAttr(s){ return escapeHtml(s); }

    function refreshReservations() {
        $.ajax({
            url: monthlyBookingAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'mbp_reservation_list',
                _ajax_nonce: monthlyBookingAdmin.reservationsNonce
            }
        }).done(function(resp) {
            if (resp && resp.success && resp.data && Array.isArray(resp.data.reservations)) {
                const rows = resp.data.reservations.map(function(r){
                    var propRoom = (r.property_name ? r.property_name : '') + (r.room_name ? ' - ' + r.room_name : '');
                    var total = r.total_price ? '¥' + Number(r.total_price).toLocaleString() : '';
                    return '<tr>' +
                        '<td>' + escapeHtml(r.id) + '</td>' +
                        '<td>' + escapeHtml(propRoom) + '</td>' +
                        '<td>' + escapeHtml(r.guest_name || '') + '</td>' +
                        '<td>' + escapeHtml(r.guest_email || '') + '</td>' +
                        '<td>' + escapeHtml(r.checkin_date) + '</td>' +
                        '<td>' + escapeHtml(r.checkout_date) + '</td>' +
                        '<td>' + escapeHtml(total) + '</td>' +
                        '<td><span class="status-' + escapeAttr(r.status || '') + '">' + escapeHtml(r.status || '') + '</span></td>' +
                        '<td>' +
                          '<button type="button" class="button button-small mbp-reservation-edit"' +
                            ' data-id="' + escapeAttr(r.id) + '"' +
                            ' data-room-id="' + escapeAttr(r.room_id || '') + '"' +
                            ' data-start="' + escapeAttr(r.checkin_date) + '"' +
                            ' data-end="' + escapeAttr(r.checkout_date) + '"' +
                            ' data-guest-name="' + escapeAttr(r.guest_name || '') + '"' +
                            ' data-guest-email="' + escapeAttr(r.guest_email || '') + '"' +
                            ' data-status="' + escapeAttr(r.status || '') + '"' +
                            ' data-notes="' + escapeAttr(r.notes || '') + '">' +
                            '編集' +
                          '</button> ' +
                          '<button type="button" class="button button-small button-link-delete mbp-reservation-delete" data-id="' + escapeAttr(r.id) + '">削除</button>' +
                        '</td>' +
                    '</tr>';
                }).join('');
                $('#mbp-reservations-body').html(rows);
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
            _ajax_nonce: monthlyBookingAdmin.reservationsNonce,
            reservation_id: id || ''
        };

        ['room_id','checkin_date','checkout_date','guest_name','guest_email','guest_phone','status','notes'].forEach(function(name){
            const v = $form.find('[name="'+name+'"]').val();
            if (typeof v !== 'undefined') payload[name] = v;
        });

        $.ajax({
            url: monthlyBookingAdmin.ajaxurl,
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
                $('#checkin_date, #checkout_date').attr('aria-invalid', 'true').addClass('error');
                $('#checkin_date').trigger('focus');
            } else {
                renderMessage('error', 'ネットワークエラーが発生しました。');
            }
        });
    });

    $(document).on('click', '.mbp-reservation-edit', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const $inlineForm = $('#mbp-reservation-form');
        if (!$inlineForm.length) {
            const id = $btn.data('id');
            window.location.href = 'admin.php?page=monthly-room-booking-registration&action=edit&id=' + encodeURIComponent(id);
            return;
        }
        $('#mbp-reservation-id').val($btn.data('id'));
        $('#mbp-room-id').val($btn.data('room-id'));
        $('#checkin_date').val($btn.data('start'));
        $('#checkout_date').val($btn.data('end'));
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
            url: monthlyBookingAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'mbp_reservation_delete',
                _ajax_nonce: monthlyBookingAdmin.reservationsNonce,
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
        .html(`
            .error { border-color: #dc3232 !important; box-shadow: 0 0 2px rgba(220, 50, 50, 0.8); }
            
            /* Campaign Assignment Modal Styles */
            .monthly-booking-modal {
                display: none;
                position: fixed;
                z-index: 100000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.5);
            }
            
            .modal-content {
                background-color: #fefefe;
                margin: 5% auto;
                padding: 0;
                border: 1px solid #888;
                width: 80%;
                max-width: 600px;
                border-radius: 4px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            
            .modal-header {
                padding: 20px;
                background-color: #f1f1f1;
                border-bottom: 1px solid #ddd;
                border-radius: 4px 4px 0 0;
                position: relative;
            }
            
            .modal-header h2 {
                margin: 0;
                font-size: 18px;
                color: #23282d;
            }
            
            .close-modal {
                color: #aaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
                position: absolute;
                right: 20px;
                top: 15px;
                cursor: pointer;
            }
            
            .close-modal:hover,
            .close-modal:focus {
                color: #000;
                text-decoration: none;
            }
            
            .modal-body {
                padding: 20px;
            }
            
            .modal-footer {
                padding: 20px;
                background-color: #f1f1f1;
                border-top: 1px solid #ddd;
                border-radius: 0 0 4px 4px;
                text-align: right;
            }
            
            .modal-footer .button {
                margin-left: 10px;
            }
            
            /* Toggle Switch Styles */
            .toggle-switch {
                position: relative;
                display: inline-block;
                width: 60px;
                height: 34px;
                margin-right: 10px;
            }
            
            .toggle-switch input {
                opacity: 0;
                width: 0;
                height: 0;
            }
            
            .toggle-slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                transition: .4s;
                border-radius: 34px;
            }
            
            .toggle-slider:before {
                position: absolute;
                content: "";
                height: 26px;
                width: 26px;
                left: 4px;
                bottom: 4px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }
            
            input:checked + .toggle-slider {
                background-color: #2196F3;
            }
            
            input:checked + .toggle-slider:before {
                transform: translateX(26px);
            }
            
            .toggle-label {
                vertical-align: middle;
                font-weight: 600;
            }
            
            /* Campaign Assignment Table Styles */
            #campaign-assignments-table .status-active {
                color: #46b450;
                font-weight: 600;
            }
            
            #campaign-assignments-table .status-inactive {
                color: #dc3232;
                font-weight: 600;
            }
            
            .campaign-actions {
                white-space: nowrap;
            }
            
            .campaign-actions .button {
                margin-right: 5px;
                padding: 2px 8px;
                font-size: 11px;
                line-height: 1.4;
            }
            
            #no-assignments-message {
                text-align: center;
                padding: 40px 20px;
                color: #666;
                font-style: italic;
            }
        `)
        .appendTo('head');
    
    $('#room_select').on('change', function() {
        const roomId = $(this).val();
        const baseUrl = window.location.origin + window.location.pathname;
        const newUrl = baseUrl + '?page=monthly-room-booking-calendar&room_id=' + roomId;
        
        
        if (roomId && roomId !== '0') {
            window.location.href = newUrl;
        }
    });
    
    let currentRoomId = null;
    
    if ($('#campaign-assignments-container').length > 0) {
        currentRoomId = $('#room-id').val();
        if (currentRoomId) {
            loadCampaignAssignments();
            loadActiveCampaigns();
        }
    }
    
    $('#add-campaign-assignment').on('click', function() {
        resetCampaignForm();
        $('#modal-title').text('Add Campaign Assignment');
        showCampaignModal();
    });
    
    $('.close-modal, .cancel-modal').on('click', function() {
        hideCampaignModal();
    });
    
    $('#campaign-assignment-modal').on('click', function(e) {
        if (e.target === this) {
            hideCampaignModal();
        }
    });
    
    $('#save-assignment').on('click', function() {
        saveCampaignAssignment();
    });
    
    $('#start-date, #end-date').on('change', function() {
        validateDateRange();
        checkPeriodOverlap();
    });
    
    $(document).on('click', '.edit-assignment', function() {
        const assignmentId = $(this).data('assignment-id');
        editCampaignAssignment(assignmentId);
    });
    
    $(document).on('click', '.delete-assignment', function() {
        const assignmentId = $(this).data('assignment-id');
        if (confirm(__('Are you sure you want to delete this campaign assignment?', 'monthly-booking'))) {
            deleteCampaignAssignment(assignmentId);
        }
    });
    
    $(document).on('click', '.toggle-assignment-status', function() {
        const assignmentId = $(this).data('assignment-id');
        const isActive = $(this).data('is-active');
        toggleAssignmentStatus(assignmentId, isActive);
    });
    
    function showCampaignModal() {
        $('#campaign-assignment-modal').show();
    }
    
    function hideCampaignModal() {
        $('#campaign-assignment-modal').hide();
        resetCampaignForm();
    }
    
    function resetCampaignForm() {
        $('#campaign-assignment-form')[0].reset();
        $('#assignment-id').val('');
        $('#is-active').prop('checked', true);
        hideValidationErrors();
    }
    
    function loadCampaignAssignments() {
        $.ajax({
            url: monthlyBookingAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_room_campaign_assignments',
                room_id: currentRoomId,
                nonce: monthlyBookingAdmin.reservationsNonce
            },
            success: function(response) {
                if (response.success) {
                    renderCampaignAssignments(response.data);
                } else {
                    console.error('Failed to load campaign assignments:', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('キャンペーン割り当ての読み込みに失敗しました:', error);
                $('#campaign-assignments-table').hide();
                $('#no-assignments-message').show().find('p').text(__('キャンペーン割り当ての読み込みに失敗しました。ページを再読み込みしてください。', 'monthly-booking'));
            }
        });
    }
    
    function loadActiveCampaigns() {
        $.ajax({
            url: monthlyBookingAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_active_campaigns',
                nonce: monthlyBookingAdmin.reservationsNonce
            },
            success: function(response) {
                if (response.success) {
                    populateCampaignSelect(response.data);
                } else {
                    console.error('Failed to load campaigns:', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('アクティブキャンペーンの読み込みに失敗しました:', error);
                $('#campaign-select').html('<option value="">' + __('キャンペーンの読み込みに失敗しました', 'monthly-booking') + '</option>');
            }
        });
    }
    
    function populateCampaignSelect(campaigns) {
        const $select = $('#campaign-select');
        $select.find('option:not(:first)').remove();
        
        campaigns.forEach(function(campaign) {
            const discountText = campaign.discount_type === 'percentage' 
                ? campaign.discount_value + '%' 
                : '¥' + campaign.discount_value;
            $select.append(
                $('<option></option>')
                    .val(campaign.id)
                    .text(campaign.campaign_name + ' (' + discountText + ')')
            );
        });
    }
    
    function renderCampaignAssignments(assignments) {
        const $tbody = $('#campaign-assignments-tbody');
        $tbody.empty();
        
        if (assignments.length === 0) {
            $('#campaign-assignments-table').hide();
            $('#no-assignments-message').show();
            return;
        }
        
        $('#no-assignments-message').hide();
        $('#campaign-assignments-table').show();
        
        assignments.forEach(function(assignment) {
            const startDate = new Date(assignment.start_date);
            const endDate = new Date(assignment.end_date);
            const duration = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
            
            const statusClass = assignment.is_active == 1 ? 'status-active' : 'status-inactive';
            const statusText = assignment.is_active == 1 ? 'Active' : 'Inactive';
            
            const discountText = assignment.discount_type === 'percentage' 
                ? assignment.discount_value + '%' 
                : '¥' + assignment.discount_value;
            
            const row = `
                <tr>
                    <td>
                        <strong>${assignment.campaign_name}</strong><br>
                        <small>${discountText} discount</small>
                    </td>
                    <td>
                        ${assignment.start_date} to ${assignment.end_date}
                    </td>
                    <td>${duration} days</td>
                    <td><span class="${statusClass}">${statusText}</span></td>
                    <td class="campaign-actions">
                        <button type="button" class="button edit-assignment" data-assignment-id="${assignment.id}">
                            Edit
                        </button>
                        <button type="button" class="button toggle-assignment-status" 
                                data-assignment-id="${assignment.id}" data-is-active="${assignment.is_active}">
                            ${assignment.is_active == 1 ? 'Disable' : 'Enable'}
                        </button>
                        <button type="button" class="button delete-assignment" data-assignment-id="${assignment.id}">
                            Delete
                        </button>
                    </td>
                </tr>
            `;
            $tbody.append(row);
        });
    }
    
    function saveCampaignAssignment() {
        if (!validateForm()) {
            return;
        }
        
        const formData = {
            action: 'save_campaign_assignment',
            assignment_id: $('#assignment-id').val(),
            room_id: currentRoomId,
            campaign_id: $('#campaign-select').val(),
            start_date: $('#start-date').val(),
            end_date: $('#end-date').val(),
            is_active: $('#is-active').is(':checked') ? 1 : 0,
            nonce: monthlyBookingAdmin.nonce
        };
        
        $.ajax({
            url: monthlyBookingAdmin.ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    hideCampaignModal();
                    loadCampaignAssignments();
                } else {
                    showValidationErrors(response.data);
                }
            },
            error: function() {
                showValidationErrors(__('Network error occurred while saving.', 'monthly-booking'));
            }
        });
    }
    
    function editCampaignAssignment(assignmentId) {
        const $row = $(`.edit-assignment[data-assignment-id="${assignmentId}"]`).closest('tr');
        
        $.ajax({
            url: monthlyBookingAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_campaign_assignment',
                assignment_id: assignmentId,
                nonce: monthlyBookingAdmin.reservationsNonce
            },
            success: function(response) {
                if (response.success) {
                    const assignment = response.data;
                    $('#assignment-id').val(assignment.id);
                    $('#campaign-select').val(assignment.campaign_id);
                    $('#start-date').val(assignment.start_date);
                    $('#end-date').val(assignment.end_date);
                    $('#is-active').prop('checked', assignment.is_active == 1);
                    $('#modal-title').text('Edit Campaign Assignment');
                    showCampaignModal();
                } else {
                    alert(__('Failed to load assignment data: ', 'monthly-booking') + response.data);
                }
            },
            error: function() {
                alert(__('割り当てデータの読み込みに失敗しました。', 'monthly-booking'));
            }
        });
    }
    
    function deleteCampaignAssignment(assignmentId) {
        $.ajax({
            url: monthlyBookingAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_campaign_assignment',
                assignment_id: assignmentId,
                nonce: monthlyBookingAdmin.reservationsNonce
            },
            success: function(response) {
                if (response.success) {
                    loadCampaignAssignments();
                } else {
                    alert(__('Failed to delete assignment: ', 'monthly-booking') + response.data);
                }
            },
            error: function() {
                alert(__('Network error occurred while deleting.', 'monthly-booking'));
            }
        });
    }
    
    function toggleAssignmentStatus(assignmentId, currentStatus) {
        const newStatus = currentStatus == 1 ? 0 : 1;
        
        $.ajax({
            url: monthlyBookingAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'toggle_assignment_status',
                assignment_id: assignmentId,
                is_active: newStatus,
                nonce: monthlyBookingAdmin.reservationsNonce
            },
            success: function(response) {
                if (response.success) {
                    loadCampaignAssignments();
                } else {
                    alert(__('Failed to toggle status: ', 'monthly-booking') + response.data);
                }
            },
            error: function() {
                alert(__('Network error occurred while toggling status.', 'monthly-booking'));
            }
        });
    }
    
    function validateForm() {
        hideValidationErrors();
        
        if (!$('#campaign-select').val()) {
            showValidationErrors(__('Please select a campaign.', 'monthly-booking'));
            return false;
        }
        
        if (!$('#start-date').val() || !$('#end-date').val()) {
            showValidationErrors(__('Please select both start and end dates.', 'monthly-booking'));
            return false;
        }
        
        if (!validateDateRange()) {
            return false;
        }
        
        return true;
    }
    
    function validateDateRange() {
        const startDate = new Date($('#start-date').val());
        const endDate = new Date($('#end-date').val());
        
        if (startDate >= endDate) {
            showValidationErrors(__('End date must be after start date.', 'monthly-booking'));
            return false;
        }
        
        return true;
    }
    
    function checkPeriodOverlap() {
        const startDate = $('#start-date').val();
        const endDate = $('#end-date').val();
        const assignmentId = $('#assignment-id').val();
        
        if (!startDate || !endDate || !currentRoomId) {
            return;
        }
        
        $.ajax({
            url: monthlyBookingAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'check_campaign_period_overlap',
                room_id: currentRoomId,
                start_date: startDate,
                end_date: endDate,
                assignment_id: assignmentId,
                nonce: monthlyBookingAdmin.reservationsNonce
            },
            success: function(response) {
                if (!response.success) {
                    showValidationErrors(response.data);
                } else {
                    hideValidationErrors();
                }
            },
            error: function() {
                console.error('Network error checking period overlap');
            }
        });
    }
    
    function showValidationErrors(message) {
        $('#validation-errors p').text(message);
        $('#validation-errors').show();
    }
    
    function hideValidationErrors() {
        $('#validation-errors').hide();
    }
    
});

$(document).ready(function() {
    if ($('#room_select option').length <= 1) {
        $('.calendar-controls').after('<div class="notice notice-warning"><p>' + __('部屋データが見つかりません。管理者にお問い合わせください。', 'monthly-booking') + '</p></div>');
    }
    
    $('#room_select').on('change', function() {
        try {
            const roomId = $(this).val();
            if (roomId && roomId !== '0') {
                const baseUrl = window.location.origin + window.location.pathname;
                const newUrl = baseUrl + '?page=monthly-room-booking-calendar&room_id=' + roomId;
                window.location.href = newUrl;
            }
        } catch (error) {
            console.error('部屋選択エラー:', error);
            alert(__('部屋選択でエラーが発生しました: ', 'monthly-booking') + error.message);
        }
    });
});
