/**
 * app.js — Global JS loaded on every page.
 * Lightweight — only includes behaviour needed on all routes.
 */
(function () {
    'use strict';

    // Auto-dismiss Bootstrap alerts after 5 s
    document.querySelectorAll('.alert.alert-dismissible').forEach(function (el) {
        setTimeout(function () {
            const bsAlert = window.bootstrap?.Alert?.getOrCreateInstance(el);
            bsAlert?.close();
        }, 5000);
    });

})();
