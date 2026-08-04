<?php

// Load security functions and require login.
require_once "common.php";
require_login();

$message = "";
$user_id = $_SESSION["user_id"];

// Process the profile form.
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Protect the form against CSRF attacks.
    verify_csrf();

    $name = trim($_POST["name"] ?? "");
    $additional_email =
        trim($_POST["additional_email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");

    // Validate the profile information.
    if ($name === "") {
        $message = "Name cannot be empty.";

    } elseif (mb_strlen($name) > 100) {
        $message = "Name must be 100 characters or fewer.";

    } elseif (
        $additional_email !== "" &&
        !filter_var(
            $additional_email,
            FILTER_VALIDATE_EMAIL
        )
    ) {
        $message = "Additional email is not valid.";

    } elseif (strlen($additional_email) > 100) {
        $message =
            "Additional email must be 100 characters or fewer.";

    } elseif (
        $phone !== "" &&
        !preg_match('/^[0-9+(). -]{7,20}$/', $phone)
    ) {
        $message = "Phone number is not valid.";

    } else {

        // Update only the logged-in user's profile.
        $sql = "
            UPDATE users
            SET
                name = ?,
                additional_email = ?,
                phone = ?
            WHERE id = ?
        ";

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
            $message = "Profile update failed.";
        }

        $stmt->close();
    }
}


// Get the user's current profile information.
$sql = "
    SELECT
        name,
        email,
        additional_email,
        phone
    FROM users
    WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

// Stop if the account cannot be found.
if (!$user) {
    http_response_code(404);
    exit("User account not found.");
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

    <title>Edit Profile | miniFacebook</title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    >

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <main class="container">

        <h1>Edit Profile</h1>

        <p>
            <a href="index.php">Back to Home</a>
        </p>

        <?php if ($message !== ""): ?>
            <p class="message">
                <?= e($message) ?>
            </p>
        <?php endif; ?>

        <form method="post" action="editprofile.php">

            <?= csrf_input() ?>

            <label for="primary_email">
                Primary Email
            </label>

            <input
                type="email"
                id="primary_email"
                value="<?= e($user["email"]) ?>"
                disabled
            >

            <label for="name">Name</label>

            <input
                type="text"
                id="name"
                name="name"
                required
                maxlength="100"
                value="<?= e($user["name"]) ?>"
            >

            <label for="additional_email">
                Additional Email
            </label>

            <input
                type="email"
                id="additional_email"
                name="additional_email"
                maxlength="100"
                value="<?= e(
                    $user["additional_email"] ?? ""
                ) ?>"
            >

            <label for="phone">Phone</label>

            <input
                type="text"
                id="phone"
                name="phone"
                maxlength="20"
                pattern="[0-9+(). -]{7,20}"
                value="<?= e($user["phone"] ?? "") ?>"
            >

            <button type="submit">
                Update Profile
            </button>

        </form>

    </main>

</body>

</html>
