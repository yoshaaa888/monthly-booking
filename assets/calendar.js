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
        const $calendar = $('.monthly-booking-calendar');
        if ($calendar.length === 0) return;
        
        $('.calendar-header h3').text(monthNames[month] + ' ' + year);
        
        announceMonthChange(year, month);
        
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
                action: 'mbp_load_calendar',
                month: month + 1,
                year: year,
                nonce: monthlyBookingAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateCalendarWithBookings(response.data);
                }
            },
            error: function() {
                console.log('Failed to load booking data');
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
        
        const calendarGrid = document.querySelector('.calendar-grid');
        console.log('Calendar grid found:', calendarGrid);
        if (calendarGrid) {
            calendarGrid.addEventListener('keydown', function(e) {
                const currentCell = e.target;
                if (!currentCell.classList.contains('calendar-day') || currentCell.getAttribute('tabindex') !== '0') {
                    return;
                }
                
                const allCells = Array.from(calendarGrid.querySelectorAll('.calendar-day:not(.other-month)'));
                const currentIndex = allCells.indexOf(currentCell);
                let targetIndex = -1;
                
                switch(e.key) {
                    case 'ArrowRight':
                        e.preventDefault();
                        targetIndex = Math.min(currentIndex + 1, allCells.length - 1);
                        break;
                    case 'ArrowLeft':
                        e.preventDefault();
                        targetIndex = Math.max(currentIndex - 1, 0);
                        break;
                    case 'ArrowDown':
                        e.preventDefault();
                        targetIndex = Math.min(currentIndex + 7, allCells.length - 1);
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        targetIndex = Math.max(currentIndex - 7, 0);
                        break;
                    case 'Home':
                        e.preventDefault();
                        targetIndex = 0;
                        break;
                    case 'End':
                        e.preventDefault();
                        targetIndex = allCells.length - 1;
                        break;
                    case 'PageDown':
                        e.preventDefault();
                        handleMonthNavigation('next', currentIndex);
                        return;
                    case 'PageUp':
                        e.preventDefault();
                        handleMonthNavigation('prev', currentIndex);
                        return;
                    case 'Enter':
                    case ' ':
                        e.preventDefault();
                        currentCell.click();
                        break;
                    case 'Tab':
                        return;
                    default:
                        return;
                }
                
                if (targetIndex >= 0 && allCells[targetIndex]) {
                    const targetCell = allCells[targetIndex];
                    setRovingTabindex(targetCell);
                    targetCell.focus();
                }
            });
        }
        
        function handleMonthNavigation(direction, currentIndex) {
            const currentColumn = currentIndex % 7;
            const navButton = document.querySelector(direction === 'next' ? '.calendar-next' : '.calendar-prev');
            
            if (navButton) {
                navButton.click();
                setTimeout(() => {
                    const calendarGrid = document.querySelector('.calendar-grid');
                    if (calendarGrid) {
                        const newCells = Array.from(calendarGrid.querySelectorAll('.calendar-day:not(.other-month)'));
                        const targetCell = newCells[Math.min(currentColumn, newCells.length - 1)];
                        if (targetCell) {
                            setRovingTabindex(targetCell);
                            targetCell.focus();
                        }
                    }
                }, 150);
            }
        }
        
        function setRovingTabindex(activeCell) {
            const allCells = document.querySelectorAll('.calendar-day:not(.other-month)');
            allCells.forEach(cell => {
                cell.setAttribute('tabindex', '-1');
            });
            activeCell.setAttribute('tabindex', '0');
        }
    }
    
    let lastAnnouncementTime = 0;
    const ANNOUNCEMENT_THROTTLE = 500; // 500ms throttle
    
    function announceMonthChange(year, month) {
        const now = Date.now();
        if (now - lastAnnouncementTime < ANNOUNCEMENT_THROTTLE) {
            return;
        }
        lastAnnouncementTime = now;
        
        const liveRegion = document.getElementById('calendar-announcements');
        if (liveRegion) {
            const monthName = monthNames[month];
            const announcement = `${year}年${monthName}を表示`;
            liveRegion.textContent = announcement;
        }
    }
    
    if ($('.monthly-booking-calendar').length) {
        initCalendar();
        initTooltips();
        
        setTimeout(() => {
            const firstCell = document.querySelector('.calendar-day[tabindex="0"]');
            if (firstCell) {
                setRovingTabindex(firstCell);
            }
        }, 100);
    }
    
    function initTooltips() {
        const calendarGrid = document.querySelector('.calendar-grid');
        if (!calendarGrid) return;
        
        let currentTooltip = null;
        let currentFocusedElement = null;
        
        calendarGrid.addEventListener('mouseenter', function(e) {
            const target = e.target.closest('.calendar-day[aria-describedby]');
            if (target) {
                showTooltip(target);
            }
        }, true);
        
        calendarGrid.addEventListener('mouseleave', function(e) {
            const target = e.target.closest('.calendar-day[aria-describedby]');
            if (target) {
                hideTooltip(target);
            }
        }, true);
        
        calendarGrid.addEventListener('focus', function(e) {
            const target = e.target.closest('.calendar-day[aria-describedby]');
            if (target) {
                currentFocusedElement = target;
                showTooltip(target);
            }
        }, true);
        
        calendarGrid.addEventListener('blur', function(e) {
            const target = e.target.closest('.calendar-day[aria-describedby]');
            if (target) {
                hideTooltip(target);
                currentFocusedElement = null;
            }
        }, true);
        
        calendarGrid.addEventListener('touchstart', function(e) {
            const target = e.target.closest('.calendar-day[aria-describedby]');
            if (target) {
                e.preventDefault(); // Prevent mouse events
                showTooltip(target);
            }
        }, true);
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && currentTooltip) {
                hideAllTooltips();
                if (currentFocusedElement) {
                    currentFocusedElement.focus();
                }
            }
        });
        
        function showTooltip(dayElement) {
            hideAllTooltips();
            
            const tooltipId = dayElement.getAttribute('aria-describedby');
            const tooltip = document.getElementById(tooltipId);
            if (tooltip) {
                tooltip.removeAttribute('aria-hidden');
                tooltip.style.display = 'block';
                currentTooltip = tooltip;
            }
        }
        
        function hideTooltip(dayElement) {
            const tooltipId = dayElement.getAttribute('aria-describedby');
            const tooltip = document.getElementById(tooltipId);
            if (tooltip) {
                tooltip.setAttribute('aria-hidden', 'true');
                tooltip.style.display = 'none';
                if (currentTooltip === tooltip) {
                    currentTooltip = null;
                }
            }
        }
        
        function hideAllTooltips() {
            const allTooltips = calendarGrid.querySelectorAll('.campaign-tooltip');
            allTooltips.forEach(tooltip => {
                tooltip.setAttribute('aria-hidden', 'true');
                tooltip.style.display = 'none';
            });
            currentTooltip = null;
        }
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
