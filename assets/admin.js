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
        
        console.log('Room dropdown changed:', roomId);
        console.log('Redirecting to:', newUrl);
        
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
        if (confirm('Are you sure you want to delete this campaign assignment?')) {
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
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_room_campaign_assignments',
                room_id: currentRoomId,
                nonce: monthlyBookingAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    renderCampaignAssignments(response.data);
                } else {
                    console.error('Failed to load campaign assignments:', response.data);
                }
            },
            error: function() {
                console.error('Network error loading campaign assignments');
            }
        });
    }
    
    function loadActiveCampaigns() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_active_campaigns',
                nonce: monthlyBookingAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    populateCampaignSelect(response.data);
                } else {
                    console.error('Failed to load campaigns:', response.data);
                }
            },
            error: function() {
                console.error('Network error loading campaigns');
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
            url: ajaxurl,
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
                showValidationErrors('Network error occurred while saving.');
            }
        });
    }
    
    function editCampaignAssignment(assignmentId) {
        const $row = $(`.edit-assignment[data-assignment-id="${assignmentId}"]`).closest('tr');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_campaign_assignment',
                assignment_id: assignmentId,
                nonce: monthlyBookingAdmin.nonce
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
                    alert('Failed to load assignment data: ' + response.data);
                }
            },
            error: function() {
                alert('Network error loading assignment data.');
            }
        });
    }
    
    function deleteCampaignAssignment(assignmentId) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_campaign_assignment',
                assignment_id: assignmentId,
                nonce: monthlyBookingAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    loadCampaignAssignments();
                } else {
                    alert('Failed to delete assignment: ' + response.data);
                }
            },
            error: function() {
                alert('Network error occurred while deleting.');
            }
        });
    }
    
    function toggleAssignmentStatus(assignmentId, currentStatus) {
        const newStatus = currentStatus == 1 ? 0 : 1;
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'toggle_assignment_status',
                assignment_id: assignmentId,
                is_active: newStatus,
                nonce: monthlyBookingAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    loadCampaignAssignments();
                } else {
                    alert('Failed to toggle status: ' + response.data);
                }
            },
            error: function() {
                alert('Network error occurred while toggling status.');
            }
        });
    }
    
    function validateForm() {
        hideValidationErrors();
        
        if (!$('#campaign-select').val()) {
            showValidationErrors('Please select a campaign.');
            return false;
        }
        
        if (!$('#start-date').val() || !$('#end-date').val()) {
            showValidationErrors('Please select both start and end dates.');
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
            showValidationErrors('End date must be after start date.');
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
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'check_campaign_period_overlap',
                room_id: currentRoomId,
                start_date: startDate,
                end_date: endDate,
                assignment_id: assignmentId,
                nonce: monthlyBookingAdmin.nonce
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
    
    console.log('Monthly Booking admin scripts loaded');
});
