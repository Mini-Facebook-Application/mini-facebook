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
    $name = trim($_POST["name"]);
    $additional_email = trim($_POST["additional_email"]);
    $phone = trim($_POST["phone"]);

    if ($name == "") {
        $message = "Name cannot be empty.";
    } elseif ($additional_email != "" &&
              !filter_var($additional_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Additional email is not valid.";
    } else {
        $sql = "UPDATE users
                SET name = ?, additional_email = ?, phone = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssi",
            $name,
            $additional_email,
            $phone,
            $user_id
        );

        if ($stmt->execute()) {
            $_SESSION["name"] = $name;
            $message = "Profile updated.";
        } else {
            $message = "Something went wrong.";
        }
    }
}

$sql = "SELECT name, email, additional_email, phone
        FROM users
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <h1>Edit Profile</h1>

    <p><a href="index.php">Back to Home</a></p>

    <?php
    if ($message != "") {
        echo "<p class='message'>"
            . htmlspecialchars($message)
            . "</p>";
    }
    ?>

    <form method="post" action="editprofile.php">

        <label>Primary Email</label>
        <input
            type="email"
            value="<?php echo htmlspecialchars($user["email"]); ?>"
            disabled
        >

        <label>Name</label>
        <input
            type="text"
            name="name"
            value="<?php echo htmlspecialchars($user["name"]); ?>"
            required
        >

        <label>Additional Email</label>
        <input
            type="email"
            name="additional_email"
            value="<?php echo htmlspecialchars(
                $user["additional_email"] ?? ""
            ); ?>"
        >

        <label>Phone</label>
        <input
            type="text"
            name="phone"
            value="<?php echo htmlspecialchars(
                $user["phone"] ?? ""
            ); ?>"
        >

        <button type="submit">Update Profile</button>

    </form>

</div>

</body>
</html>
