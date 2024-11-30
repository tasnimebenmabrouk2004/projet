<?php
session_start(); // Start the session
error_reporting(0);
require_once('include/config.php');

// Check if user is logged in, if not redirect to login
if (strlen($_SESSION["uid"]) == 0) {
    header('location:login.php');
    exit;
}

$uid = $_SESSION['uid']; // Get the logged-in user's ID

// Fetch booking details from the database
$sql = "SELECT t1.id as bookingid, t3.fname as Name, t3.email as email, t1.booking_date as bookingdate,
            t2.titlename as title, t2.PackageDuratiobn as PackageDuration, t2.Price as Price,
            t2.Description as Description, t4.category_name as category_name, t5.PackageName as PackageName
         FROM tblbooking as t1
         JOIN tbladdpackage as t2 ON t1.package_id = t2.id
         JOIN tbluser as t3 ON t1.userid = t3.id
         JOIN tblcategory as t4 ON t2.category = t4.id
         JOIN tblpackage as t5 ON t2.PackageType = t5.id
         WHERE t1.userid = :uid";
$query = $dbh->prepare($sql);
$query->bindParam(':uid', $uid, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Booking History</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Your Booking History</h2>

        <!-- Check if session variable exists -->
        <?php if(strlen($_SESSION["uid"]) == 0): ?>
            <h3>Please log in to view your booking history.</h3>
        <?php else: ?>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Booking Date</th>
                        <th>Package Name</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>  
                            <!-- Button to generate PDF -->
                            <a href="generate_pdf.php" class="btn btn-primary">Download PDF</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $cnt = 1;
                    if ($query->rowCount() > 0) {
                        foreach ($results as $result) { ?>
                            <tr>
                                <td><?php echo $cnt; ?></td>
                                <td><?php echo htmlentities($result->bookingdate); ?></td>
                                <td><?php echo htmlentities($result->PackageName); ?></td>
                                <td><?php echo htmlentities($result->Price); ?></td>
                                <td><?php echo htmlentities($result->Description); ?></td>
                            </tr>
                            <?php $cnt++; 
                        } 
                    } ?>
                </tbody>
            </table>

        <?php endif; ?>
    </div>
</body>
</html>
