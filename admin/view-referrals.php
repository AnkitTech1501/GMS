<?php
include "dbcon.php";

if (!isset($_POST['user_id'])) {
    echo "<p>No user selected.</p>";
    exit;
}

$user_id = $_POST['user_id'];
$username = $_POST['username'];
$fullname = $_POST['fullname'];

$q = "SELECT referral_earning,username as parent_member_username, amount FROM members WHERE user_id = $user_id LIMIT 1";
$r = mysqli_query($con, $q);
$row = mysqli_fetch_assoc($r);
$referralData = json_decode($row['referral_earning'], true);
$parent_member_username = $row['parent_member_username'];
$baseAmount = isset($row['amount']) ? (float)$row['amount'] : 10000;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Referral Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
        }

        .referral-container {
            max-width: 900px;
            margin: 60px auto;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            background: linear-gradient(135deg, #4e73df, #1cc88a);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 30px;
            font-size: 1.5rem;
            text-align: center;
            font-weight: 600;
        }

        .referral-box {
            margin-bottom: 25px;
            border-left: 5px solid #4e73df;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            transition: 0.3s;
        }

        .referral-box:hover {
            transform: scale(1.01);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
        }

        .referral-title {
            font-size: 1.2rem;
            color: #4e73df;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .referral-item {
            padding: 8px 12px;
            border-radius: 8px;
            background-color: #f8f9fc;
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 0.95rem;
        }

        .referral-item i {
            color: #1cc88a;
            margin-right: 8px;
        }

        .bonus-badge {
            font-weight: bold;
            color: #20c997;
        }

        .total-summary {
            font-size: 1.2rem;
            text-align: right;
            font-weight: 600;
            color: #1cc88a;
            margin-top: 20px;
        }

        .back-btn {
            text-align: center;
            margin-top: 30px;
        }

        .btn-rounded {
            border-radius: 30px;
            padding: 10px 25px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="container referral-container">
        <div class="card">
            <div class="card-header">
                Referral Earnings for <?php echo htmlspecialchars($fullname); ?> (@<?php echo htmlspecialchars($username); ?>)
            </div>
            <div class="card-body p-4">

                <?php if (!$referralData || empty($referralData)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i> No referral data found.
                    </div>
                <?php else: ?>
                    <?php
                    function getUsername($id, $conn)
                    {
                        $res = mysqli_query($conn, "SELECT username FROM members WHERE user_id = $id LIMIT 1");
                        if ($row = mysqli_fetch_assoc($res)) {
                            return $row['username'];
                        }
                        return "Unknown ($id)";
                    }
                    $totalPercent = 0;
                    $totalEarned = 0;

                    function formatCurrency($num)
                    {
                        return '$' . number_format($num, 2);
                    }

                    foreach ($referralData as $direct => $referrals):
                        // $directUsername = getUsername($direct, $con);
                        echo "<div class='referral-box'>";
                        echo "<div class='referral-title'><i class='fas fa-user-friends'></i> $parent_member_username referred:</div>";
                        foreach ($referrals as $ref => $percent) {
                            $refUsername = getUsername($ref, $con);
                            $earnedAmount = ($baseAmount * $percent) / 100;

                            echo "<div class='referral-item'>
                                <span><i class='fas fa-arrow-right'></i> $refUsername</span>
                                <span class='bonus-badge'>$percent% = " . formatCurrency($earnedAmount) . "</span>
                              </div>";

                            $totalPercent += $percent;
                            $totalEarned += $earnedAmount;
                        }

                        echo "</div>";
                    endforeach;

                    $tds = $totalEarned * 0.10;
                    $finalPayout = $totalEarned - $tds;
                    $remaining = $baseAmount - $finalPayout;
                    ?>

                    <div class="total-summary">
                        Base Amount: <strong><?php echo formatCurrency($baseAmount); ?></strong><br>
                        Total Bonus Used: <strong><?php echo $totalPercent; ?>%</strong><br>
                        Total Earned: <strong><?php echo formatCurrency($totalEarned); ?></strong><br>
                        TDS (10%): <strong><?php echo formatCurrency($tds); ?></strong><br>
                        Final Payable: <strong><?php echo formatCurrency($finalPayout); ?></strong><br>
                        Remaining in System: <strong><?php echo formatCurrency($remaining); ?></strong>
                    </div>
                <?php endif; ?>

                <div class="back-btn">
                    <a href="members.php" class="btn btn-secondary btn-rounded"><i class="fas fa-arrow-left"></i> Back to Members</a>
                </div>
            </div>
        </div>
    </div>

</body>

</html>