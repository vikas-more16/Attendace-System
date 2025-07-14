<?php 
session_start();
$_SESSION['Class'] = $_POST['Class'];
$_SESSION['Subject'] = $_POST['Subject'];
$_SESSION['Date'] = $_POST['date'];

$Class = $_SESSION['Class'];
$Subject = $_SESSION['Subject'];
$Date = $_SESSION['Date'];

$serverName = "sql311.infinityfree.com";
$userName = "if0_39468667";
$password = "8805Vikas332261";
$DATABASE  = "if0_39468667_attendance";

$conn = new mysqli($serverName, $userName, $password, $DATABASE);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$Class = mysqli_real_escape_string($conn, $Class);
$Subject = mysqli_real_escape_string($conn, $Subject);
$Date = mysqli_real_escape_string($conn, $Date);

// Check if the column already exists
$checkColumn = "SHOW COLUMNS FROM `$Subject` LIKE '$Date'";
$result = $conn->query($checkColumn);

if ($result->num_rows == 0) {
    $sql = "ALTER TABLE `$Subject` ADD COLUMN `$Date` VARCHAR(10) DEFAULT 'Absent'";
    if (!$conn->query($sql)) {
        die("Error adding column: " . $conn->error);
    }
}

// Insert or update attendance_status
$sql1 = "INSERT INTO attendance_status (class, subject, date, status) 
         VALUES ('$Class', '$Subject', '$Date', 'open') 
         ON DUPLICATE KEY UPDATE status = 'open'";

if ($conn->query($sql1) === TRUE) {
    header("Location: QR.php");
    exit();
} else {
    die("SQL Error: " . $conn->error);
}

$conn->close();
?>
