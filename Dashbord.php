<?php
session_start(); // Start the session

//Write your database connection details here
$serverName = "";
$userName = "";
$password = "";
$DATABASE  = "";
$SUBJECT = $_SESSION['Subject'];
$CLASS = $_SESSION['Class'];
$DATE = $_SESSION['Date'];

$conn = new mysqli($serverName, $userName, $password, $DATABASE);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Close Attendance
$closeAttendanceQuery = "UPDATE attendance_status 
                         SET status = 'closed' 
                         WHERE class = '$CLASS' 
                         AND subject = '$SUBJECT' 
                         AND date = '$DATE'";

if (!$conn->query($closeAttendanceQuery)) {
    die("Error updating attendance status: " . $conn->error);
}

// ✅ Fetch Roll Number Column Name
$rollColumnQuery = "SHOW COLUMNS FROM `$SUBJECT`";
$rollColumnResult = $conn->query($rollColumnQuery);

$rollColumnName = null;
if ($rollColumnResult->num_rows > 0) {
    while ($row = $rollColumnResult->fetch_assoc()) {
        if (stripos($row['Field'], 'roll') !== false) {
            $rollColumnName = $row['Field'];
            break;
        }
    }
}

// ✅ Ensure Roll Number Column Exists
if (!$rollColumnName) {
    die("<script>alert('Error: Roll number column not found!'); window.location.href='markAttendance.html';</script>");
}

// ✅ Fetch Attendance Data
$presentStudents = [];
$absentStudents = range(1, 80); // Assume all 80 students are absent initially

$sql = "SELECT `$rollColumnName`, `$DATE` FROM `$SUBJECT`";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $roll = (int) $row[$rollColumnName];
        if ($row[$DATE] === 'Present') {
            $presentStudents[] = $roll;
            if (($key = array_search($roll, $absentStudents)) !== false) {
                unset($absentStudents[$key]); // Remove present students from absent list
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report</title>
    <link rel="stylesheet" href="dashbord.css">
</head>
<body>
    <div class="container">
        <h2>Attendance Report - <?php echo htmlspecialchars($DATE); ?></h2>

        <!-- ✅ Show Total Present and Absent Students -->
        <h3 class="summary">
            ✅ Total Present: <?php echo count($presentStudents); ?> | ❌ Total Absent: <?php echo count($absentStudents); ?>
        </h3>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>Roll No</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($presentStudents as $roll): ?>
                    <tr class="present">
                        <td><?php echo $roll; ?></td>
                        <td>Present</td>
                    </tr>
                <?php endforeach; ?>
                <?php foreach ($absentStudents as $roll): ?>
                    <tr class="absent">
                        <td><?php echo $roll; ?></td>
                        <td>Absent</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <form action="attendance.html">
            <button type="submit">Next Attendance</button>
        </form>
    </div>
</body>
</html>
