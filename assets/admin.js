document.addEventListener('DOMContentLoaded', function () {
  var t = window.mb_t || function (k) { return k; };

  document.querySelectorAll('.campaign-duplicate').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var id = this.getAttribute('data-campaign-id') || '';
      var name = this.getAttribute('data-campaign-name') || '';
      var dtype = this.getAttribute('data-discount-type') || 'percentage';
      var dval = this.getAttribute('data-discount-value') || '';
      var sdate = this.getAttribute('data-start-date') || '';
      var edate = this.getAttribute('data-end-date') || '';
      var targetPlan = this.getAttribute('data-target-plan') || '';

      try {
        if (typeof showCampaignModal === 'function') {
          showCampaignModal();
        } else {
          var m = document.getElementById('campaign-modal');
          if (m) m.style.display = 'block';
        }

        var $title = document.getElementById('modal-title');
        if ($title) $title.textContent = (window.mb_t ? mb_t('campaigns.actions.duplicate') : '複製') + ' → ' + (window.mb_t ? mb_t('common.create') : '新規作成');

        var $action = document.getElementById('form-action');
        if ($action) $action.value = 'create_campaign';
        var $cid = document.getElementById('campaign-id');
        if ($cid) $cid.value = '';

        var $name = document.getElementById('name');
        if ($name) $name.value = (name || '') + ' (複製)';

        var $dtype = document.getElementById('discount_type');
        if ($dtype) { $dtype.value = dtype; if (typeof updateDiscountUnit === 'function') updateDiscountUnit(); }

        var $dval = document.getElementById('discount_value');
        if ($dval) $dval.value = dval;

        var $sd = document.getElementById('start_date');
        if ($sd && sdate) $sd.value = sdate;
        var $ed = document.getElementById('end_date');
        if ($ed && edate) $ed.value = edate;

        if (targetPlan) {
          var plans = String(targetPlan).split(',');
          ['SS','S','M','L'].forEach(function(code){
            var qs = 'input[name="contract_types[]"][value="'+code+'"]';
            var el = document.querySelector(qs);
            if (el) el.checked = plans.indexOf(code) !== -1 || targetPlan === 'ALL';
          });
        }
      } catch (err) {
        console.error('duplicate preset failed', err);
        alert((window.mb_t ? mb_t('error.generic') : 'エラーが発生しました'));
      }
    });
  });

  document.querySelectorAll('.toggle-campaign-status').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var id = this.getAttribute('data-campaign-id');
      var active = this.getAttribute('data-is-active') === '1' || this.getAttribute('data-is-active') === 'true';
      var label = active ? t('campaigns.actions.disable') : t('campaigns.actions.enable');
      if (!confirm(label + ' ?')) return;
      var newActive = !active;
      this.setAttribute('data-is-active', newActive ? '1' : '0');
      this.textContent = newActive ? t('campaigns.actions.disable') : t('campaigns.actions.enable');
      var tr = this.closest('tr');
      if (tr) {
        var statusCell = tr.querySelector('td:nth-child(5), td.status-cell');
        if (statusCell) {
          statusCell.textContent = newActive ? t('status.active') : t('status.inactive');
        }
      }
      alert(label + ' #' + id);
    });
  });

  document.querySelectorAll('.campaign-delete').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      var id = this.getAttribute('data-campaign-id');
      console.log('delete campaign', id);
    });
  });
  document.querySelectorAll('.campaign-assign').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var id = this.getAttribute('data-campaign-id');
      alert(t('campaigns.actions.assign_to_rooms') + ' #' + id);
    });
  });
});
jQuery(document).ready(function($) {
    function showCampaignMessage(type, text) {
        var $m = jQuery('#campaign-form-message');
        if (!$m.length) return;
        $m.removeClass('error success').addClass(type === 'error' ? 'error' : 'success').text(text || '').show();
    }
    jQuery(document).on('submit', '#campaign-form', function(e){
        e.preventDefault();
        var $form = jQuery(this);
        var action = jQuery('#form-action').val() || 'create_campaign';
        var nonce = $form.find('input[name="nonce"]').val() || (window.monthlyBookingAdmin ? monthlyBookingAdmin.nonce : '');
        var payload = {
            action: action,
            nonce: nonce,
            name: $form.find('[name="name"]').val() || '',
            discount_type: $form.find('[name="discount_type"]').val() || '',
            discount_value: $form.find('[name="discount_value"]').val() || '',
            period_type: ($form.find('input[name="period_type"]:checked').val() || 'fixed'),
            relative_days: $form.find('[name="relative_days"]').val() || '',
            start_date: $form.find('[name="start_date"]').val() || '',
            end_date: $form.find('[name="end_date"]').val() || '',
            'contract_types[]': $form.find('input[name="contract_types[]"]:checked').map(function(){return this.value;}).get()
        };
        var cid = $form.find('[name="campaign_id"]').val();
        if (cid) payload.campaign_id = cid;
        jQuery.ajax({
            url: (window.monthlyBookingAdmin ? monthlyBookingAdmin.ajaxurl : ajaxurl),
            type: 'POST',
            data: payload
        }).done(function(resp){
            if (resp && resp.success) {
                var msg = (resp.data && resp.data.message) ? resp.data.message : 'OK';
                showCampaignMessage('success', msg);
                setTimeout(function(){ window.location.reload(); }, 800);
            } else {
                var msg = (resp && (resp.data || resp.message)) ? (resp.data || resp.message) : 'エラーが発生しました。';
                showCampaignMessage('error', msg);
            }
        }).fail(function(xhr){
            var msg = 'ネットワークエラーが発生しました。';
            try {
                var j = xhr.responseJSON;
                if (j && (j.data || j.message)) msg = j.data || j.message;
            } catch(e){}
            showCampaignMessage('error', msg);
        });
    });
    jQuery(document).on('click', '.campaign-delete', function(e){
        e.preventDefault();
        var id = jQuery(this).data('campaign-id');
        if (!id) return;
        if (!window.confirm('このキャンペーンを削除しますか？')) return;
        jQuery.ajax({
            url: (window.monthlyBookingAdmin ? monthlyBookingAdmin.ajaxurl : ajaxurl),
            type: 'POST',
            data: { action: 'delete_campaign', nonce: (window.monthlyBookingAdmin ? monthlyBookingAdmin.nonce : ''), campaign_id: id }
        }).done(function(resp){
            if (resp && resp.success) {
                window.location.reload();
            } else {
                var msg = (resp && (resp.data || resp.message)) ? (resp.data || resp.message) : '削除に失敗しました。';
                showCampaignMessage('error', msg);
            }
        }).fail(function(){ showCampaignMessage('error', 'ネットワークエラーが発生しました。'); });
    });
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
    
function getSelectedRoomIds() {
  var ids = [];
  document.querySelectorAll('#rooms-table .room-select:checked').forEach(function(el){
    var v = el.value;
    if (v) ids.push(v);
  });
  return ids;
}
document.addEventListener('click', function(e){
  if (e.target && e.target.id === 'rooms-bulk-assign') {
    e.preventDefault();
    var ids = getSelectedRoomIds();
    if (!ids.length) { alert((window.mb_t?mb_t('rooms.bulk.select_warning'):'部屋を選択してください')); return; }
    if (typeof openAssignmentModal === 'function') {
      openAssignmentModal({});
    } else {
      var modal = document.getElementById('assignment-modal');
      if (!modal) { alert('Modal not found'); return; }
      modal.style.display = 'block';
    }
    var btnSave = document.getElementById('assignment-save');
    if (btnSave) {
      btnSave.onclick = function(){
        var campaign = document.getElementById('assignment_campaign').value;
        var sd = document.getElementById('assignment_start').value;
        var ed = document.getElementById('assignment_end').value;
        var active = document.getElementById('assignment_active').checked ? 1 : 0;
        if (!campaign || !sd || !ed) { alert((window.mb_t?mb_t('campaigns.validation.fixed_dates_required'):'必須項目が未入力です')); return; }
        var ajaxurl = (window.monthlyBookingAdmin ? monthlyBookingAdmin.ajaxurl : ajaxurl);
        var nonce = (window.monthlyBookingAdmin ? monthlyBookingAdmin.nonce : '');
        var ok = 0, ng = 0;
        var promises = ids.map(function(roomId){
          return jQuery.post(ajaxurl, {
            action: 'mb_save_room_assignment',
            nonce: nonce,
            room_id: roomId,
            campaign_id: campaign,
            start_date: sd,
            end_date: ed,
            is_active: active
          }).then(function(resp){
            if (resp && resp.success) ok++; else ng++;
          }).catch(function(){ ng++; });
        });
        Promise.all(promises).then(function(){
          alert((window.mb_t?mb_t('rooms.bulk.result'):'完了') + ' OK='+ok+' NG='+ng);
          window.location.reload();
        });
      };
    }
    var cancelBtn = document.getElementById('assignment-cancel');
    if (cancelBtn) cancelBtn.onclick = function(){ document.getElementById('assignment-modal').style.display='none'; };
  }
  if (e.target && e.target.id === 'rooms-bulk-unassign') {
    e.preventDefault();
    var ids = getSelectedRoomIds();
    if (!ids.length) { alert((window.mb_t?mb_t('rooms.bulk.select_warning'):'部屋を選択してください')); return; }
    var campaign = prompt((window.mb_t?mb_t('rooms.bulk.unassign_prompt'):'解除するキャンペーンIDを入力（空で全て）'), '');
    var ajaxurl = (window.monthlyBookingAdmin ? monthlyBookingAdmin.ajaxurl : ajaxurl);
    var nonce = (window.monthlyBookingAdmin ? monthlyBookingAdmin.nonce : '');
    jQuery.post(ajaxurl, {
      action: 'mb_bulk_unassign_campaigns',
      nonce: nonce,
      room_ids: ids,
      campaign_id: campaign ? campaign : ''
    }).done(function(resp){
      if (resp && resp.success) {
        alert((window.mb_t?mb_t('rooms.bulk.unassign_done'):'解除しました'));
        window.location.reload();
      } else {
        alert((resp && (resp.data||resp.message)) || 'エラー');
      }
    }).fail(function(){ alert('Network error'); });
  }
  if (e.target && e.target.id === 'rooms-select-all') {
    var checked = e.target.checked;
    document.querySelectorAll('#rooms-table .room-select').forEach(function(el){ el.checked = checked; });
  }
});
jQuery(document).on('change', '.cleaning-toggle', function(){
  var roomId = jQuery(this).data('room-id');
  var val = jQuery(this).is(':checked') ? 1 : 0;
  jQuery.post((window.monthlyBookingAdmin?monthlyBookingAdmin.ajaxurl:ajaxurl), {
    action: 'mb_toggle_cleaning',
    nonce: (window.monthlyBookingAdmin?monthlyBookingAdmin.nonce:''),
    room_id: roomId,
    is_cleaned: val
  }).done(function(resp){
  });
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

function openAssignmentModal(opts){
    var $m = jQuery('#assignment-modal');
    if (!$m.length) return;
    var $room = jQuery('#assignment_room');
    var $camp = jQuery('#assignment_campaign');
    var $sd = jQuery('#assignment_start');
    var $ed = jQuery('#assignment_end');
    var $act = jQuery('#assignment_active');
    var $msg = jQuery('#assignment-message');
    var $save = jQuery('#assignment-save');
    $room.empty(); $camp.empty();
    $msg.removeClass('notice-error notice-success').hide().text('');
    $save.prop('disabled', false);

    jQuery.post(monthlyBookingAdmin.ajaxurl, {
        action: 'mb_get_rooms',
        nonce: monthlyBookingAdmin.nonce
    }, function(resp){
        if (resp && resp.success && Array.isArray(resp.data)) {
            resp.data.forEach(function(r){
                var opt = document.createElement('option');
                opt.value = r.id; opt.textContent = r.name || r.display_name || ('#'+r.id);
                $room.append(opt);
            });
            if (opts && opts.room_id) $room.val(String(opts.room_id));
        }
    });

    jQuery.post(monthlyBookingAdmin.ajaxurl, {
        action: 'mb_get_campaigns',
        nonce: monthlyBookingAdmin.nonce
    }, function(resp){
        if (resp && resp.success && Array.isArray(resp.data)) {
            resp.data.forEach(function(c){
                var opt = document.createElement('option');
                opt.value = c.id; opt.textContent = c.name || ('#'+c.id);
                $camp.append(opt);
            });
            if (opts && opts.campaign_id) $camp.val(String(opts.campaign_id));
        }
    });

    if (opts && opts.start_date) $sd.val(opts.start_date); else $sd.val('');
    if (opts && opts.end_date) $ed.val(opts.end_date); else $ed.val('');
    $act.prop('checked', !(opts && opts.is_active === 0));

    attachRoomSearch($room);
    attachRealtimeOverlap($room, $sd, $ed, $msg, $save);

    $m.show();
}

function attachRoomSearch($room){
    var $search = jQuery('#assignment_room_search');
    if (!$search.length) return;
    var timer = null;
    $search.off('input').on('input', function(){
        clearTimeout(timer);
        var q = this.value.trim();
        timer = setTimeout(function(){
            jQuery.post(monthlyBookingAdmin.ajaxurl, {
                action: 'mb_get_rooms',
                nonce: monthlyBookingAdmin.nonce,
                q: q
            }, function(resp){
                if (resp && resp.success && Array.isArray(resp.data)) {
                    var current = $room.val();
                    $room.empty();
                    resp.data.forEach(function(r){
                        var opt = document.createElement('option');
                        opt.value = r.id; opt.textContent = r.name || r.display_name || ('#'+r.id);
                        $room.append(opt);
                    });
                    if (current && $room.find('option[value="'+current+'"]').length) {
                        $room.val(current);
                    }
                }
            });
        }, 250);
    });
}

function attachRealtimeOverlap($room, $sd, $ed, $msg, $save){
    function run(){
        var room_id = parseInt($room.val()||'0', 10);
        var sd = $sd.val();
        var ed = $ed.val();
        if (!room_id || !sd || !ed) { $save.prop('disabled', false); $msg.hide(); return; }
        $save.prop('disabled', true);
        jQuery.post(monthlyBookingAdmin.ajaxurl, {
            action: 'mb_check_overlap',
            nonce: monthlyBookingAdmin.nonce,
            room_id: room_id,
            start_date: sd,
            end_date: ed
        }, function(r){
            var t = window.mb_t || function(k){return k;};
            if (r && r.success && r.data && r.data.overlap) {
                $msg.removeClass('notice-success').addClass('notice notice-error').text(t('campaigns.validation.overlap_detected')).show();
                $save.prop('disabled', true);
            } else {
                $msg.removeClass('notice-error').addClass('notice notice-success').text('').hide();
                $save.prop('disabled', false);
            }
        });
    }
    var timer = null;
    $sd.add($ed).off('input change').on('input change', function(){
        clearTimeout(timer);
        timer = setTimeout(run, 300);
    });
    run();
}

jQuery(document).on('click', '#assignment-cancel', function(){ jQuery('#assignment-modal').hide(); });

jQuery(document).on('click', '#assignment-save', function(){
    var t = window.mb_t || function(k){return k;};
    var $msg = jQuery('#assignment-message');
    var $save = jQuery('#assignment-save');
    var room_id = parseInt(jQuery('#assignment_room').val()||'0',10);
    var campaign_id = parseInt(jQuery('#assignment_campaign').val()||'0',10);
    var sd = jQuery('#assignment_start').val();
    var ed = jQuery('#assignment_end').val();
    var is_active = jQuery('#assignment_active').is(':checked') ? 1 : 0;

    if (!room_id || !campaign_id || !sd || !ed) {
        $msg.removeClass('notice-success').addClass('notice notice-error').text(t('campaigns.validation.fixed_dates_required')).show();
        return;
    }

    $save.prop('disabled', true);

    jQuery.post(monthlyBookingAdmin.ajaxurl, {
        action: 'mb_check_overlap', nonce: monthlyBookingAdmin.nonce,
        room_id: room_id, start_date: sd, end_date: ed
    }, function(r){
        if (r && r.success && r.data && r.data.overlap) {
            $msg.removeClass('notice-success').addClass('notice notice-error').text(t('campaigns.validation.overlap_detected')).show();
            $save.prop('disabled', false);
            return;
        }
        jQuery.post(monthlyBookingAdmin.ajaxurl, {
            action: 'mb_save_room_assignment', nonce: monthlyBookingAdmin.nonce,
            room_id: room_id, campaign_id: campaign_id, start_date: sd, end_date: ed, is_active: is_active
        }, function(resp){
            if (resp && resp.success) {
                $msg.removeClass('notice-error').addClass('notice notice-success').text(t('notices.saved')).show();
                setTimeout(function(){
                    jQuery('#assignment-modal').hide();
                    if (typeof loadCampaignAssignments === 'function') {
                        loadCampaignAssignments(room_id);
                    }
                }, 800);
            } else {
                $msg.removeClass('notice-success').addClass('notice notice-error').text(t('error.generic')).show();
                $save.prop('disabled', false);
            }
        });
    });
});

jQuery(document).on('click', '.campaign-assign', function(e){
    e.preventDefault();
    var cid = jQuery(this).data('campaign-id');
    openAssignmentModal({ campaign_id: cid });
});
    
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
    (function autoCalcNightsCPT(){
        function ymdToDate(v){
            if(!v) return null;
            var parts = String(v).split('-');
            if (parts.length !== 3) return null;
            var y = parseInt(parts[0],10), m = parseInt(parts[1],10), d = parseInt(parts[2],10);
            if (!y || !m || !d) return null;
            return new Date(Date.UTC(y, m-1, d));
        }
        function recalc(){
            var $ci = jQuery('[name="checkin_date"]');
            var $co = jQuery('[name="checkout_date"]');
            var $n  = jQuery('[name="nights"]');
            if (!$ci.length || !$co.length || !$n.length) return;
            var ci = ymdToDate($ci.val());
            var co = ymdToDate($co.val());
            if (!ci || !co) return;
            var diffDays = Math.round((co - ci) / 86400000);
            if (Number.isFinite(diffDays)) {
                if (diffDays > 0) {
                    $n.val(diffDays);
                } else {
                    $n.val('');
                }
            }
        }
        jQuery(document).on('change', '[name="checkin_date"], [name="checkout_date"]', recalc);
        jQuery(recalc);
    })();
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
    
    $(document).on('click', '.campaign-edit', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var id = $btn.data('campaign-id');
        if (!id) return;

        var currentName = $btn.data('name') || $btn.data('campaign-name') || '';
        var currentDiscountType = $btn.data('discount-type') || 'percentage';
        var currentDiscountValue = $btn.data('discount-value') || '';
        var currentStart = $btn.data('start-date') || '';
        var currentEnd = $btn.data('end-date') || '';

        var name = window.prompt('キャンペーン名を入力', currentName);
        if (name === null) return;
        var discountValueStr = window.prompt('割引値を入力', String(currentDiscountValue));
        if (discountValueStr === null) return;
        var discountValue = parseFloat(discountValueStr);
        if (isNaN(discountValue)) discountValue = 0;
        var startDate = window.prompt('開始日 YYYY-MM-DD', currentStart);
        if (startDate === null) return;
        var endDate = window.prompt('終了日 YYYY-MM-DD', currentEnd);
        if (endDate === null) return;

        $.ajax({
            url: monthlyBookingAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'update_campaign',
                nonce: monthlyBookingAdmin.nonce,
                campaign_id: id,
                name: name,
                discount_type: currentDiscountType,
                discount_value: discountValue,
                start_date: startDate,
                end_date: endDate
            }
        }).done(function(resp){
            if (resp && resp.success) {
                location.reload();
            } else {
                alert('更新に失敗: ' + (resp && resp.data ? resp.data : ''));
            }
        }).fail(function(){
            alert('ネットワークエラー');
        });
    });

    $(document).on('click', '.campaign-delete', function(e) {
        e.preventDefault();
        var id = $(this).data('campaign-id');
        if (!id) return;
        if (!window.confirm('このキャンペーンを削除しますか？')) return;

        $.ajax({
            url: monthlyBookingAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_campaign',
                nonce: monthlyBookingAdmin.nonce,
                campaign_id: id
            }
        }).done(function(resp){
            if (resp && resp.success) {
                location.reload();
            } else {
                alert('削除に失敗: ' + (resp && resp.data ? resp.data : ''));
            }
        }).fail(function(){
            alert('ネットワークエラー');
        });
    });

});

