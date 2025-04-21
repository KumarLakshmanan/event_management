<?php
if (!isset($_SESSION)) {
    session_start();
}
$today = date('Y-m-d');
?>
<div class="p-3"></div>
<div class="container">
    <div class="white-box">
        <div class="row">
            <div class="col-md-6 mb-2">
                <a href="<?= $adminBaseUrl ?>primarysettings" class="btn btn-primary w-100 py-3">
                    Primary Settings
                </a>
            </div>
            <div class="col-md-6 mb-2">
                <a href="<?= $adminBaseUrl ?>secondarysettings" class="btn btn-primary w-100 py-3">
                    Secondary Settings
                </a>
            </div>
            <div class="col-md-6 mb-2">
                <a href="<?= $adminBaseUrl ?>bannersettings" class="btn btn-primary w-100 py-3">
                    Banner Settings
                </a>
            </div>
            <div class="col-md-6 mb-2">
                <a href="<?= $adminBaseUrl ?>gallery" class="btn btn-primary w-100 py-3">
                    Gallery
                </a>
            </div>
            <div class="col-md-6 mb-2">
                <a href="<?= $adminBaseUrl ?>testimonialsettings" class="btn btn-primary w-100 py-3">
                    Testimonials
                </a>
            </div>
            <div class="col-md-6 mb-2">
                <a href="<?= $adminBaseUrl ?>teamsettings" class="btn btn-primary w-100 py-3">
                    Team members
                </a>
            </div>
            <div class="col-md-6 mb-2">
                <a href="<?= $adminBaseUrl ?>faqsettings" class="btn btn-primary w-100 py-3">
                    FAQ
                </a>
            </div>
            <div class="col-md-6 mb-2">
                <a href="<?= $adminBaseUrl ?>bookingsettings" class="btn btn-primary w-100 py-3">
                    Booking Settings
                </a>
            </div>
            <div class="col-md-6 mb-2">
                <a href="<?= $adminBaseUrl ?>featuresettings" class="btn btn-primary w-100 py-3">
                    Bus Features
                </a>
            </div>
            <div class="col-md-6 mb-2">
                <a href="<?= $adminBaseUrl ?>jubileesettings" class="btn btn-primary w-100 py-3">
                    Jubilee Settings
                </a>
            </div>
            <div class="col-md-6 mb-2">
                <a href="<?= $adminBaseUrl ?>feature_new_settings" class="btn btn-primary w-100 py-3">
                    Features We Provide
                </a>
            </div>
            <!-- <div class="col-md-6 mb-2">
                <a href="<?= $adminBaseUrl ?>chat_variables" class="btn btn-primary w-100 py-3">
                    Chat Variables
                </a>
            </div> -->
            <div class="col-md-6 mb-2">
                <a href="<?= $adminBaseUrl ?>suggestionbuttons" class="btn btn-primary w-100 py-3">
                    Chat Suggestion Button
                </a>
            </div>
            <div class="col-md-6 mb-2">
                <a href="<?= $adminBaseUrl ?>autoreplies" class="btn btn-primary w-100 py-3">
                    Chat Auto Replies
                </a>
            </div>
        </div>
    </div>
</div>