<html>

<head>
    <meta charset="utf-8">
    <title>Xpert Event Register</title>
    <script src="<?php echo $adminBaseUrl ?>js/jquery.min.js"></script>
    <script src="<?php echo $adminBaseUrl ?>js/sweetalert.js"></script>
    <link rel="stylesheet" type="text/css" href="style.css">
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
            background-color: #ffffff;
        }

        h1 {
            letter-spacing: -1px;
        }

        a {
            color: #5469d4;
            text-decoration: unset;
        }

        .login-root {
            background-image: url('img/bgimage.jpg'); /* Replace with your image path */
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }


        .loginbackground {
            min-height: 692px;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            top: 0;
            z-index: 0;
            overflow: hidden;
        }

        .flex-flex {
            display: flex;
        }

        .align-center {
            align-items: center;
        }

        .center-center {
            align-items: center;
            justify-content: center;
        }

        .box-root {
            box-sizing: border-box;
        }

        .flex-direction--column {
            -ms-flex-direction: column;
            flex-direction: column;
        }

        .loginbackground-gridContainer {
            display: -ms-grid;
            display: grid;
            justify-content: center;
            margin: 0 -2%;
            transform: rotate(-12deg) skew(-12deg);
        }

        .box-divider--light-all-2 {
            box-shadow: inset 0 0 0 2px #e3e8ee;
        }

        .box-background--blue {
            background-color: #5469d4;
        }

        .box-background--white {
            background-color: #ffffff;
        }

        .box-background--blue800 {
            background-color: #212d63;
        }

        .box-background--gray100 {
            background-color: #e3e8ee;
        }

        .box-background--cyan200 {
            background-color: #7fd3ed;
        }

        .padding-top--64 {
            padding-top: 64px;
        }

        .padding-top--24 {
            padding-top: 24px;
        }

        .padding-top--48 {
            padding-top: 48px;
        }

        .padding-bottom--24 {
            padding-bottom: 24px;
        }

        .padding-horizontal--48 {
            padding: 48px;
        }

        .padding-bottom--15 {
            padding-bottom: 15px;
        }


        .flex-justifyContent--center {
            -ms-flex-pack: center;
            justify-content: center;
        }

        .formbg {
            margin: 0px auto;
            width: 100%;
            max-width: 448px;
            background: white;
            border-radius: 4px;
            box-shadow: rgba(60, 66, 87, 0.12) 0px 7px 14px 0px, rgba(0, 0, 0, 0.12) 0px 3px 6px 0px;
        }

        span {
            display: block;
            font-size: 20px;
            line-height: 28px;
            color: #1a1f36;
        }

        label {
            margin-bottom: 10px;
        }

        .reset-pass a,
        label {
            font-size: 14px;
            font-weight: 600;
            display: block;
        }

        .reset-pass>a {
            text-align: right;
            margin-bottom: 10px;
        }

        .grid--50-50 {
            display: grid;
            grid-template-columns: 50% 50%;
            align-items: center;
        }

        .field input {
            font-size: 16px;
            line-height: 28px;
            padding: 8px 16px;
            width: 100%;
            min-height: 44px;
            border: unset;
            border-radius: 4px;
            outline-color: rgb(84 105 212 / 0.5);
            background-color: rgb(255, 255, 255);
            box-shadow: rgba(0, 0, 0, 0) 0px 0px 0px 0px,
                rgba(0, 0, 0, 0) 0px 0px 0px 0px,
                rgba(0, 0, 0, 0) 0px 0px 0px 0px,
                rgba(60, 66, 87, 0.16) 0px 0px 0px 1px,
                rgba(0, 0, 0, 0) 0px 0px 0px 0px,
                rgba(0, 0, 0, 0) 0px 0px 0px 0px,
                rgba(0, 0, 0, 0) 0px 0px 0px 0px;
        }

        input[type="submit"] {
            background-color: rgb(84, 105, 212);
            box-shadow: rgba(0, 0, 0, 0) 0px 0px 0px 0px,
                rgba(0, 0, 0, 0) 0px 0px 0px 0px,
                rgba(0, 0, 0, 0.12) 0px 1px 1px 0px,
                rgb(84, 105, 212) 0px 0px 0px 1px,
                rgba(0, 0, 0, 0) 0px 0px 0px 0px,
                rgba(0, 0, 0, 0) 0px 0px 0px 0px,
                rgba(60, 66, 87, 0.08) 0px 2px 5px 0px;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
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

        a.ssolink {
            display: block;
            text-align: center;
            font-weight: 600;
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

        .animationRightLeft {
            animation: animationRightLeft 2s ease-in-out infinite;
        }

        .animationLeftRight {
            animation: animationLeftRight 2s ease-in-out infinite;
        }

        .tans3s {
            animation: animationLeftRight 3s ease-in-out infinite;
        }

        .tans4s {
            animation: animationLeftRight 4s ease-in-out infinite;
        }

        @keyframes animationLeftRight {
            0% {
                transform: translateX(0px);
            }

            50% {
                transform: translateX(1000px);
            }

            100% {
                transform: translateX(0px);
            }
        }

        @keyframes animationRightLeft {
            0% {
                transform: translateX(0px);
            }

            50% {
                transform: translateX(-1000px);
            }

            100% {
                transform: translateX(0px);
            }
        }
    </style>
</head>

<body>
    <div class="login-root">
        <div class="box-root flex-flex flex-direction--column" style="min-height: 100vh;flex-grow: 1;">
            <div class="box-root padding-top--24 flex-flex flex-direction--column" style="flex-grow: 1; z-index: 9;">
                <div class="formbg-outer">
                    <div class="formbg">
                        <div class="formbg-inner padding-horizontal--48">
                            <center>
                                <h2>Xpert Event</h2><br>
                                <h3><a href="#" rel="dofollow">Registration Form</a></h3>
                            </center>
                            <br /><br>
                            <form id="register-form" action="<?= $apiUrl ?>">
                                <div class="field padding-bottom--24">
                                    <label for="fullname">Full Name</label>
                                    <input type="text" name="fullname" required>
                                </div>
                                <div class="field padding-bottom--24">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" required>
                                </div>
                                <div class="field padding-bottom--24">
                                    <label for="password">Password</label>
                                    <input type="password" name="password" required>
                                </div>
                                <div class="field padding-bottom--24">
                                    <label for="confirm_password">Confirm Password</label>
                                    <input type="password" name="confirm_password" required>
                                </div>
                                <div class="field padding-bottom--24">
                                    <label for="phone">Phone</label>
                                    <input type="text" name="phone" required>
                                </div>
                                <div class="field padding-bottom--24">
                                    <label for="address">Address</label>
                                    <input type="text" name="address" required>
                                </div>
                                <div class="field padding-bottom--24">
                                    <input type="submit" name="submit" value="Register">
                                </div>
                                
                                <div class="field">
                                    <a href="./" class="btn btn-outline-primary w-100">Back to Home</a>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
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