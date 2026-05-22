/* SmartStock – Main JS */
(function () {
    'use strict';

    // ---- Sidebar toggle (mobile) ----
    const sidebar  = document.getElementById('sidebar');
    const toggle   = document.getElementById('sidebarToggle');
    const overlay  = document.getElementById('sidebarOverlay');

    function openSidebar() {
        sidebar?.classList.add('open');
        overlay?.classList.add('show');
    }
    function closeSidebar() {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('show');
    }

    toggle?.addEventListener('click', openSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // ---- Auto-dismiss alerts ----
    document.querySelectorAll('.ss-alert[data-auto-dismiss]').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        }, 4000);
    });

    // ---- Confirm delete ----
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) e.preventDefault();
        });
    });

    // ---- Animate stat values ----
    document.querySelectorAll('[data-count]').forEach(el => {
        const target = parseFloat(el.dataset.count) || 0;
        const isFloat = el.dataset.count.includes('.');
        const prefix  = el.dataset.prefix  || '';
        const suffix  = el.dataset.suffix  || '';
        const duration = 1000;
        const step = 16;
        const steps = duration / step;
        let current = 0;
        const increment = target / steps;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) { current = target; clearInterval(timer); }
            el.textContent = prefix + (isFloat ? current.toFixed(2) : Math.floor(current).toLocaleString()) + suffix;
        }, step);
    });

    // ---- Animate category bars ----
    document.querySelectorAll('.cat-bar-fill').forEach(el => {
        const w = el.style.width;
        el.style.width = '0';
        setTimeout(() => { el.style.width = w; }, 100);
    });

})();
