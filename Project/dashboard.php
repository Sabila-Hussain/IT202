<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//Note: we have this up here, so our update happens before our get/fetch
//that way we'll fetch the updated data and have it correctly reflect on the form below
//As an exercise swap these two and see how things change
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    die(header("Location: login.php"));
}

// echo "<pre>" . var_export($_SESSION, true) . "</pre>";

$username = "";
if (isset($_SESSION["user"]) && isset($_SESSION["user"]["username"])) {
    $username = $_SESSION["user"]["username"];
}


?>
<p>Welcome to the main page, 
    <?php echo $username; ?>
</p>