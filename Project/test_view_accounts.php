<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>
<?php
//fetching
$result = [];
if (isset($id)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT Accounts.id,account_number,account_type,opened_date,last_updated,balance, user_id, Users.username FROM Accounts JOIN Users on Accounts.user_id = Users.id where Accounts.id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
}
?>
<?php if (isset($result) && !empty($result)): ?>
    <div class="card">
        <p class="card-title">
            Account Information
        </p>
        <div class="card-body">
            <div>
                <!-- <p>Account Information</p> -->
                <div>Account Owner: <?php safer_echo($result["username"]); ?></div>
                <div>Account Number: <?php safer_echo($result["account_number"]); ?></div>
                <div>Account Type: <?php safer_echo($result["account_type"]); ?></div>
                <div>Account Opened: <?php safer_echo($result["opened_date"]); ?></div>
                <div>Last Transaction: <?php safer_echo($result["last_updated"]); ?></div>
                <div>Current Balance: <?php safer_echo($result["balance"]); ?></div>
            </div>
        </div>
    </div>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>
<?php require(__DIR__ . "/partials/flash.php"); ?>