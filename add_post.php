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

$content = trim($_POST["content"] ?? "");
$user_id = $_SESSION["user_id"];

// Validate the post content in PHP.
if (
    $content === "" ||
    strlen($content) > 2000
) {
    http_response_code(400);
    exit("Post must contain 1 to 2000 characters.");
}

// Insert the post using a prepared statement.
$stmt = $conn->prepare(
    "INSERT INTO posts (user_id, content)
     VALUES (?, ?)"
);

$stmt->bind_param(
    "is",
    $user_id,
    $content
);

if (!$stmt->execute()) {
    $stmt->close();

    http_response_code(500);
    exit("The post could not be created.");
}

$stmt->close();

// Return to the feed with a success message.
header("Location: posts.php?status=post-created");
exit;
