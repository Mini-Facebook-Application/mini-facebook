<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$sql = "
SELECT
    posts.id,
    posts.content,
    posts.created_at,
    users.name
FROM posts
JOIN users
ON posts.user_id = users.id
ORDER BY posts.created_at DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Posts | miniFacebook</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

<h1>Posts</h1>

<p><a href="index.php">← Home</a></p>

<?php

if ($result->num_rows == 0) {

    echo "<p>No posts yet.</p>";

} else {

    while($row = $result->fetch_assoc()) {

        echo "<div class='post'>";

        echo "<h3>"
            . htmlspecialchars($row["name"])
            . "</h3>";

        echo "<p>"
            . nl2br(htmlspecialchars($row["content"]))
            . "</p>";

        echo "<small>"
            . $row["created_at"]
            . "</small>";

        echo "</div>";
    }

}

?>

</div>

</body>
</html>
