<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('location:../index.php');
    exit();
}
include "dbcon.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>View Inquiries</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../css/bootstrap.min.css" />
    <link rel="stylesheet" href="../css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="../css/fullcalendar.css" />
    <link rel="stylesheet" href="../css/matrix-style.css" />
    <link rel="stylesheet" href="../css/matrix-media.css" />
    <link href="../font-awesome/css/fontawesome.css" rel="stylesheet" />
    <link href="../font-awesome/css/all.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/jquery.gritter.css" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>
    <style>
        .word-break {
            word-break: break-word;
        }
    </style>
</head>

<body>

    <div id="header">
        <h1><a href="index.php">Perfect Gym Admin</a></h1>
    </div>

    <?php include 'includes/topheader.php' ?>
    <?php $page = 'inquiry-member';
    include 'includes/sidebar.php' ?>

    <div id="content">
        <div id="content-header">
            <div id="breadcrumb">
                <a href="index.php" class="tip-bottom"><i class="fas fa-home"></i> Home</a>
                <a href="#" class="current">View Inquiries</a>
            </div>
            <h1 class="text-center">Member Inquiries <i class="fas fa-comments"></i></h1>
        </div>

        <div class="container-fluid">
            <hr>
            <div class="row-fluid">
                <div class="span12">
                    <div class="widget-box">
                        <div class="widget-title">
                            <span class="icon"><i class="fas fa-table"></i></span>
                            <h5>Inquiry List</h5>
                        </div>
                        <div class="widget-content nopadding">

                            <table class='table table-bordered table-hover data-table'>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Member Name</th>
                                        <th>Inquiry Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $cnt = 1;
                                    $query = "SELECT i.inquiry_id, i.inquiry_description description, m.fullname 
                                              FROM inquiry i 
                                              JOIN members m ON i.member_id = m.user_id 
                                              ORDER BY i.inquiry_id DESC";

                                    $result = mysqli_query($conn, $query);

                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>
                                                <td class='text-center'>{$cnt}</td>
                                                <td class='text-center'>" . htmlspecialchars($row['fullname']) . "</td>
                                                <td class='word-break'>" . htmlspecialchars($row['description']) . "</td>
                                              </tr>";
                                        $cnt++;
                                    }
                                    ?>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row-fluid">
        <div id="footer" class="span12"><?php echo date("Y"); ?> &copy; Developed By Naseeb Bajracharya</div>
    </div>

    <style>
        #footer {
            color: white;
        }
    </style>

    <!-- Scripts -->
    <script src="../js/excanvas.min.js"></script>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/jquery.ui.custom.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/jquery.dataTables.min.js"></script>
    <script src="../js/matrix.js"></script>
    <script src="../js/matrix.tables.js"></script>

</body>

</html>