<?php

// Load security functions and require login.
require_once "common.php";
require_login();

$message = "";
$user_id = $_SESSION["user_id"];

// Process the password-change form.
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Protect the form against CSRF attacks.
    verify_csrf();

    $current_password =
        $_POST["current_password"] ?? "";

    $new_password =
        $_POST["new_password"] ?? "";

    $confirm_password =
        $_POST["confirm_password"] ?? "";

    // Get the user's current password hash.
    $stmt = $conn->prepare(
        "SELECT password FROM users WHERE id = ?"
    );

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();

    // Validate the current and new passwords.
    if (
        !$user ||
        !password_verify(
            $current_password,
            $user["password"]
        )
    ) {
        $message = "Current password is incorrect.";

    } elseif (strlen($new_password) < 8) {
        $message =
            "New password must be at least 8 characters.";

    } elseif ($new_password !== $confirm_password) {
        $message = "New passwords do not match.";

    } else {

        // Hash the new password before saving it.
        $hashed_password = password_hash(
            $new_password,
            PASSWORD_DEFAULT
        );

        $stmt = $conn->prepare(
            "UPDATE users
             SET password = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "si",
            $hashed_password,
            $user_id
        );

        if ($stmt->execute()) {
            $message = "Password updated successfully.";

            // Create a new session ID after the password change.
            session_regenerate_id(true);
        } else {
            $message = "Password update failed.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Change Password | miniFacebook</title>

    <!-- Open-source Bootstrap framework -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    >

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <main class="container">

        <h1>Change Password</h1>

        <p>
            <a href="index.php">Back to Home</a>
        </p>

        <?php if ($message !== ""): ?>
            <p class="message">
                <?= e($message) ?>
            </p>
        <?php endif; ?>

        <form method="post" action="changepassword.php">

            <!-- Hidden CSRF token -->
            <?= csrf_input() ?>

            <label for="current_password">
                Current Password
            </label>

            <input
                type="password"
                id="current_password"
                name="current_password"
                required
            >

            <label for="new_password">
                New Password
            </label>

            <input
                type="password"
                id="new_password"
                name="new_password"
                required
                minlength="8"
            >

            <label for="confirm_password">
                Confirm New Password
            </label>

            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                required
                minlength="8"
            >

            <button type="submit">
                Update Password
            </button>

        </form>

    </main>

</body>

</html>
