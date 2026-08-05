<?php

// Load security functions and require login.
require_once "common.php";
require_login();


// Return new chat messages to JavaScript.
if (
    $_SERVER["REQUEST_METHOD"] === "GET" &&
    isset($_GET["fetch"])
) {
    header("Content-Type: application/json");

    $after_id = filter_input(
        INPUT_GET,
        "after_id",
        FILTER_VALIDATE_INT
    );

    if ($after_id === false || $after_id === null) {
        $after_id = 0;
    }

    $stmt = $conn->prepare(
        "SELECT
            chat_messages.id,
            chat_messages.user_id,
            chat_messages.message,
            chat_messages.created_at,
            users.name
         FROM chat_messages
         JOIN users
            ON users.id = chat_messages.user_id
         WHERE chat_messages.id > ?
         ORDER BY chat_messages.id ASC
         LIMIT 100"
    );

    $stmt->bind_param("i", $after_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $messages = [];

    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            "id" => (int) $row["id"],
            "name" => $row["name"],
            "message" => $row["message"],
            "created_at" => $row["created_at"],
            "is_own" =>
                (int) $row["user_id"] ===
                (int) $_SESSION["user_id"]
        ];
    }

    $stmt->close();

    echo json_encode(
        $messages,
        JSON_HEX_TAG |
        JSON_HEX_AMP |
        JSON_HEX_APOS |
        JSON_HEX_QUOT
    );

    exit;
}


// Save a new chat message.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Content-Type: application/json");

    verify_csrf();

    $message = trim($_POST["message"] ?? "");

    if (
        $message === "" ||
        strlen($message) > 500
    ) {
        http_response_code(400);

        echo json_encode([
            "success" => false,
            "error" =>
                "Message must contain 1 to 500 characters."
        ]);

        exit;
    }

    $user_id = (int) $_SESSION["user_id"];

    $stmt = $conn->prepare(
        "INSERT INTO chat_messages
            (user_id, message)
         VALUES (?, ?)"
    );

    $stmt->bind_param(
        "is",
        $user_id,
        $message
    );

    $stmt->execute();
    $stmt->close();

    echo json_encode([
        "success" => true
    ]);

    exit;
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

    <title>Live Chat | miniFacebook</title>

    <!-- Open-source Bootstrap framework -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    >

    <link rel="stylesheet" href="style.css">

    <style>
        .chat-container {
            max-width: 800px;
        }

        .chat-messages {
            height: 420px;
            overflow-y: auto;
            border: 1px solid #cccccc;
            border-radius: 6px;
            background: #ffffff;
            padding: 15px;
            margin-bottom: 18px;
        }

        .chat-message {
            background: #f1eff8;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 10px;
        }

        .chat-message.own-message {
            background: #e2d9f3;
        }

        .chat-message p {
            margin: 4px 0;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .chat-message small {
            color: #666666;
        }

        .chat-status {
            min-height: 24px;
            color: #b02a37;
        }
    </style>
</head>

<body>

    <main class="container chat-container">

        <div class="d-flex justify-content-between align-items-center">
            <h1>Live Chat</h1>

            <a href="index.php">
                Home
            </a>
        </div>

        <p>
            Messages update automatically every two seconds.
        </p>

        <div
            id="chatMessages"
            class="chat-messages"
            aria-live="polite"
        ></div>

        <form id="chatForm">
            <?= csrf_input() ?>

            <label
                for="message"
                class="form-label"
            >
                Message
            </label>

            <textarea
                id="message"
                name="message"
                class="form-control"
                maxlength="500"
                rows="3"
                required
            ></textarea>

            <button
                type="submit"
                class="btn btn-primary mt-3"
            >
                Send Message
            </button>
        </form>

        <p
            id="chatStatus"
            class="chat-status mt-2"
        ></p>

    </main>

    <script>
        const chatMessages =
            document.getElementById("chatMessages");

        const chatForm =
            document.getElementById("chatForm");

        const messageInput =
            document.getElementById("message");

        const chatStatus =
            document.getElementById("chatStatus");

        let lastMessageId = 0;


        // Safely add one message to the page.
        function displayMessage(item) {
            const messageBox =
                document.createElement("div");

            messageBox.className = "chat-message";

            if (item.is_own) {
                messageBox.classList.add("own-message");
            }

            const name =
                document.createElement("strong");

            name.textContent = item.name;

            const message =
                document.createElement("p");

            message.textContent = item.message;

            const time =
                document.createElement("small");

            time.textContent = item.created_at;

            messageBox.appendChild(name);
            messageBox.appendChild(message);
            messageBox.appendChild(time);

            chatMessages.appendChild(messageBox);

            chatMessages.scrollTop =
                chatMessages.scrollHeight;
        }


        // Request messages newer than the last displayed message.
        async function loadMessages() {
            try {
                const response = await fetch(
                    "chat.php?fetch=1&after_id=" +
                    lastMessageId,
                    {
                        cache: "no-store"
                    }
                );

                if (!response.ok) {
                    throw new Error(
                        "Could not load messages."
                    );
                }

                const messages =
                    await response.json();

                messages.forEach(function (item) {
                    displayMessage(item);
                    lastMessageId = item.id;
                });

                chatStatus.textContent = "";
            } catch (error) {
                chatStatus.textContent =
                    "Chat connection was interrupted.";
            }
        }


        // Send the form without refreshing the page.
        chatForm.addEventListener(
            "submit",
            async function (event) {
                event.preventDefault();

                chatStatus.textContent = "";

                const formData =
                    new FormData(chatForm);

                try {
                    const response = await fetch(
                        "chat.php",
                        {
                            method: "POST",
                            body: formData
                        }
                    );

                    const result =
                        await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(
                            result.error ||
                            "Message could not be sent."
                        );
                    }

                    messageInput.value = "";

                    await loadMessages();
                } catch (error) {
                    chatStatus.textContent =
                        error.message;
                }
            }
        );


        loadMessages();

        setInterval(
            loadMessages,
            2000
        );
    </script>

</body>

</html>
