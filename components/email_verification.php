<html>

<head>
    <meta charset="utf-8">
    <title>Xpert Event: Email Verfication</title>
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
        <h2>Thanks for verifying!</h2>
        <p>Your email has been successfully verified.</p>
    </div>
    <form id="emailForm" method="POST" action="<?php echo $adminBaseUrl ?>email_verification">
        <!-- No visible inputs needed -->
        <input type="hidden" name="user_id" id="user_id" value="<?= htmlspecialchars($_REQUEST['user_id']) ?>">
        <input type="hidden" name="mode" value="email_verification">
    </form>

    <script>
        $(document).ready(function () {

        var formdata = new FormData();
        formdata.append('mode', 'email_verification');
        formdata.append('user_id', $('#user_id').val());
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
                        text: "Email Verified Successfully",
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
