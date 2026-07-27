<?php
require_once "config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $name = trim($_POST["name"] ?? "");

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
    } elseif ($name === "") {
        $message = "Please enter your name.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "An account with that email already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (email, password, name)
                 VALUES (?, ?, ?)"
            );
            $stmt->bind_param("sss", $email, $hashedPassword, $name);

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | miniFacebook</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container">
        <h1>Create an Account</h1>

        <?php if ($message !== ""): ?>
            <p class="message">
                <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <form method="post" action="registration.php">
            <label for="name">Name</label>
            <input
                type="text"
                id="name"
                name="name"
                required
                maxlength="100"
                value="<?= htmlspecialchars($_POST["name"] ?? "", ENT_QUOTES, "UTF-8") ?>"
            >

            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                required
                maxlength="100"
                value="<?= htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, "UTF-8") ?>"
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                minlength="8"
            >

            <button type="submit">Register</button>
        </form>

        <p>
            Already have an account?
            <a href="login.php">Log in</a>
        </p>
    </main>
</body>
</html>
