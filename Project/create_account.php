<?php 
require_once(__DIR__ . "/partials/nav.php");
if (!is_logged_in()) {
    flash("You need to login first!");
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    die(header("Location: login.php"));
}?>

<form method="POST">
	<label>Account Type</label>
	<select name="account_type">
		<option value="Checking">Checking</option>
		<!-- <option value="Saving">Saving</option> -->
		<!-- <option value="Loan">Loan</option> -->
	</select>
	<label>Balance</label>
	<input type="number" min="0" name="balance"/>
	<input type="submit" name="save" value="Create"/>
</form>

<?php
if(isset($_POST["save"])){
	//TODO add proper validation/checks
    $bal = $_POST["balance"];
    if ($bal < 5){
        flash("Please enter a balance greater than $5");
    }else{
        $accNum = randomNumber(12);
        $accType = $_POST["account_type"];
        $opened = date('Y-m-d H:i:s');//calc
        $updated = $opened;
        $user = get_user_id();
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO Accounts (account_number, account_type, opened_date, last_updated, user_id) VALUES(:accnum, :acctype, :opened, :updated, :user)");
        $r = $stmt->execute([
            ":accnum"=>$accNum,
            ":acctype"=>$accType,
            ":opened"=>$opened,
            ":updated"=>$updated,
            ":user"=>$user
        ]);
        if($r){
            $stmt = $db->prepare("SELECT id FROM Accounts where account_number =:num");
            $r = $stmt->execute([":num" => $accNum]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                $e = $stmt->errorInfo();
                flash($e[2]);
            }else{
                $src_id = getWorldAccountId();
                // var_dump($result);

                do_bank_action($src_id, $result['id'], $bal, "Deposit", "created new account", $opened);
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
<?php require(__DIR__ . "/partials/flash.php");?>