<?php

// Load the security functions and require a logged-in user.
require_once "common.php";
require_login();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Home | miniFacebook</title>

    <!-- Open-source Bootstrap framework -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    >

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <main class="container">

        <h1>miniFacebook</h1>

        <!-- Safely display the logged-in user's name. -->
        <p>
            Welcome, <?= e($_SESSION["name"]) ?>!
        </p>

        <nav>
            <p>
                <a href="posts.php">
                    View and Manage Posts
                </a>
            </p>
            <p>
                <a href="chat.php">
                    Live Chat
                </a>
            </p>
            <p>
                <a href="editprofile.php">
                    Edit Profile
                </a>
            </p>

            <p>
                <a href="changepassword.php">
                    Change Password
                </a>
            </p>

            <!-- Only superusers can see the user-management link. -->
            <?php if (
                ($_SESSION["role"] ?? "user") === "superuser"
            ): ?>
                <p>
                    <a href="admin.php">
                        Manage Users
                    </a>
                </p>
            <?php endif; ?>
        </nav>

        <!-- Use POST and a CSRF token to log out securely. -->
        <form
            method="post"
            action="logout.php"
            class="inline-form"
        >
            <?= csrf_input() ?>

            <button type="submit" class="link-button">
                Log Out
            </button>
        </form>

    </main>

</body>

</html>
