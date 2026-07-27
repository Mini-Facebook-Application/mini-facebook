<?php
session_start();
require_once "config.php";

$message = "";

if (isset($_GET["registered"])) {
    $message = "Registration successful. Please log in.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif ($password === "") {
        $message = "Please enter your password.";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, email, password, name
             FROM users
             WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user["password"])) {
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["name"] = $user["name"];

            header("Location: index.php");
            exit;
        } else {
            $message = "Incorrect email or password.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | miniFacebook</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container">
        <h1>Log In</h1>

        <?php if ($message !== ""): ?>
            <p class="message">
                <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <form method="post" action="login.php">
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
            >

            <button type="submit">Log In</button>
        </form>

        <p>
            Need an account?
            <a href="registration.php">Register</a>
        </p>
    </main>
</body>
</html>
