<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
function get_acc_num($id){
    $db = getDB();
    $stmt = $db->prepare("SELECT account_number from Accounts WHERE id =:q");
    $r = $stmt->execute([":q" => $id]);
    if ($r) {
        $acc_num = $stmt->fetch(PDO::FETCH_ASSOC);
        return $acc_num['account_number'];
    }
    else {
        flash("There was a problem fetching account number");
    }
}
$results = [];
$acc_results = [];
$user = get_user_id();
$db = getDB();
$stmt = $db->prepare("SELECT * FROM Accounts where user_id = :id");
$stmt->execute([":id" => $user]);
$acc_results = $stmt->fetchall(PDO::FETCH_ASSOC);



    $stmt = $db->prepare("SELECT id, act_dest_id, action_type, amount, memo, expected_total from Transactions WHERE act_src_id =:q LIMIT 10");
    $r = $stmt->execute([":q" => $id]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }

    $src = get_acc_num($id);

?>
<h3 class="text-center page-title"> Account: <?php safer_echo($src); ?> </h3>

<div class="results">
    <?php if (count($results) > 0): ?>

    <div class="row title">
        <div class="col">
            <div>Account Number (Dest)</div>
        </div>
        <div class="col">
            <div>Trans Type</div>
        </div>
        <div class="col">
            <div>Amount</div>
        </div>
        <div class="col">
            <div>Memo</div>
        </div>
        <div class="col">
            <div>Balance</div>
        </div>
    </div>

            <?php foreach ($results as $r): 
                    $dest = get_acc_num($r["act_dest_id"]);
                ?>
                <div class="row">

                    <div class="col">
                        <div><?php safer_echo($dest); ?></div>
                    </div>
                    <div class="col">
                        <div><?php safer_echo($r["action_type"]); ?></div>
                    </div>
                    <div class="col">
                        <div><?php safer_echo($r["amount"]); ?></div>
                    </div>
                    <div class="col">
                        <div><?php safer_echo($r["memo"]); ?></div>
                    </div>
                    <div class="col">
                        <div><?php safer_echo($r["expected_total"]); ?></div>
                    </div>
                    <!-- <div>
                        <a type="button" href="test_edit_transactions.php?id=<?php safer_echo($r['id']); ?>">Edit</a>
                        <a type="button" href="test_view_transactions.php?id=<?php safer_echo($r['id']); ?>">View</a>
                    </div> -->
                </div>

            <?php endforeach; ?>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>
<?php require(__DIR__ . "/partials/flash.php"); ?>