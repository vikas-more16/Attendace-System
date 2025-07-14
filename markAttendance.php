<?php
session_start();

// ✅ Reset only required session variables
unset($_SESSION['Class'], $_SESSION['Subject'], $_SESSION['Date']);

// ✅ Retrieve session variables or fallback to GET parameters
if (!isset($_SESSION['Class'])) $_SESSION['Class'] = $_GET['class'] ?? null;
if (!isset($_SESSION['Subject'])) $_SESSION['Subject'] = $_GET['subject'] ?? null;
if (!isset($_SESSION['Date'])) $_SESSION['Date'] = $_GET['date'] ?? null;

// ✅ Check if session variables exist
$databaseName = $_SESSION['Class'];
$subjectTable = $_SESSION['Subject'];
$dateColumn = $_SESSION['Date'];

if (!$databaseName || !$subjectTable || !$dateColumn) {
    die("<h3 style='color: red;'>❌ Error: Session expired! Please try again.</h3>");
}

// ✅ Database connection
$serverName = "sql311.infinityfree.com";
$userName = "if0_39468667";
$password = "8805Vikas332261";
$DATABASE  = "if0_39468667_attendance";
$conn = new mysqli($serverName, $userName, $password, $DATABASE);

if ($conn->connect_error) {
    die("<h3 style='color: red;'>❌ Connection failed: " . $conn->connect_error . "</h3>");
}

// ✅ Check attendance status
$statusQuery = "SELECT status FROM attendance_status WHERE class = '$databaseName' AND subject = '$subjectTable' AND date = '$dateColumn'";
$statusResult = $conn->query($statusQuery);

// ✅ Debugging: Print SQL Query
//echo "<h4>🔍 Running Query: $statusQuery</h4>";

if (!$statusResult) {
    die("<h3 style='color: red;'>❌ SQL Error: " . $conn->error . "</h3>");
}

if ($statusResult->num_rows > 0) {
    $statusRow = $statusResult->fetch_assoc();
    $attendanceStatus = strtolower($statusRow['status']);

   // echo "<h3>📌 Attendance Status: " . strtoupper($attendanceStatus) . "</h3>"; // PRINT ON PAGE

    if ($attendanceStatus == 'closed') {
        die("<div style='display: flex; justify-content: center; align-items: center; height: 100vh; text-align: center; font-size: 1.5rem; color: red;'>
        <h3>🚫 Attendance is closed! You cannot mark attendance.</h3>
    </div>");
    } elseif ($attendanceStatus != 'open') {
        die("<div style='display: flex; justify-content: center; align-items: center; height: 100vh; text-align: center; font-size: 1.5rem; color: red;'>
        <h3>❌ Invalid attendance status! Please contact admin.</h3>
    </div>");
    }
} else {
    die("<div style='display: flex; justify-content: center; align-items: center; height: 100vh; text-align: center; font-size: 1.5rem; color: red;'>
        <h3>⚠️ No attendance record found for this class/subject/date.</h3>
    </div>");
}

// ✅ Ensure 'roll-no' is provided
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['roll-no'])) {
    $roll = (int) $_POST['roll-no'];

    // ✅ Check if Date Column Exists
    $checkColumn = "SHOW COLUMNS FROM `$subjectTable` LIKE '$dateColumn'";
    $result = $conn->query($checkColumn);
    if ($result->num_rows == 0) {
        $alterTableQuery = "ALTER TABLE `$subjectTable` ADD `$dateColumn` VARCHAR(10) DEFAULT NULL";
        $conn->query($alterTableQuery);
    }

    // ✅ Check if Roll Number Exists
    $checkRoll = $conn->prepare("SELECT * FROM `$subjectTable` WHERE `RollNo` = ?");
    $checkRoll->bind_param("i", $roll);
    $checkRoll->execute();
    $result = $checkRoll->get_result();

    if ($result->num_rows > 0) {
        $updateAttendance = $conn->prepare("UPDATE `$subjectTable` SET `$dateColumn` = 'Present' WHERE `RollNo` = ?");
    } else {
        $updateAttendance = $conn->prepare("INSERT INTO `$subjectTable` (`RollNo`, `$dateColumn`) VALUES (?, 'Present')");
    }
    
    $updateAttendance->bind_param("i", $roll);
    if ($updateAttendance->execute()) {
         echo "<script> window.location.href='sucess.html';</script>";
    } else {
        echo "<h3 style='color: red;'>❌ Error updating attendance: " . $conn->error . "</h3>";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="markAttendance.css">
</head>
<body>

<div class="input-container">
    <form id="attendance-form" action="markAttendance.php?class=<?php echo urlencode($databaseName); ?>&subject=<?php echo urlencode($subjectTable); ?>&date=<?php echo urlencode($dateColumn); ?>" method="post">
        <select name="roll-no" id="roll">
            <option value="" disabled selected hidden>Select Your RollNo ...</option>
            <?php for ($i = 1; $i <= 80; $i++) { echo "<option value='$i'>$i</option>"; } ?>
        </select>
        <button type="submit">Mark Attendance</button>
    </form>
</div>

<script>
    document.getElementById("attendance-form").addEventListener("submit", function(e) {
        let rollNo = document.getElementById("roll").value;
        if (!rollNo) {
            alert("Please select your Roll Number!");
            e.preventDefault();
        }
    });

</script>

</body>
</html>
