<?php

// Load security functions and require login.
require_once "common.php";
require_login();

// Only accept form submissions using POST.
if ($_SERVER["REQUEST_METHOD"] !== " "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}

// Protect the form against CSRF attacks.
verify_csrf();

// Read and validate the post ID.
$post_id = filter_input(
    INPUT_POST,
    "post_id",
    FILTER_VALIDATE_INT
);

$user_id = $_SESSION["user_id"];

if (!$post_id) {
    http_response_code(400);
    exit("Invalid post.");
}

$stmt = $conn->prepare(
    "DELETE FROM posts
     WHERE id = ?
     AND user_id = ?"
);

$stmt->bind_param(
    "ii",
    $post_id,
    $user_id
);

$stmt->execute();

$deleted_rows = $stmt->affected_rows;

$stmt->close();

// No deleted row means the user did not own the post.
if ($deleted_rows !== 1) {
    http_response_code(403);
    exit("You cannot delete this post.");
}

/*
The post's comments are also deleted because
the database uses ON DELETE CASCADE.
*/
header("Location: posts.php?status=post-deleted");
exit;
