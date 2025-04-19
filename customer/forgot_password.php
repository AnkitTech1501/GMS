<?php
session_start();
include('dbcon.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

$feedback = "";

if (isset($_POST['reset'])) {
    $user_input = mysqli_real_escape_string($con, $_POST['user_input']);

    // Try finding user by username or email
    $admin_exists = mysqli_query(
        $con,
        "SELECT EMAIL_ID FROM members WHERE username = '$user_input' OR EMAIL_ID = '$user_input'"
    );

    if (mysqli_num_rows($admin_exists) > 0) {
        $row = mysqli_fetch_assoc($admin_exists);
        $email = $row['EMAIL_ID'];

        $token = bin2hex(random_bytes(32)); // secure random token
        $insert = mysqli_query(
            $con,
            "INSERT INTO password_resets (email, token, role) VALUES ('$email', '$token', 'MEMBERS')"
        );

        if ($insert) {
            $resetLink = "http://localhost:8080/Gym-System/customer/reset_password.php?token=$token";

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'ankitdubey91377@gmail.com';
                $mail->Password   = 'hrqh vlde jlao xbpi'; // Use App Password
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('ankitdubey91377@gmail.com', 'Gym System');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body    = "Click the link below to reset your password:<br><br>
                                 <a href='$resetLink'>$resetLink</a>";

                // Optional: Enable debugging
                // $mail->SMTPDebug = 2;
                // $mail->Debugoutput = 'html';

                $mail->send();
                $feedback = "<div class='alert alert-success'>Password reset link sent to <strong>$email</strong>.</div>";
            } catch (Exception $e) {
                $errorLog = "Mailer Error: {$mail->ErrorInfo}\n";
                file_put_contents("CustomerEmaillog.txt", $errorLog, FILE_APPEND);
                $feedback = "<div class='alert alert-danger'>Error sending email: {$mail->ErrorInfo}</div>";
            }
        } else {
            $feedback = "<div class='alert alert-danger'>Database error while saving token.</div>";
        }
    } else {
        $feedback = "<div class='alert alert-danger'>No user found with that username or email.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Gym System Admin - Forgot Password</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="css/matrix-style.css" />
    <link rel="stylesheet" href="css/matrix-login.css" />
    <link href="../font-awesome/css/fontawesome.css" rel="stylesheet" />
    <link href="../font-awesome/css/all.css" rel="stylesheet" />
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
                        <span class="add-on bg_lg"><i class="fas fa-user-circle"></i></span>
                        <input type="text" name="user_input" placeholder="Enter your Username or Email" required />
                    </div>
                </div>
            </div>
            <div class="form-actions center">
                <button type="submit" class="btn btn-block btn-large btn-info" title="Reset Password" name="reset">
                    Send Reset link to email
                </button>
            </div>
        </form>

        <?php if (!empty($feedback)) echo $feedback; ?>
    </div>

    <script src="js/jquery.min.js"></script>
    <script src="js/matrix.login.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/matrix.js"></script>
</body>

</html>