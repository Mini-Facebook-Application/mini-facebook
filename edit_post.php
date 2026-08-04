<?php

// Load security functions and require login.
require_once "common.php";
require_login();

$message = "";
$user_id = $_SESSION["user_id"];

// Get the post ID from the URL.
$post_id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);


// Process the edit form.
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Protect the form against CSRF attacks.
    verify_csrf();

    $post_id = filter_input(
        INPUT_POST,
        "post_id",
        FILTER_VALIDATE_INT
    );

    $content = trim($_POST["content"] ?? "");

    // Validate the edited post.
    if (
        !$post_id ||
        $content === "" ||
        strlen($content) > 2000
    ) {
        $message =
            "Post must contain 1 to 2000 characters.";

    } else {

        /*
        Update only when the post belongs to the
        logged-in user.
        */
        $stmt = $conn->prepare(
            "UPDATE posts
             SET content = ?
             WHERE id = ?
             AND user_id = ?"
        );

        $stmt->bind_param(
            "sii",
            $content,
            $post_id,
            $user_id
        );

        $stmt->execute();

        $changed_rows = $stmt->affected_rows;

        $stmt->close();

        /*
        A value of zero means the post was unchanged
        or did not belong to this user.
        */
        if ($changed_rows === 0) {

            // Check whether the user owns the post.
            $check = $conn->prepare(
                "SELECT id
                 FROM posts
                 WHERE id = ?
                 AND user_id = ?"
            );

            $check->bind_param(
                "ii",
                $post_id,
                $user_id
            );

            $check->execute();

            $owned_post =
                $check->get_result()->fetch_assoc();

            $check->close();

            if (!$owned_post) {
                http_response_code(403);
                exit("You cannot edit this post.");
            }
        }

        header(
            "Location: posts.php?status=post-updated"
        );
        exit;
    }
}


// Stop if the post ID is missing or invalid.
if (!$post_id) {
    http_response_code(400);
    exit("Invalid post.");
}


// Get the post only if it belongs to this user.
$stmt = $conn->prepare(
    "SELECT id, content
     FROM posts
     WHERE id = ?
     AND user_id = ?"
);

$stmt->bind_param(
    "ii",
    $post_id,
    $user_id
);

$stmt->execute();

$post = $stmt->get_result()->fetch_assoc();

$stmt->close();


// Prevent users from editing someone else's post.
if (!$post) {
    http_response_code(403);
    exit("You cannot edit this post.");
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

    <title>Edit Post | miniFacebook</title>

    <!-- Open-source Bootstrap framework -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    >

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <main class="container">

        <h1>Edit Post</h1>

        <p>
            <a href="posts.php">Back to Posts</a>
        </p>

        <?php if ($message !== ""): ?>
            <p class="message">
                <?= e($message) ?>
            </p>
        <?php endif; ?>

        <form method="post" action="edit_post.php">

            <?= csrf_input() ?>

            <input
                type="hidden"
                name="post_id"
                value="<?= (int) $post["id"] ?>"
            >

            <label for="content">
                Post Content
            </label>

            <textarea
                id="content"
                name="content"
                required
                maxlength="2000"
                rows="7"
            ><?= e($post["content"]) ?></textarea>

            <button type="submit">
                Save Changes
            </button>

        </form>

    </main>

</body>

</html>
