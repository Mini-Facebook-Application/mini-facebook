<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | miniFacebook</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container">
        <h1>miniFacebook</h1>

        <p>
            Welcome,
            <?= htmlspecialchars($_SESSION["name"], ENT_QUOTES, "UTF-8") ?>!
        </p>

        <p><a href="posts.php">View Posts</a></p>
        <p><a href="editprofile.php">Edit Profile</a></p>
        <p><a href="changepassword.php">Change Password</a></p>
        <p><a href="logout.php">Log Out</a></p>
    </main>
</body>
</html>
