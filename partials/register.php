<html>

<head>
    <meta charset="utf-8">
    <title>EVENT MANAGEMENT: REGISTER</title>
    <script src="<?php echo $adminBaseUrl ?>js/jquery.min.js"></script>
    <script src="<?php echo $adminBaseUrl ?>js/sweetalert.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo $adminBaseUrl ?>css/style.css">
    <style>
        * {
            padding: 0;
            margin: 0;
            color: #1a1f36;
            box-sizing: border-box;
            word-wrap: break-word;
            font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Ubuntu, sans-serif;
        }

        body {
            min-height: 100%;
            background-image: url('img/e_image.jpg'); /* Path to your background image */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        h1 {
            letter-spacing: -1px;
        }

        a {
            color: #5469d4;
            text-decoration: unset;
        }

        .login-root {
            background: rgba(255, 255, 255, 0.8); /* Adding a slight white overlay to the content area */
            display: flex;
            width: 100%;
            min-height: 100vh;
            overflow: hidden;
            justify-content: center;
            align-items: center;
        }

        .formbg {
            width: 100%;
            max-width: 448px;
            background: white;
            border-radius: 8px;
            box-shadow: rgba(60, 66, 87, 0.12) 0px 7px 14px 0px, rgba(0, 0, 0, 0.12) 0px 3px 6px 0px;
            padding: 30px;
            box-sizing: border-box;
        }

        h3 {
            color: #5469d4;
            font-size: 24px;
            margin-bottom: 24px;
        }

        label {
            font-size: 16px;
            color: #333;
            font-weight: bold;
            margin-bottom: 8px;
            display: block;
        }

        .field {
            margin-bottom: 20px;
        }

        .field input {
            font-size: 16px;
            padding: 12px 16px;
            width: 100%;
            min-height: 44px;
            border: 2px solid #dcdfe6;
            border-radius: 4px;
            outline: none;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease-in-out;
        }

        .field input:focus {
            border-color: #5469d4; /* Change border color on focus */
        }

        input[type="submit"] {
            background-color: rgb(84, 105, 212);
            color: white;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            padding: 14px;
            border-radius: 4px;
            border: none;
            transition: background-color 0.3s ease-in-out;
        }

        input[type="submit"]:hover {
            background-color: #3b50b5; /* Darken the button on hover */
        }

        .field-checkbox input {
            width: 20px;
            height: 15px;
            margin-right: 5px;
            box-shadow: unset;
            min-height: unset;
        }

        .field-checkbox label {
            display: flex;
            align-items: center;
            margin: 0;
        }

        .footer-link span {
            font-size: 14px;
            text-align: center;
        }

        .listing a {
            color: #697386;
            font-weight: 600;
            margin: 0 10px;
        }

        .reset-pass a {
            text-align: right;
            margin-top: 10px;
            font-size: 14px;
            color: #5469d4;
        }
    </style>
</head>

<body>
    <div class="login-root">
        <div class="formbg">
            <center>
                <h3><a href="#" rel="dofollow">EVENT MANAGEMENT REGISTER</a></h3>
            </center>
            <form id="register-form" action="<?= $apiUrl ?>">
                <div class="field">
                    <label for="fullname">Full Name</label>
                    <input type="text" name="fullname" required>
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="field">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <div class="field">
                    <label for="phone">Phone</label>
                    <input type="text" name="phone" required>
                </div>
                <div class="field">
                    <label for="address">Address</label>
                    <input type="text" name="address" required>
                </div>
                <div class="field">
                    <input type="submit" name="submit" value="Register">
                </div>
                
                <div class="field">
                    <a href="./" class="btn btn-outline-primary w-100">Back to Home</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    $(document).ready(function () {
        $("#register-form").submit(function (e) {
            e.preventDefault();
            const password = $("input[name='password']").val();
            const confirmPassword = $("input[name='confirm_password']").val();

            if (password !== confirmPassword) {
                swal({
                    title: 'Password Mismatch',
                    text: 'Password and Confirm Password must match!',
                    icon: 'warning',
                    confirmButtonText: 'Ok'
                });
                return;
            }

            var form = $(this);
            var url = form.attr('action');
            var data = form.serialize();
            data += '&mode=register'; // adjust as per your API mode

            $.ajax({
                type: "POST",
                url: url,
                data: data,
                success: function (data) {
                    if (data.error.code == '#200') {
                        swal({
                            title: 'Success',
                            text: "Registered successfully",
                            icon: 'success',
                            confirmButtonText: 'Ok'
                        }).then(() => {
                            window.location.href = './'; // redirect after success
                        });
                    } else {
                        swal({
                            title: 'Error',
                            text: data.error.description,
                            icon: 'error',
                            confirmButtonText: 'Ok'
                        });
                    }
                }
            });
        });
    });
    </script>

</body>

</html>
