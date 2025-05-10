<html>

<head>
    <meta charset="utf-8">
    <title>Xpert Event: Guest</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="<?php echo $adminBaseUrl ?>js/jquery.min.js"></script>
    <script src="<?php echo $adminBaseUrl ?>js/sweetalert.js"></script>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        body {
            background: #f9f9f9;
            font-family: Arial, sans-serif;
        }

        .thankyou-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 40px;
            background: #fff;
            text-align: center;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }

        h2 {
            color: #333;
        }

    </style>
</head>

<body>
    <div class="thankyou-container">
        <h2>Thanks for responding!</h2>
        <p>Your RSVP has been recorded successfully.</p>
    </div>
    <form id="rsvpForm" method="POST" action="<?php echo $adminBaseUrl ?>rsvp_attend">
        <!-- No visible inputs needed -->
        <input type="hidden" name="booking_id" id="booking_id" value="<?= htmlspecialchars($_REQUEST['booking_id']) ?>">
        <input type="hidden" name="guest_id" id="guest_id" value="<?= htmlspecialchars($_REQUEST['guest_id']) ?>">
        <input type="hidden" name="mode" value="rsvp_attend">
    </form>

    <script>
        $(document).ready(function () {

        var formdata = new FormData();
        formdata.append('mode', 'rsvp_attend');
        formdata.append('booking_id', $('#booking_id').val());
        formdata.append('guest_id', $('#guest_id').val());
        $.ajax({
            url: "<?= $apiUrl ?>",
            cache: false,
            beforeSend: function(xhr) {
                xhr.setRequestHeader("Cache-Control", "no-cache");
                xhr.setRequestHeader("pragma", "no-cache");
            },
            dataType: "json",
            processData: false,
            contentType: false,
            type: 'POST',
            data: formdata,
            success: function(data) {
                if (data.error.code == '#200') {
                    swal({
                        title: 'Success!',
                        icon: 'success',
                        text: "Status Updated successfully",
                        confirmButtonText: 'Ok'
                    }).then((result) => {
                        
                    });
                }
            }
        });
        });
    </script>
</body>

</html>
