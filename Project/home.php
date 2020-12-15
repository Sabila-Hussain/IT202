<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//we use this to safely get the email to display
$username = "";
if (isset($_SESSION["user"]) && isset($_SESSION["user"]["email"])) {
    $username = $_SESSION["user"]["username"];
}
?>
    <p>Welcome, <?php echo $username; ?></p>
    <?php require(__DIR__ . "/partials/flash.php"); ?>