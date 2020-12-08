<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
$query = "";
$results = [];
$acc_results = [];
$user = get_user_id();
$db = getDB();
$stmt = $db->prepare("SELECT * FROM Accounts where user_id = :id");
$stmt->execute([":id" => $user]);
$acc_results = $stmt->fetchall(PDO::FETCH_ASSOC);
if (isset($_POST["query"])) {
    $query = $_POST["query"];
}
if (isset($_POST["search"]) && !empty($query)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, act_dest_id, action_type, amount, memo, expected_total from Transactions WHERE act_src_id =:q LIMIT 10");
    $r = $stmt->execute([":q" => $query]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }
}
?>
<form method="POST">
<label>Account</label>
    <select name="query" >
        <option value="-1">None</option>
        <?php foreach ($acc_results as $account): ?>
            <option value="<?php safer_echo($account["id"]); ?>">
                <?php safer_echo($account["account_number"]); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input type="submit" value="Search" name="search"/>
</form>
<div class="results">
    <?php if (count($results) > 0): ?>
        <div class="list-group">
            <?php foreach ($results as $r): ?>
                <div class="list-group-item">
                    <div>
                        <div>Account Number (Dest):</div>
                        <div><?php safer_echo($r["act_dest_id"]); ?></div>
                    </div>
                    <div>
                        <div>Trans Type:</div>
                        <div><?php safer_echo($r["action_type"]); ?></div>
                    </div>
                    <div>
                        <div>Amount:</div>
                        <div><?php safer_echo($r["amount"]); ?></div>
                    </div>
                    <div>
                        <div>Memo:</div>
                        <div><?php safer_echo($r["memo"]); ?></div>
                    </div>
                    <div>
                        <div>Balance:</div>
                        <div><?php safer_echo($r["expected_total"]); ?></div>
                    </div>
                    <div>
                        <a type="button" href="test_edit_transactions.php?id=<?php safer_echo($r['id']); ?>">Edit</a>
                        <a type="button" href="test_view_transactions.php?id=<?php safer_echo($r['id']); ?>">View</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>
<?php require(__DIR__ . "/partials/flash.php"); ?>