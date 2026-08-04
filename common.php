<?php

/*
common.php

This file contains functions that will be shared by multiple pages.

Instead of rewriting the same security code inside login.php,
posts.php, editprofile.php, and other pages, those pages can include
this file and use these functions.
*/


/*
Start a PHP session if one has not already been started.

Sessions allow the website to remember information about a logged-in
user as they move between different pages.
*/
if (session_status() !== PHP_SESSION_ACTIVE) {

    /*
    Configure security settings for the session cookie.

    httponly:
    Prevents JavaScript from directly reading the session cookie.
    This helps protect the cookie during an XSS attack.

    secure:
    Sends the session cookie only through HTTPS when HTTPS is active.

    samesite:
    Helps prevent another website from sending unwanted requests
    using the user's session cookie.
    */
    session_set_cookie_params([
        "httponly" => true,

        "secure" =>
            !empty($_SERVER["HTTPS"])
            && $_SERVER["HTTPS"] !== "off",

        "samesite" => "Strict"
    ]);

    /*
    Start the session after configuring the cookie settings.
    */
    session_start();
}


/*
Load the database connection from config.php.

require_once makes sure config.php is loaded only once, even if
another file already included it.
*/
require_once "config.php";


/*
Function: e()
This function helps prevent stored and reflected XSS attacks.
*/
function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}


/*
Function: csrf_token()

A CSRF token helps confirm that a form submission came from a form
created by this website, rather than from a malicious external site.
*/
function csrf_token()
{
    /*
    If the session does not have a token yet, create one.

    random_bytes(32) creates 32 secure random bytes.
    bin2hex() converts those bytes into text that can be placed
    safely inside an HTML form.
    */
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] =
            bin2hex(random_bytes(32));
    }

    /*
    Return the token so another function or form can use it.
    */
    return $_SESSION["csrf_token"];
}


/*
Function: csrf_input()

Purpose:
Create the hidden CSRF input that will be placed inside HTML forms.

The browser submits this hidden value with the rest of the form.

Example use inside a form:

<form method="post">
    <?php echo csrf_input(); ?>
</form>
*/
function csrf_input()
{
    /*
    e() safely escapes the token before placing it in HTML.
    */
    return
        '<input type="hidden" name="csrf_token" value="'
        . e(csrf_token())
        . '">';
}


/*
Function: verify_csrf()

Purpose:
Check that a submitted POST form contains the correct CSRF token.

This function will be called before processing actions such as:

- Creating a post
- Editing a post
- Deleting a post
- Adding a comment
- Editing a profile
- Changing a password
*/
function verify_csrf()
{
    /*
    Read the token submitted by the form.

    The ?? operator uses an empty string if csrf_token was not
    included in the POST request.
    */
    $submitted_token = $_POST["csrf_token"] ?? "";

    /*
    First, confirm that the submitted token is a string.

    Then use hash_equals() to securely compare the submitted token
    with the token stored in the user's session.
    */
    if (
        !is_string($submitted_token)
        || !hash_equals(
            csrf_token(),
            $submitted_token
        )
    ) {
        /*
        HTTP status code 403 means that the request is forbidden.
        The requested action will not be completed.
        */
        http_response_code(403);
        exit("Invalid CSRF token.");
    }
}


/*
Function: require_login()
Protect pages that should only be available to logged-in users.
*/
function require_login()
{
    if (empty($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }
}


/*
Function: require_superuser()
*/
function require_superuser()
{
    require_login();

    /*
    If no role exists in the session, "user" is used as the
    safe default. This prevents a missing role from being treated
    as a superuser.
    */
    if (
        ($_SESSION["role"] ?? "user")
        !== "superuser"
    ) {
        http_response_code(403);
        exit("Access denied.");
    }
}