$(document).ready(function() {
    if ($('#room_select option').length <= 1) {
        $('.calendar-controls').after('<div class="notice notice-warning"><p>' + __('部屋データが見つかりません。管理者にお問い合わせください。', 'monthly-booking') + '</p></div>');
    }
    
    (function(){
        var $form = $('#campaign-form');
        if (!$form.length) return;

        function t(k){ return (window.mb_t && typeof window.mb_t === 'function') ? window.mb_t(k) : k; }

        function getMode(){
            var v = $('#discount_type').val();
            return v === 'fixed' ? 'fixed' : 'percent';
        }

        function validate(){
            var ok = true;
            var msgs = [];

            var name = $('#name').val().trim();
            if (!name) { ok = false; msgs.push(t('campaigns.validation.name_required')); }

            var mode = getMode();
            var val = parseFloat($('#discount_value').val() || '0');
            if (isNaN(val)) val = 0;

            if (mode === 'percent') {
                if (!(val > 0 && val < 50)) { ok = false; msgs.push(t('campaigns.validation.discount_percent_range')); }
            } else {
                if (val < 0) { ok = false; msgs.push(t('campaigns.validation.discount_fixed_range')); }
            }

            var pt = $('input[name="period_type"]:checked').val() || 'fixed';
            if (pt === 'checkin_relative') {
                var rd = parseInt($('#relative_days').val() || '0', 10);
                if (!(rd >= 1 && rd <= 30)) { ok = false; msgs.push(t('campaigns.validation.relative_days_range')); }
            } else if (pt === 'fixed') {
                var sd = $('#start_date').val(), ed = $('#end_date').val();
                if (!sd || !ed) { ok = false; msgs.push(t('campaigns.validation.fixed_dates_required')); }
                if (sd && ed && sd >= ed) { ok = false; msgs.push(t('campaigns.validation.date_order')); }
            }

            if ($('input[name="contract_types[]"]:checked').length === 0) {
                ok = false; msgs.push(t('campaigns.validation.contract_types_required'));
            }

            var $msg = $('#campaign-form-message');
            if ($msg.length) {
                $msg.removeClass('notice-error notice-success')
                    .addClass(ok ? 'notice notice-success' : 'notice notice-error')
                    .text(msgs.join(' / '))
                    .show();
            }

            return ok;
        }

        $('input[name="period_type"]').on('change', function(){
            var v = $('input[name="period_type"]:checked').val();
            if (v === 'checkin_relative') {
                $('.fixed-period-row').hide();
                $('#relative-days-row').show();
                $('#unlimited-warning').hide();
            } else if (v === 'fixed') {
                $('.fixed-period-row').show();
                $('#relative-days-row').hide();
                $('#unlimited-warning').hide();
            } else if (v === 'first_month_30d') {
                $('.fixed-period-row').hide();
                $('#relative-days-row').hide();
                $('#unlimited-warning').hide();
            } else if (v === 'unlimited') {
                $('.fixed-period-row').hide();
                $('#relative-days-row').hide();
                $('#unlimited-warning').show();
            }
        }).trigger('change');

        $form.on('submit', function(e){
            if (!validate()) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
    })();
$(document).on('click', '.calendar-matrix .matrix-cell.day', function(){
    var $cell = $(this);
    var $row = $cell.closest('.matrix-row');
    $('.calendar-matrix .matrix-cell.day[aria-selected="true"]').attr('aria-selected','false').removeClass('is-selected');
    $cell.attr('aria-selected','true').addClass('is-selected');
    var date = $cell.data('date');
    var code = $cell.data('code');
    var roomId = $row.data('room-id');
    var $panel = $('#mb-cal-sidepanel');
    if ($panel.length) {
        $panel.find('[data-field="room"]').text($row.find('.room-name').text());
        $panel.find('[data-field="date"]').text(date);
        var t = window.mb_t || function(k){return k;};
        var statusText = '';
        switch (code) {
            case 'occ': statusText = t('calendar.status.occupied'); break;
            case 'clean': statusText = t('calendar.status.cleaning'); break;
            case 'vac_camp': statusText = t('calendar.status.vacant_with_campaign'); break;
            case 'vac': statusText = t('calendar.status.vacant'); break;
            default: statusText = '';
        }
        $panel.find('[data-field="status"]').text(statusText);
        $panel.data('room-id', roomId);
        $panel.data('date', date);
    }
});

$(document).on('click', '#mb-cal-sidepanel [data-action="assign"]', function(){
    var $panel = $('#mb-cal-sidepanel');
    var roomId = $panel.data('room-id');
    if (!roomId) return;
    $(document).trigger('mb:open-assign-modal', { room_id: roomId });
});

$(document).on('click', '#mb-cal-sidepanel [data-action="cleaning"]', function(){
    var $panel = $('#mb-cal-sidepanel');
    var roomId = $panel.data('room-id');
    if (!roomId || !window.monthlyBookingAdmin) return;
    var payload = {
        action: 'toggle_cleaning_status',
        nonce: monthlyBookingAdmin.nonce,
        room_id: roomId
    };
    jQuery.post(monthlyBookingAdmin.ajaxurl, payload).done(function(resp){
        if (resp && resp.success) {
            var $sel = jQuery('.calendar-matrix .matrix-cell.day.is-selected');
            if ($sel.length) {
                var code = $sel.data('code');
                if (code === 'clean') {
                    $sel.attr('data-code','vac').data('code','vac').find('.mb-cal-symbol').text('○').attr('class','mb-cal-symbol mb-cal-vac');
                }
            }
        }
    });
});

$(document).on('click', '.calendar-matrix .matrix-row .row-actions [data-action="assign"]', function(){
    var roomId = jQuery(this).closest('.matrix-row').data('room-id');
    if (!roomId) return;
    jQuery(document).trigger('mb:open-assign-modal', { room_id: roomId });
});

$(document).on('click', '.calendar-matrix .matrix-row .row-actions [data-action="cleaning"]', function(){
    var roomId = jQuery(this).closest('.matrix-row').data('room-id');
    if (!roomId || !window.monthlyBookingAdmin) return;
    var payload = {
        action: 'toggle_cleaning_status',
        nonce: monthlyBookingAdmin.nonce,
        room_id: roomId
    };
    jQuery.post(monthlyBookingAdmin.ajaxurl, payload);
});


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
