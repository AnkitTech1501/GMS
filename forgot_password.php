<?php session_start();
include('dbcon.php'); ?>
<!DOCTYPE html>
<html lang="en">
<!-- Visit BST for more projects -->

<head>
    <title>Gym System Admin</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="css/matrix-style.css" />
    <link rel="stylesheet" href="css/matrix-login.css" />
    <link href="font-awesome/css/fontawesome.css" rel="stylesheet" />
    <link href="font-awesome/css/all.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>

</head>

<body>

    <div id="loginbox">
        <form id="loginform" method="POST" class="form-vertical" action="">
            <div class="control-group normal_text">
                <h3><img src="img/icontest3.png" alt="Logo" /></h3>
            </div>
            <div class="control-group">
                <div class="controls">
                    <div class="main_input_box">
                        <span class="add-on bg_lg"><i class="fas fa-user-circle"></i></span><input type="text" name="user_input" placeholder="Enter your Username" required />
                    </div>
                </div>
            </div>
            <div class="form-actions center">
                <!-- <span class="pull-right"><a type="submit" href="index.html" class="btn btn-success" /> Login</a></span> -->
                <!-- <input type="submit" class="button" title="Log In" name="login" value="Admin Login"></input> -->
                <button type="submit" class="btn btn-block btn-large btn-info" title="Reset Password" name="reset">Send Reset link to email</button>
            </div>


        </form>
        <?php

        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;

        require 'vendor/autoload.php';
        if (isset($_POST['reset'])) {
            $user_input = mysqli_real_escape_string($con, $_POST['user_input']);

            // Search in Admin table
            $admin_exists = mysqli_query($con, "SELECT EMAIL_ID FROM admin WHERE username = '$user_input'");

            if (mysqli_num_rows($admin_exists) > 0) {
                $row = mysqli_fetch_assoc($admin_exists);
                $email = $row['EMAIL_ID'];

                $token = bin2hex(random_bytes(32)); // secure random token
                $insert = mysqli_query($con, "INSERT INTO password_resets (email, token, role) VALUES ('$email', '$token', 'ADMIN')");

                if ($insert) {
                    $resetLink = "http://localhost:8080/Gym-System/reset_password.php?token=$token";

                    // Send email with PHPMailer
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'ankitdubey91377@gmail.com';
                        $mail->Password   = 'hrqh vlde jlao xbpi';
                        $mail->SMTPSecure = 'tls';
                        $mail->Port       = 587;

                        $mail->setFrom('ankitdubey@email.com', 'Gym System');
                        $mail->addAddress($email);
                        $mail->isHTML(true);
                        $mail->Subject = 'Password Reset Request';
                        $mail->Body    = "Click the link below to reset your password:<br><br><a href='$resetLink'>$resetLink</a>";

                        $mail->send();
                        echo "<div class='alert alert-success'>Password reset link sent to your email.</div>";
                    } catch (Exception $e) {
                        echo "<div class='alert alert-danger'>Message could not be sent. Mailer Error: {$mail->ErrorInfo}</div>";
                    }
                }
            } else {
                echo "<div class='alert alert-danger'>No user found with that email address.</div>";
            }
        }
        ?>

    </div>

    <script src="js/jquery.min.js"></script>
    <script src="js/matrix.login.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/matrix.js"></script>
</body>
<!-- Visit BST for more projects -->

</html>