<?php

// Load security functions and require a superuser.
require_once "common.php";
require_superuser();

$message = "";

// Display a message after enabling or disabling a user.
$status = $_GET["status"] ?? "";

if ($status === "user-disabled") {
    $message = "User account disabled.";
} elseif ($status === "user-enabled") {
    $message = "User account enabled.";
} elseif ($status === "no-change") {
    $message = "No user account was changed.";
}


// Get all registered users.
$stmt = $conn->prepare(
    "SELECT
        id,
        name,
        email,
        role,
        is_disabled,
        created_at
     FROM users
     ORDER BY created_at DESC"
);

$stmt->execute();

$users = $stmt->get_result()->fetch_all(
    MYSQLI_ASSOC
);

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Users | miniFacebook</title>

    <!-- Open-source Bootstrap framework -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    >

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <main class="container admin-container">

        <div class="page-heading">

            <h1>Manage Users</h1>

            <a href="index.php">
                Home
            </a>

        </div>


        <?php if ($message !== ""): ?>

            <p class="message">
                <?= e($message) ?>
            </p>

        <?php endif; ?>


        <div class="table-responsive">

            <table class="user-table">

                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($users as $user): ?>

                        <tr>

                            <td>
                                <?= e($user["name"]) ?>
                            </td>

                            <td>
                                <?= e($user["email"]) ?>
                            </td>

                            <td>
                                <?= e($user["role"]) ?>
                            </td>

                            <td>
                                <?php if (
                                    (int) $user["is_disabled"] === 1
                                ): ?>
                                    Disabled
                                <?php else: ?>
                                    Enabled
                                <?php endif; ?>
                            </td>

                            <td>

                                <!-- Superuser accounts cannot be disabled. -->
                                <?php if (
                                    $user["role"] === "user"
                                ): ?>

                                    <form
                                        method="post"
                                        action="admin_user_action.php"
                                        class="inline-form"
                                    >

                                        <?= csrf_input() ?>

                                        <input
                                            type="hidden"
                                            name="user_id"
                                            value="<?= (int) $user["id"] ?>"
                                        >

                                        <?php if (
                                            (int) $user["is_disabled"] === 1
                                        ): ?>

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="enable"
                                            >

                                            <button
                                                type="submit"
                                                class="button-secondary"
                                            >
                                                Enable
                                            </button>

                                        <?php else: ?>

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="disable"
                                            >

                                            <button
                                                type="submit"
                                                class="button-danger"
                                            >
                                                Disable
                                            </button>

                                        <?php endif; ?>

                                    </form>

                                <?php else: ?>

                                    Protected

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </main>

</body>

</html>
