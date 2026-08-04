<?php

// Load the session and security functions.
require_once "common.php";

// Only allow logout requests submitted by the form.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}

// Verify the logout form's CSRF token.
verify_csrf();

// Remove all session data.
$_SESSION = [];

// Remove the session cookie from the browser.
if (ini_get("session.use_cookies")) {
    $cookie = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        time() - 42000,
        $cookie["path"],
        $cookie["domain"],
        $cookie["secure"],
        $cookie["httponly"]
    );
}

// End the session.
session_destroy();

// Return to the login page.
header("Location: login.php");
exit;
