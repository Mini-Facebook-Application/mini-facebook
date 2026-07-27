<?php
session_start();
include "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$message = "";
$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];

    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!password_verify($current_password, $user["password"])) {

        $message = "Current password is incorrect.";

    } elseif (strlen($new_password) < 8) {

        $message = "New password must be at least 8 characters.";

    } else {

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $hashed_password, $user_id);

        if ($stmt->execute()) {
            $message = "Password updated successfully.";
        } else {
            $message = "Password update failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

<h1>Change Password</h1>

<p><a href="index.php">Back to Home</a></p>

<?php
if ($message != "") {
    echo "<p>$message</p>";
}
?>

<form method="post">

<label>Current Password</label>
<input type="password" name="current_password" required>

<label>New Password</label>
<input type="password" name="new_password" required>

<button type="submit">
Update Password
</button>

</form>

</div>

</body>
</html>
