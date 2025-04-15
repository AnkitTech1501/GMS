<!DOCTYPE html>
<html lang="en">

<head>
    <title>Gym System Admin</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../css/bootstrap.min.css" />
    <link rel="stylesheet" href="../css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="../css/fullcalendar.css" />
    <link rel="stylesheet" href="../css/matrix-style.css" />
    <link rel="stylesheet" href="../css/matrix-media.css" />
    <link href="../font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/jquery.gritter.css" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>
</head>

<body>


    <form role="form" action="index.php" method="POST">
        <?php
        include 'dbcon.php';
        function generateReferralCode($length = 6)
        {
            return strtoupper(substr(md5(uniqid(rand(), true)), 0, $length));
        }
        function distributeCommission($con, $referred_by_code, $new_user_id)
        {
            $percentages = [10, 5, 2];
            $total_given = 0;
            $max_commission = 20;
            $amount = 100; // base amount for commission

            $referral_chain = [];

            for ($level = 0; $level < 3 && $referred_by_code && $total_given < $max_commission; $level++) {
                $res = mysqli_query($con, "SELECT * FROM members WHERE referral_code = '$referred_by_code'");
                if (!$res || mysqli_num_rows($res) == 0) break;

                $referrer = mysqli_fetch_assoc($res);
                $referrer_id = $referrer['user_id'];
                $referrer_ref_code = $referrer['referral_code'];
                $referrer_earnings_json = $referrer['referral_earning'];

                $percent = $percentages[$level];
                $remaining = $max_commission - $total_given;
                $eligible_percent = min($percent, $remaining);
                $commission = ($amount * $eligible_percent) / 100;
                $total_given += $eligible_percent;

                // Decode existing earnings
                $earnings = json_decode($referrer_earnings_json, true);
                if (!is_array($earnings)) {
                    $earnings = [];
                }

                // Identify who this earning came through
                $through_user_id = ($level === 0) ? $new_user_id : $referral_chain[0];

                // Prepare nested earnings structure
                if (!isset($earnings[$through_user_id])) {
                    $earnings[$through_user_id] = [];
                }
                if (!isset($earnings[$through_user_id][$new_user_id])) {
                    $earnings[$through_user_id][$new_user_id] = 0;
                }
                $earnings[$through_user_id][$new_user_id] += $commission;

                // Save updated earnings JSON
                $updated_json = json_encode($earnings);
                mysqli_query($con, "UPDATE members SET referral_earning = '$updated_json' WHERE user_id = $referrer_id");

                // Store referrer in the chain
                array_unshift($referral_chain, $referrer_id);
                $referred_by_code = $referrer['referred_by'];
            }
        }

        if (isset($_POST['fullname'])) {
            $fullname = isset($_POST["fullname"]) ? trim($_POST["fullname"]) : '';
            $username = isset($_POST["username"]) ? trim($_POST["username"]) : '';
            $password = isset($_POST["password"]) ? trim($_POST["password"]) : '';
            $gender = isset($_POST["gender"]) ? trim($_POST["gender"]) : '';
            $services = isset($_POST["services"]) ? trim($_POST["services"]) : '';
            $plan = isset($_POST["plan"]) ? trim($_POST["plan"]) : '';
            $address = isset($_POST["address"]) ? trim($_POST["address"]) : '';
            $contact = isset($_POST["contact"]) ? trim($_POST["contact"]) : '';
            $referred_by = isset($_POST["referred_by"]) ? $_POST["referred_by"] : null;
            if (empty($fullname)) $errors[] = "Full Name is required.";
            if (empty($username)) $errors[] = "Username is required.";
            if (empty($password)) $errors[] = "Password is required.";
            if (empty($gender)) $errors[] = "Gender is required.";
            if (empty($services)) $errors[] = "Services selection is required.";
            if (empty($plan)) $errors[] = "Plan is required.";
            if (empty($address)) $errors[] = "Address is required.";
            if (empty($contact)) $errors[] = "Contact number is required.";

            // Additional validations
            if (!preg_match('/^[0-9]{10}$/', $contact)) {
                $errors[] = "Contact number must be 10 digits.";
            } else {
                // Check uniqueness
                $contact_check = mysqli_query($con, "SELECT user_id FROM members WHERE contact = '$contact'");
                if (mysqli_num_rows($contact_check) > 0) {
                    $errors[] = "This contact number is already registered.";
                }
            }

            // If there are errors, show them
            if (!empty($errors)) {
                echo "<div class='container-fluid'><div class='row-fluid'><div class='span12'><div class='alert alert-danger'>";
                echo "<strong>Please fix the following errors:</strong><ul>";
                foreach ($errors as $error) {
                    echo "<li>$error</li>";
                }
                echo "</ul></div></div></div></div>";
                exit;
            }
            $referral_code = generateReferralCode();

            $qry = "INSERT INTO members(fullname, username, password, dor, gender, services, amount, plan, address, contact, status, referral_code, referred_by)
                    VALUES ('$fullname', '$username', '$password', CURRENT_TIMESTAMP, '$gender', '$services', '0', '$plan', '$address', '$contact', 'Pending', '$referral_code', '$referred_by')";

            $result = mysqli_query($con, $qry);
            $new_user_id = mysqli_insert_id($con);
            if (!$result) {
                echo "<div class='container-fluid'>";
                echo "<div class='row-fluid'>";
                echo "<div class='span12'>";
                echo "<div class='widget-box'>";
                echo "<div class='widget-title'> <span class='icon'> <i class='icon-info-sign'></i> </span>";
                echo "<h5>Error Message</h5>";
                echo "</div>";
                echo "<div class='widget-content'>";
                echo "<div class='error_ex'>";
                echo "<h1 style='color:maroon;'>Error 404</h1>";
                echo "<h3>Error occured while entering your details</h3>";
                echo "<p>Please Try Again</p>";
                echo "<a class='btn btn-warning btn-big'  href='../pages/index.php'>Go Back</a> </div>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            } else {
                if (!empty($referred_by)) {
                    distributeCommission($con, $referred_by, $new_user_id);
                }
                echo "<div class='container-fluid'>";
                echo "<div class='row-fluid'>";
                echo "<div class='span12'>";
                echo "<div class='widget-box'>";
                echo "<div class='widget-title'> <span class='icon'> <i class='icon-info-sign'></i> </span>";
                echo "<h5>Message</h5>";
                echo "</div>";
                echo "<div class='widget-content'>";
                echo "<div class='error_ex'>";
                echo "<h3>Registration Successful</h3><p>Your Referral Code: <strong>$referral_code</strong></p>";
                echo "<h1>Success</h1>";
                echo "<h3>Member details has been added!</h3>";
                echo "<p>The requested details are added. Please wait for the verification.</p>";
                echo "<a class='btn btn-inverse btn-big'  href='../index.php'>Go Back</a> </div>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<h3>YOU ARE NOT AUTHORIZED TO REDIRECT THIS PAGE. GO BACK to <a href='index.php'> DASHBOARD </a></h3>";
        }
        ?>
    </form>
</body>

<!--end-Footer-part-->

<script src="../js/excanvas.min.js"></script>
<script src="../js/jquery.min.js"></script>
<script src="../js/jquery.ui.custom.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/jquery.flot.min.js"></script>
<script src="../js/jquery.flot.resize.min.js"></script>
<script src="../js/jquery.peity.min.js"></script>
<script src="../js/fullcalendar.min.js"></script>
<script src="../js/matrix.js"></script>
<script src="../js/matrix.dashboard.js"></script>
<script src="../js/jquery.gritter.min.js"></script>
<script src="../js/matrix.interface.js"></script>
<script src="../js/matrix.chat.js"></script>
<script src="../js/jquery.validate.js"></script>
<script src="../js/matrix.form_validation.js"></script>
<script src="../js/jquery.wizard.js"></script>
<script src="../js/jquery.uniform.js"></script>
<script src="../js/select2.min.js"></script>
<script src="../js/matrix.popover.js"></script>
<script src="../js/jquery.dataTables.min.js"></script>
<script src="../js/matrix.tables.js"></script>