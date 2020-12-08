<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
$id = get_user_id();
$results = [];
    $db = getDB();
    $stmt = $db->prepare("SELECT id, account_number, account_type, opened_date, last_updated, balance from Accounts WHERE user_id like :id LIMIT 10");
    $r = $stmt->execute([":id" => $id]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }
?>
<!-- <form method="POST">
    <input name="query" placeholder="Search" value="<?php safer_echo($query); ?>"/>
    <input type="submit" value="Search" name="search"/>
</form> -->
<h3 class="text-center page-title"> My Accounts </h3>
<div class="results">
    <?php if (count($results) > 0): ?>
    <div class="row title">
        <div class="col">
            <div>Account Number</div>
        </div>
        <div class="col">
            <div>Account Type</div>
        </div>
        <div class="col">
            <div>Balance</div>
        </div>
    </div>

            <?php foreach ($results as $r): ?>
                <div class="row">
                    <div class="col">
                        <div><?php safer_echo($r["account_number"]); ?></div>
                    </div>
                    <div class="col">
                        <div><?php safer_echo($r["account_type"]); ?></div>
                    </div>
                    <div class="col">
                        <div><?php safer_echo($r["balance"]); ?></div>
                    </div>
                    <!-- <div>
                        <a type="button" href="test_edit_accounts.php?id=<?php safer_echo($r['id']); ?>">Edit</a>
                        <a type="button" href="test_view_accounts.php?id=<?php safer_echo($r['id']); ?>">View</a>
                    </div> -->
                </div>
            <?php endforeach; ?>
    <?php else: ?>
        <p>No results found</p>
    <?php endif; ?>
</div>
<?php require(__DIR__ . "/partials/flash.php"); ?>