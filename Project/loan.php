<?php 
require_once(__DIR__ . "/partials/nav.php");
if (!is_logged_in()) {
    flash("You need to login first!");
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    die(header("Location: login.php"));
}?>
<?php
$id = get_user_id();
$results = [];
    $db = getDB();
    $stmt = $db->prepare("SELECT id, account_number, account_type, opened_date, last_updated, balance from Accounts WHERE user_id like :id");
    $r = $stmt->execute([":id" => $id]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }
?>

<form method="POST">
<label> Destination Account <label>
<select name="dest_acc" required>
        <?php foreach ($results as $account): ?>
            <option value="<?php safer_echo($account["account_number"]); ?>">
                <?php safer_echo($account["account_number"]); ?>
            </option>
        <?php endforeach; ?>
    </select>
	<label>Balance (minimum $500)</label>
	<input type="number" min="500" name="balance"/>
	<input type="submit" name="save" value="Get Loan"/>
</form>

<?php
if(isset($_POST["save"])){
	//TODO add proper validation/checks
    $bal = $_POST["balance"];
    if ($bal < 500){
        flash("Please enter a balance greater than $5");
    }else{
        $accNum = randomNumber(12);
        $dest_acc = $_POST["dest_acc"];
        $apy = 0.03;
        $opened = date('Y-m-d H:i:s');//calc
        $updated = $opened;
        $stmt = $db->prepare("INSERT INTO Accounts (account_number, account_type, opened_date, last_updated, user_id, APY, balance) VALUES(:accnum, :acctype, :opened, :updated, :user, :apy, :balance)");
        $r = $stmt->execute([
            ":accnum"=>$accNum,
            ":acctype"=>"Loan",
            ":opened"=>$opened,
            ":updated"=>$updated,
            ":user"=>$id,
            ":apy"=>$apy,
            ":balance"=>$bal,
        ]);
        if($r){
            var_dump($r);

            $stmt = $db->prepare("SELECT id FROM Accounts where account_number =:num");
            $r = $stmt->execute([":num" => $dest_acc]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                $e = $stmt->errorInfo();
                flash($e[2]);
            }else{
                $src_id = getWorldAccountId();
                var_dump($result);

                do_bank_action($src_id, $result['id'], $bal, "Deposit", "Got a loan", $opened);
                flash("Your new account has been created successfully!");
                header("Location:view_accounts.php");
            }
        }
        else{
            $e = $stmt->errorInfo();
            flash("Error creating: " . var_export($e, true));
        }
    }

}


?>
<?php require(__DIR__ . "/partials/flash.php"); ?>