(function () {
    'use strict';

    // Password show/hide toggle
    document.querySelectorAll('.auth-password-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = btn.dataset.target;
            var input    = document.getElementById(targetId);
            var icon     = btn.querySelector('i');

            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
                btn.setAttribute('aria-label', 'Hide password');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
                btn.setAttribute('aria-label', 'Show password');
            }
        });
    });
})();
