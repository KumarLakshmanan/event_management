<?php
return [
    '/' => 'partials/login.php',
    '/home' => 'partials/landing.php',
    '/register' => 'partials/register.php',
    '/rsvp_attend' => 'partials/mail/mail_read.php',
    '/rsvp_notattend' => 'partials/mail/mail_notread.php',
    
    '/services' => 'modules_view/services.php',
    '/addservice' => 'modules_view/services/add.php',
    '/editservice' => 'modules_view/services/edit.php',

    '/packages' => 'modules_view/packages.php',
    '/addpackage' => 'modules_view/packages/add.php',
    '/editpackage' => 'modules_view/packages/edit.php',

    '/managers' => 'modules_view/managers.php',
    '/addmanager' => 'modules_view/member/add.php',
    '/editmanager' => 'modules_view/member/edit.php',

    '/clients' => 'modules_view/clients.php',
    '/addclient' => 'modules_view/users/add.php',
    '/editclient' => 'modules_view/users/edit.php',

    '/explore_bundles' => 'modules_view/explore_bundles.php',
    '/addcustompackage' => 'modules_view/createcustompackage.php',
    '/booking' => 'modules_view/booking.php',
    '/editbooking' => 'modules_view/editbooking.php',
    '/clientbooking' => 'modules_view/clientbooking.php',
    '/editallbooking' => 'modules_view/editallbooking.php',

    '/delete' => 'modules_view/delete.php',
    '/all' => 'modules_view/all.php',
    '/enquirymessages' => 'modules_view/enquirymessages.php',
    '/allbooking' => 'modules_view/allbooking.php',
    '/logout' => 'partials/logout.php',
];
