<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['Class'] = $_POST['Class'];
    $_SESSION['Subject'] = $_POST['Subject'];
    $_SESSION['Date'] = $_POST['date'];
}

// Generate the URL for attendance marking with session variables
$attendanceURL = "https://system-a0001.free.nf/markAttendance.php?class=" . urlencode($_SESSION['Class']) .
                 "&subject=" . urlencode($_SESSION['Subject']) . 
                 "&date=" . urlencode($_SESSION['Date']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic QR Code for Attendance</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="qr-container" id="qr-container">
        <h2>Scan to Mark Attendance</h2>
        <a href="<?php echo $attendanceURL; ?>">   
            <div id="qrcode"></div>
        </a>

        <script>
            // Generate QR Code using PHP URL
            let attendanceURL = "<?php echo $attendanceURL; ?>";
            new QRCode(document.getElementById("qrcode"), {
                text: attendanceURL, 
                width: 300,
                height: 300
            });

            console.log("QR Code Generated for: " + attendanceURL);
        </script>

        <form action="Dashbord.php">
            <button id="close-qr-btn" type="submit">QR and Show Results</button>
        </form>
    </div>
</body>
</html>