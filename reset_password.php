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
                        <span class="add-on bg_lg"><i class="fas fa-user-circle"></i></span><input type="text" name="new_password" placeholder="enter new password" required />
                    </div>
                </div>
            </div>
            <div class="form-actions center">
                <!-- <span class="pull-right"><a type="submit" href="index.html" class="btn btn-success" /> Login</a></span> -->
                <!-- <input type="submit" class="button" title="Log In" name="login" value="Admin Login"></input> -->
                <button type="submit" class="btn btn-block btn-large btn-info" title="Reset Password" name="reset">Reset Password</button>
            </div>


        </form>
        <?php
        if (!isset($_GET['token'])) {
            die("Invalid request.");
        }
        $token = mysqli_real_escape_string($con, $_GET['token']);
        $query = mysqli_query($con, "SELECT * FROM password_resets WHERE token='$token'");
        $data = mysqli_fetch_assoc($query);

        if (!$data) {
            die("Invalid or expired token.");
        }

        if (isset($_POST['reset'])) {
            $new_pass = md5(mysqli_real_escape_string($con, $_POST['new_password']));
            $email = $data['email'];

            $update = mysqli_query($con, "UPDATE admin SET password='$new_pass' WHERE EMAIL_ID='$email'");

            if ($update) {
                mysqli_query($con, "DELETE FROM password_resets WHERE email='$email'"); // Clean up
                echo "<script>alert('Password has been reset successfully.'); window.location='index.php';</script>";
            } else {
                echo "<div class='alert alert-danger'>Failed to reset password.</div>";
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