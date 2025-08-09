jQuery(document).ready(function($) {
    'use strict';
    
    let currentDate = new Date();
    let currentMonth = currentDate.getMonth();
    let currentYear = currentDate.getFullYear();
    
    const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    
    function initCalendar() {
        renderCalendar(currentMonth, currentYear);
        bindEvents();
    }
    
    function renderCalendar(month, year) {
        try {
            const $calendar = $('.monthly-booking-calendar');
            if ($calendar.length === 0) return;
            
            $('.calendar-header h3').text(monthNames[month] + ' ' + year);
            
            $('.calendar-grid').empty();
            
            dayNames.forEach(day => {
                $('.calendar-grid').append(`<div class="calendar-day-header">${day}</div>`);
            });
            
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const daysInPrevMonth = new Date(year, month, 0).getDate();
            
            for (let i = firstDay - 1; i >= 0; i--) {
                const day = daysInPrevMonth - i;
                $('.calendar-grid').append(createDayElement(day, true, year, month - 1));
            }
            
            for (let day = 1; day <= daysInMonth; day++) {
                $('.calendar-grid').append(createDayElement(day, false, year, month));
            }
            
            const totalCells = $('.calendar-grid .calendar-day').length + $('.calendar-grid .calendar-day-header').length;
            const remainingCells = 42 - (totalCells - 7); // 6 weeks * 7 days - headers
            
            for (let day = 1; day <= remainingCells; day++) {
                $('.calendar-grid').append(createDayElement(day, true, year, month + 1));
            }
            
            loadBookingData(month, year);
        } catch (error) {
            console.error('カレンダーの描画に失敗しました:', error);
            $('.calendar-grid').html('<div class="calendar-error-message" style="text-align: center; padding: 20px; color: #d63638;">カレンダーの表示でエラーが発生しました。</div>');
        }
    }
    
    function createDayElement(day, isOtherMonth, year, month) {
        const today = new Date();
        const isToday = !isOtherMonth && 
                       day === today.getDate() && 
                       month === today.getMonth() && 
                       year === today.getFullYear();
        
        const classes = ['calendar-day'];
        if (isOtherMonth) classes.push('other-month');
        if (isToday) classes.push('today');
        
        return `
            <div class="${classes.join(' ')}" data-date="${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}">
                <div class="day-number">${day}</div>
                <div class="day-bookings"></div>
            </div>
        `;
    }
    
    function loadBookingData(month, year) {
        if (typeof monthlyBookingAjax === 'undefined') return;
        
        $.ajax({
            url: monthlyBookingAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_calendar_bookings',
                month: month + 1,
                year: year,
                nonce: monthlyBookingAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateCalendarWithBookings(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('予約データの読み込みに失敗しました:', error);
                $('.calendar-grid').html('<div class="calendar-error-message" style="grid-column: 1 / -1; text-align: center; padding: 20px; color: #d63638; background: #fbeaea; border: 1px solid #d63638; border-radius: 4px; margin: 10px;">エラー：予約データの取得に失敗しました。ページを再読み込みしてください。</div>');
            }
        });
    }
    
    function updateCalendarWithBookings(bookings) {
        $('.calendar-day').removeClass('booked available cleaning');
        $('.day-bookings').empty();
        
        bookings.forEach(booking => {
            const $day = $(`.calendar-day[data-date="${booking.date}"]`);
            if ($day.length) {
                $day.addClass(booking.status);
                
                if (booking.guest_name) {
                    $day.find('.day-bookings').append(
                        `<div class="day-status status-${booking.status}">${booking.guest_name}</div>`
                    );
                } else {
                    $day.find('.day-bookings').append(
                        `<div class="day-status status-${booking.status}">${booking.status}</div>`
                    );
                }
            }
        });
    }
    
    function bindEvents() {
        $('.calendar-prev').on('click', function() {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            renderCalendar(currentMonth, currentYear);
        });
        
        $('.calendar-next').on('click', function() {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            renderCalendar(currentMonth, currentYear);
        });
        
        $(document).on('click', '.calendar-day:not(.other-month)', function() {
            const date = $(this).data('date');
            const $day = $(this);
            
            $('.calendar-day').removeClass('selected');
            $day.addClass('selected');
            
            $(document).trigger('monthlyBookingDaySelected', [date, $day]);
        });
        
        $(document).on('keydown', '.calendar-day[tabindex="0"]', function(e) {
            const $current = $(this);
            const $days = $('.calendar-day[tabindex="0"]:visible');
            const currentIndex = $days.index($current);
            let $target = null;
            
            switch(e.key) {
                case 'ArrowRight':
                    e.preventDefault();
                    $target = $days.eq(currentIndex + 1);
                    break;
                case 'ArrowLeft':
                    e.preventDefault();
                    $target = $days.eq(currentIndex - 1);
                    break;
                case 'ArrowDown':
                    e.preventDefault();
                    $target = $days.eq(currentIndex + 7);
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    $target = $days.eq(currentIndex - 7);
                    break;
                case 'Home':
                    e.preventDefault();
                    $target = $days.first();
                    break;
                case 'End':
                    e.preventDefault();
                    $target = $days.last();
                    break;
                case 'PageDown':
                    e.preventDefault();
                    $target = $days.eq(Math.min(currentIndex + 30, $days.length - 1));
                    break;
                case 'PageUp':
                    e.preventDefault();
                    $target = $days.eq(Math.max(currentIndex - 30, 0));
                    break;
                case 'Enter':
                case ' ':
                    e.preventDefault();
                    $current.trigger('click');
                    break;
            }
            
            if ($target && $target.length && $target.is(':visible')) {
                $target.focus();
            }
        });
    }
    
    if ($('.monthly-booking-calendar').length) {
        initCalendar();
    }
    
    window.MonthlyBookingCalendar = {
        refresh: function() {
            renderCalendar(currentMonth, currentYear);
        },
        goToMonth: function(month, year) {
            currentMonth = month;
            currentYear = year;
            renderCalendar(currentMonth, currentYear);
        }
    };
});
