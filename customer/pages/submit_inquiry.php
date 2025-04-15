<?php
include 'dbcon.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    if ($member_id > 0 && !empty($description)) {
        $stmt = mysqli_prepare($con, "INSERT INTO inquiry (member_id, inquiry_description) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "is", $member_id, $description);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Inquiry submitted successfully!'); window.location.href='inquiry.php';</script>";
        } else {
            echo "Error: " . mysqli_error($con);
        }
    } else {
        echo "Invalid input. Please try again.";
    }
}
