<?php
/**
 * Partial: Site Footer
 */
$year = date('Y');
?>
<footer class="site-footer bg-dark text-white-50 py-4 mt-auto">
    <div class="container">
        <div class="row gy-3 align-items-center">

            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 small">
                    &copy; <?= $year ?> PharmaWebcast. All rights reserved.
                </p>
            </div>

            <div class="col-md-6 text-center text-md-end">
                <ul class="list-inline mb-0 small">
                    <li class="list-inline-item">
                        <a href="/privacy" class="text-white-50 text-decoration-none">Privacy Policy</a>
                    </li>
                    <li class="list-inline-item ms-2">
                        <a href="/terms" class="text-white-50 text-decoration-none">Terms of Use</a>
                    </li>
                    <li class="list-inline-item ms-2">
                        <a href="/support" class="text-white-50 text-decoration-none">Support</a>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</footer>
