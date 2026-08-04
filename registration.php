<?php

// Load the database connection and security functions.
require_once "common.php";

$message = "";

// Process the registration form.
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Protect the form against CSRF attacks.
    verify_csrf();

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // Validate submitted information.
    if ($name === "") {
        $message = "Please enter your name.";

    } elseif (strlen($name) > 100) {
        $message = "Name must be 100 characters or fewer.";

    } elseif (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 100
    ) {
        $message = "Please enter a valid email address.";

    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";

    } else {

        // Check whether the email is already registered.
        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "An account with that email already exists.";

        } else {

            // Hash the password before storing it.
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Create the new regular-user account.
            $stmt = $conn->prepare(
                "INSERT INTO users (email, password, name)
                 VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "sss",
                $email,
                $hashed_password,
                $name
            );

            if ($stmt->execute()) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $message = "Registration failed. Please try again.";
            }

            $stmt->close();
        }

        $check->close();
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

    <title>Register | miniFacebook</title>

    <!-- Open-source Bootstrap framework -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    >

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <main class="container">

        <h1>Create an Account</h1>

        <?php if ($message !== ""): ?>
            <p class="message">
                <?= e($message) ?>
            </p>
        <?php endif; ?>

        <form method="post" action="registration.php">

            <!-- Hidden CSRF token -->
            <?= csrf_input() ?>

            <label for="name">Name</label>

            <input
                type="text"
                id="name"
                name="name"
                required
                maxlength="100"
                value="<?= e($_POST["name"] ?? "") ?>"
            >

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
                minlength="8"
            >

            <button type="submit">
                Register
            </button>

        </form>

        <p>
            Already have an account?
            <a href="login.php">Log in</a>
        </p>

    </main>

</body>

</html>
