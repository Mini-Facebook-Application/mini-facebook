<?php

// Load security functions and require login.
require_once "common.php";
require_login();

$message = "";

// Display messages after post and comment actions.
$status = $_GET["status"] ?? "";

$status_messages = [
    "post-created" => "Post created.",
    "post-updated" => "Post updated.",
    "post-deleted" => "Post deleted.",
    "comment-added" => "Comment added.",
    "invalid-post" => "That post could not be found.",
    "not-owner" => "You cannot change that post."
];

if (isset($status_messages[$status])) {
    $message = $status_messages[$status];
}


// Get all posts and their authors.
$stmt = $conn->prepare(
    "SELECT
        posts.id,
        posts.user_id,
        posts.content,
        posts.created_at,
        users.name
     FROM posts
     JOIN users
        ON posts.user_id = users.id
     ORDER BY posts.created_at DESC"
);

$stmt->execute();
$posts = $stmt->get_result()->fetch_all(
    MYSQLI_ASSOC
);

$stmt->close();


// Get all comments and their authors.
$comment_stmt = $conn->prepare(
    "SELECT
        comments.id,
        comments.post_id,
        comments.content,
        comments.created_at,
        users.name
     FROM comments
     JOIN users
        ON comments.user_id = users.id
     ORDER BY comments.created_at ASC"
);

$comment_stmt->execute();
$comment_result = $comment_stmt->get_result();


// Group the comments by their post ID.
$comments_by_post = [];

while ($comment = $comment_result->fetch_assoc()) {
    $post_id = $comment["post_id"];
    $comments_by_post[$post_id][] = $comment;
}

$comment_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Posts | miniFacebook</title>

    <!-- Open-source Bootstrap framework -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    >

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <main class="container feed-container">

        <div class="page-heading">

            <h1>miniFacebook Feed</h1>

            <a href="index.php">
                Home
            </a>

        </div>


        <?php if ($message !== ""): ?>

            <p class="message">
                <?= e($message) ?>
            </p>

        <?php endif; ?>


        <!-- Form for creating a new post. -->
        <section class="card-section">

            <h2>Create a Post</h2>

            <form method="post" action="add_post.php">

                <?= csrf_input() ?>

                <label for="content">
                    What would you like to share?
                </label>

                <textarea
                    id="content"
                    name="content"
                    required
                    maxlength="2000"
                    rows="4"
                ></textarea>

                <button type="submit">
                    Post
                </button>

            </form>

        </section>


        <?php if (count($posts) === 0): ?>

            <p>No posts yet. Create the first one.</p>

        <?php endif; ?>


        <!-- Display each post. -->
        <?php foreach ($posts as $post): ?>

            <article class="post">

                <div class="post-header">

                    <h2>
                        <?= e($post["name"]) ?>
                    </h2>

                    <small>
                        <?= e($post["created_at"]) ?>
                    </small>

                </div>


                <p>
                    <?= nl2br(e($post["content"])) ?>
                </p>


                <!-- Only the post owner sees edit and delete. -->
                <?php if (
                    (int) $post["user_id"] ===
                    (int) $_SESSION["user_id"]
                ): ?>

                    <div class="post-actions">

                        <a
                            class="button-secondary"
                            href="edit_post.php?id=<?= (int) $post["id"] ?>"
                        >
                            Edit
                        </a>


                        <form
                            method="post"
                            action="delete_post.php"
                            class="inline-form"
                            onsubmit="return confirm(
                                'Delete this post and its comments?'
                            );"
                        >

                            <?= csrf_input() ?>

                            <input
                                type="hidden"
                                name="post_id"
                                value="<?= (int) $post["id"] ?>"
                            >

                            <button
                                type="submit"
                                class="button-danger"
                            >
                                Delete
                            </button>

                        </form>

                    </div>

                <?php endif; ?>


                <!-- Display comments for this post. -->
                <section class="comments">

                    <h3>Comments</h3>

                    <?php foreach (
                        $comments_by_post[$post["id"]] ?? []
                        as $comment
                    ): ?>

                        <div class="comment">

                            <strong>
                                <?= e($comment["name"]) ?>:
                            </strong>

                            <?= nl2br(e($comment["content"])) ?>

                            <small>
                                <?= e($comment["created_at"]) ?>
                            </small>

                        </div>

                    <?php endforeach; ?>


                    <!-- Any logged-in user can add a comment. -->
                    <form
                        method="post"
                        action="add_comment.php"
                        class="comment-form"
                    >

                        <?= csrf_input() ?>

                        <input
                            type="hidden"
                            name="post_id"
                            value="<?= (int) $post["id"] ?>"
                        >

                        <label for="comment-<?= (int) $post["id"] ?>">
                            Add a comment
                        </label>

                        <textarea
                            id="comment-<?= (int) $post["id"] ?>"
                            name="content"
                            required
                            maxlength="1000"
                            rows="2"
                        ></textarea>

                        <button type="submit">
                            Comment
                        </button>

                    </form>

                </section>

            </article>

        <?php endforeach; ?>

    </main>

</body>

</html>
