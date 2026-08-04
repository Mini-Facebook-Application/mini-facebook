<?php

// Load security functions and require a superuser.
require_once "common.php";
require_superuser();

// Only accept form submissions using POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}

// Protect the action against CSRF attacks.
verify_csrf();

// Read and validate the submitted information.
$user_id = filter_input(
    INPUT_POST,
    "user_id",
    FILTER_VALIDATE_INT
);

$action = $_POST["action"] ?? "";

if (
    !$user_id ||
    !in_array(
        $action,
        ["enable", "disable"],
        true
    )
) {
    http_response_code(400);
    exit("Invalid user action.");
}


// Convert the requested action into a database value.
if ($action === "disable") {
    $is_disabled = 1;
} else {
    $is_disabled = 0;
}


/*
Update only regular-user accounts.

The role condition prevents a superuser account
from being disabled through a modified request.
*/
$stmt = $conn->prepare(
    "UPDATE users
     SET is_disabled = ?
     WHERE id = ?
     AND role = 'user'"
);

$stmt->bind_param(
    "ii",
    $is_disabled,
    $user_id
);

$stmt->execute();

$changed_rows = $stmt->affected_rows;

$stmt->close();


// Return to the user-management page.
if ($changed_rows !== 1) {
    header("Location: admin.php?status=no-change");
    exit;
}

if ($action === "disable") {
    header("Location: admin.php?status=user-disabled");
} else {
    header("Location: admin.php?status=user-enabled");
}

exit;
