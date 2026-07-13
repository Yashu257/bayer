/**
 * countdown.js — Live event countdown timer.
 *
 * Reads window.COUNTDOWN_SECONDS (injected by the layout via $inlineScript).
 * Updates four DOM nodes every second: #cd-days, #cd-hours, #cd-minutes, #cd-seconds.
 * Automatically hides the timer block and reloads when the count reaches zero.
 *
 * No framework dependencies. No jQuery.
 */
(function () {
    'use strict';

    // --- Config --------------------------------------------------------------

    const RELOAD_ON_ZERO = true;   // Reload page when countdown hits 0 (event starts)
    const RELOAD_DELAY_MS = 3000;  // Wait 3 s before reloading so user sees "Starting…"

    // --- DOM references ------------------------------------------------------

    const timerEl   = document.getElementById('countdownTimer');
    const daysEl    = document.getElementById('cd-days');
    const hoursEl   = document.getElementById('cd-hours');
    const minutesEl = document.getElementById('cd-minutes');
    const secondsEl = document.getElementById('cd-seconds');

    // Abort if timer elements are not present (event already started/ended)
    if (!timerEl || !daysEl || !hoursEl || !minutesEl || !secondsEl) {
        return;
    }

    // --- State ---------------------------------------------------------------

    /** Remaining seconds — decremented every tick. */
    let remaining = parseInt(window.COUNTDOWN_SECONDS ?? 0, 10);

    if (isNaN(remaining) || remaining <= 0) {
        handleZero();
        return;
    }

    // --- Render helpers ------------------------------------------------------

    /**
     * Zero-pad a number to at least two digits.
     * @param {number} n
     * @returns {string}
     */
    function pad(n) {
        return String(Math.max(0, n)).padStart(2, '0');
    }

    /**
     * Decompose total remaining seconds into d/h/m/s components.
     * @param {number} total  Remaining seconds (integer ≥ 0)
     * @returns {{ days: number, hours: number, minutes: number, seconds: number }}
     */
    function decompose(total) {
        const days    = Math.floor(total / 86400);
        const hours   = Math.floor((total % 86400) / 3600);
        const minutes = Math.floor((total % 3600) / 60);
        const seconds = total % 60;
        return { days, hours, minutes, seconds };
    }

    /**
     * Write current decomposed values to the DOM.
     * Skips update if the document is hidden (saves CPU on backgrounded tabs).
     */
    function render() {
        if (document.hidden) return;

        const { days, hours, minutes, seconds } = decompose(remaining);

        daysEl.textContent    = pad(days);
        hoursEl.textContent   = pad(hours);
        minutesEl.textContent = pad(minutes);
        secondsEl.textContent = pad(seconds);

        // Update ARIA live region label for screen readers (once per minute)
        if (seconds === 0) {
            timerEl.setAttribute(
                'aria-label',
                `Event starts in ${days} days, ${hours} hours, ${minutes} minutes`
            );
        }
    }

    // --- Zero handler --------------------------------------------------------

    function handleZero() {
        if (daysEl)    daysEl.textContent    = '00';
        if (hoursEl)   hoursEl.textContent   = '00';
        if (minutesEl) minutesEl.textContent = '00';
        if (secondsEl) secondsEl.textContent = '00';

        if (timerEl) {
            timerEl.classList.add('countdown-finished');
        }

        // Replace "Event starts in" label with "Starting…"
        const labelEl = timerEl?.querySelector('p');
        if (labelEl) {
            labelEl.textContent = 'Event is starting…';
        }

        if (RELOAD_ON_ZERO) {
            setTimeout(function () {
                window.location.reload();
            }, RELOAD_DELAY_MS);
        }
    }

    // --- Tick ----------------------------------------------------------------

    /**
     * Main interval callback. Decrements remaining and either renders or
     * terminates the timer.
     */
    function tick() {
        remaining -= 1;

        if (remaining <= 0) {
            clearInterval(intervalId);
            handleZero();
            return;
        }

        render();
    }

    // --- Navbar scroll effect ------------------------------------------------

    (function initNavScroll() {
        const nav = document.getElementById('mainNav');
        if (!nav) return;

        function onScroll() {
            if (window.scrollY > 60) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        }

        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll(); // Run once on load in case page is pre-scrolled
    })();

    // --- Smooth-scroll for in-page anchor links ------------------------------

    (function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (!target) return;
                e.preventDefault();

                const navH   = document.getElementById('mainNav')?.offsetHeight ?? 72;
                const top    = target.getBoundingClientRect().top + window.scrollY - navH - 16;

                window.scrollTo({ top, behavior: 'smooth' });

                // Close mobile navbar collapse if open
                const collapse = document.getElementById('navbarContent');
                if (collapse && collapse.classList.contains('show')) {
                    const bsCollapse = window.bootstrap?.Collapse?.getInstance(collapse);
                    bsCollapse?.hide();
                }
            });
        });
    })();

    // --- Init ----------------------------------------------------------------

    render();                                      // Paint immediately (no 1 s blank)
    const intervalId = setInterval(tick, 1000);   // Decrement every second

    // Pause/resume on tab visibility change to avoid drift
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            render(); // Re-sync display when tab becomes visible
        }
    });

})();
