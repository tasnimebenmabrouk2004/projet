<?php
session_start();
error_reporting(0);
require_once('include/config.php');
require('C:\xampp1\htdocs\tasnime\Online-Gym-Management-System-in-PHP-main\FPDF-master (1)\FPDF-master\fpdf.php'); // Assuming FPDF is in the same directory

if (strlen($_SESSION["uid"]) == 0) {
    header('location:login.php');
    exit;
}

$uid = $_SESSION['uid'];

try {
    // Database connection
    $dbh = new PDO("mysql:host=localhost;dbname=gym_codecampbd", "root", "");
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch booking details for the logged-in user
    $sql = "SELECT t1.id as bookingid, t3.fname as Name, t3.email as email,
                   t1.booking_date as bookingdate, t2.titlename as title,
                   t2.PackageDuratiobn as PackageDuration, t2.Price as Price, 
                   t2.Description as Description, t4.category_name as category_name, 
                   t5.PackageName as PackageName
            FROM tblbooking as t1
            JOIN tbladdpackage as t2 ON t1.package_id = t2.id
            JOIN tbluser as t3 ON t1.userid = t3.id
            JOIN tblcategory as t4 ON t2.category = t4.id
            JOIN tblpackage as t5 ON t2.PackageType = t5.id
            WHERE t1.userid = :uid";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':uid', $uid, PDO::PARAM_STR);
    $stmt->execute();
    $booking_details = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate statistics: number of bookings, total price, etc.
    $total_bookings = count($booking_details);
    $total_price = array_reduce($booking_details, function ($carry, $item) {
        return $carry + $item['Price'];
    }, 0);

    // Generate PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);

    // PDF Title
    $pdf->Cell(0, 10, 'Statistiques des Réservations', 0, 1, 'C');

    // Booking Details Table
    $pdf->Ln(10);
    $pdf->Cell(10, 10, 'No.', 1);
    $pdf->Cell(30, 10, 'Date', 1);
    $pdf->Cell(40, 10, 'Package Name', 1);
    $pdf->Cell(30, 10, 'Price', 1);
    $pdf->Cell(60, 10, 'Description', 1);
    $pdf->Ln();

    if (empty($booking_details)) {
        $pdf->Cell(0, 10, 'No bookings found.', 0, 1, 'C');
    } else {
        foreach ($booking_details as $index => $booking) {
            $pdf->Cell(10, 10, $index + 1, 1);
            $pdf->Cell(30, 10, $booking['bookingdate'], 1);
            $pdf->Cell(40, 10, $booking['PackageName'], 1);
            $pdf->Cell(30, 10, $booking['Price'], 1);
            $pdf->Cell(60, 10, substr($booking['Description'], 0, 30) . '...', 1);
            $pdf->Ln();
        }
    }

    // Total Bookings and Price
    $pdf->Ln(10);
    $pdf->Cell(0, 10, 'Total Réservations: ' . $total_bookings, 0, 1);
    $pdf->Cell(0, 10, 'Prix Total des Réservations: ' . number_format($total_price, 2) . ' USD', 0, 1);

    // Output PDF as download
    // Force download by setting header
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="rapport_reservations.pdf"');
    $pdf->Output('D');

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>
