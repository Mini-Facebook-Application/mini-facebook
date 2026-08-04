<?php

// Load the database connection and security functions.
require_once "common.php";

$message = "";

// Display messages after registration or session problems.
if (isset($_GET["registered"])) {
    $message = "Registration successful. Please log in.";
} elseif (isset($_GET["disabled"])) {
    $message = "This account is disabled. Contact a superuser.";
} elseif (isset($_GET["expired"])) {
    $message = "Your session ended. Please log in again.";
}

// Process the login form.
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Protect the form against CSRF attacks.
    verify_csrf();

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // Validate the submitted login information.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";

    } elseif ($password === "") {
        $message = "Please enter your password.";

    } else {

        // Find the account using a prepared statement.
        $sql = "
            SELECT
                id,
                email,
                password,
                name,
                role,
                is_disabled
            FROM users
            WHERE email = ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Check that the account and password are correct.
        if (
            !$user ||
            !password_verify($password, $user["password"])
        ) {
            $message = "Incorrect email or password.";

        // Prevent disabled accounts from logging in.
        } elseif ((int) $user["is_disabled"] === 1) {
            $message = "This account is disabled. Contact a superuser.";

        } else {

            // Prevent session fixation.
            session_regenerate_id(true);

            // Store the logged-in user's information.
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["role"] = $user["role"];

            // Store session security information.
            $_SESSION["user_agent"] = hash(
                "sha256",
                $_SERVER["HTTP_USER_AGENT"] ?? "unknown"
            );

            $_SESSION["last_activity"] = time();

            // Create a new CSRF token after login.
            unset($_SESSION["csrf_token"]);

            header("Location: index.php");
            exit;
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

    <title>Login | miniFacebook</title>

    <!-- Open-source Bootstrap framework -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    >

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <main class="container">

        <h1>Log In</h1>

        <?php if ($message !== ""): ?>
            <p class="message">
                <?= e($message) ?>
            </p>
        <?php endif; ?>

        <form method="post" action="login.php">

            <!-- Hidden CSRF token -->
            <?= csrf_input() ?>

            <label for="email">Email</label>

            <input
                type="email"
                id="email"
                name="email"
                required
                maxlength="100"
                value="<?= e($_POST["email"] ?? "") ?>"
            >

            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                required
            >

            <button type="submit">
                Log In
            </button>

        </form>

        <p>
            Need an account?
            <a href="registration.php">Register</a>
        </p>

    </main>

</body>

</html>
