<?php

// Shared session, database, and security functions.

if (session_status() !== PHP_SESSION_ACTIVE) {

    // Configure secure session-cookie settings.
    session_set_cookie_params([
        "httponly" => true,

        "secure" =>
            !empty($_SERVER["HTTPS"])
            && $_SERVER["HTTPS"] !== "off",

        "samesite" => "Strict"
    ]);

    session_start();
}

// Load the database connection.
require_once "config.php";


// Escape output to prevent XSS.
function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}


// Create and store a CSRF token.
function csrf_token()
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] =
            bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_token"];
}


// Create a hidden CSRF form field.
function csrf_input()
{
    return
        '<input type="hidden" name="csrf_token" value="'
        . e(csrf_token())
        . '">';
}


// Verify the submitted CSRF token.
function verify_csrf()
{
    $submitted_token =
        $_POST["csrf_token"] ?? "";

    if (
        !is_string($submitted_token) ||
        !hash_equals(
            csrf_token(),
            $submitted_token
        )
    ) {
        http_response_code(403);
        exit("Invalid CSRF token.");
    }
}


// Clear the current session.
function end_user_session()
{
    $_SESSION = [];
    session_destroy();
}


// Require a valid logged-in user.
function require_login()
{
    global $conn;

    if (empty($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }

    $current_time = time();

    // End the session after 30 minutes of inactivity.
    if (
        !empty($_SESSION["last_activity"]) &&
        $current_time -
        (int) $_SESSION["last_activity"] > 1800
    ) {
        end_user_session();

        header("Location: login.php?expired=1");
        exit;
    }

    // Check whether the browser information changed.
    $current_agent = hash(
        "sha256",
        $_SERVER["HTTP_USER_AGENT"] ?? "unknown"
    );

    if (
        empty($_SESSION["user_agent"]) ||
        !hash_equals(
            $_SESSION["user_agent"],
            $current_agent
        )
    ) {
        end_user_session();

        header("Location: login.php?expired=1");
        exit;
    }

    // Record the user's latest activity time.
    $_SESSION["last_activity"] = $current_time;

    $user_id = $_SESSION["user_id"];

    // Check the current role and account status.
    $stmt = $conn->prepare(
        "SELECT role, is_disabled
         FROM users
         WHERE id = ?"
    );

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $user = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    // End access if the account is missing or disabled.
    if (
        !$user ||
        (int) $user["is_disabled"] === 1
    ) {
        end_user_session();

        header("Location: login.php?disabled=1");
        exit;
    }

    // Keep the session role synchronized with the database.
    $_SESSION["role"] = $user["role"];
}


// Require the user to be a superuser.
function require_superuser()
{
    require_login();

    if (
        ($_SESSION["role"] ?? "user")
        !== "superuser"
    ) {
        http_response_code(403);
        exit("Access denied.");
    }
}
