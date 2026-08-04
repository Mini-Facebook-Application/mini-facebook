<?php

// Load security functions and require login.
require_once "common.php";
require_login();

// Only accept form submissions using POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}

// Protect the form against CSRF attacks.
verify_csrf();

// Read and validate the submitted values.
$post_id = filter_input(
    INPUT_POST,
    "post_id",
    FILTER_VALIDATE_INT
);

$content = trim($_POST["content"] ?? "");
$user_id = $_SESSION["user_id"];

if (
    !$post_id ||
    $content === "" ||
    mb_strlen($content) > 1000
) {
    http_response_code(400);
    exit("The comment information is not valid.");
}

// Confirm that the selected post exists.
$check = $conn->prepare(
    "SELECT id
     FROM posts
     WHERE id = ?"
);

$check->bind_param("i", $post_id);
$check->execute();

$post = $check->get_result()->fetch_assoc();

$check->close();

if (!$post) {
    header("Location: posts.php?status=invalid-post");
    exit;
}

// Add the comment using a prepared statement.
$stmt = $conn->prepare(
    "INSERT INTO comments (
        post_id,
        user_id,
        content
     )
     VALUES (?, ?, ?)"
);

$stmt->bind_param(
    "iis",
    $post_id,
    $user_id,
    $content
);

if (!$stmt->execute()) {
    $stmt->close();

    http_response_code(500);
    exit("The comment could not be added.");
}

$stmt->close();

// Return to the feed with a success message.
header("Location: posts.php?status=comment-added");
exit;
