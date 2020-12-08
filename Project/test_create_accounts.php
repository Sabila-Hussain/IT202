<?php 
require_once(__DIR__ . "/partials/nav.php");
require_once(__DIR__ . "/lib/helpers.php");
?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<form method="POST">
	<label>Account Type</label>
	<select name="account_type">
		<option value="Checking">Checking</option>
		<option value="Saving">Saving</option>
		<option value="Loan">Loan</option>
	</select>
	<label>Balance</label>
	<input type="number" min="0" name="balance"/>
	<input type="submit" name="save" value="Create"/>
</form>

<?php
if(isset($_POST["save"])){
	//TODO add proper validation/checks
    $accNum = randomNumber(12);
	$accType = $_POST["account_type"];
	$bal = $_POST["balance"];
    $opened = date('Y-m-d H:i:s');//calc
    $updated = $opened;
	$user = get_user_id();
	$db = getDB();
	$stmt = $db->prepare("INSERT INTO Accounts (account_number, account_type, opened_date, last_updated, balance, user_id) VALUES(:accnum, :acctype, :opened, :updated, :bal, :user)");
	$r = $stmt->execute([
		":accnum"=>$accNum,
		":acctype"=>$accType,
		":opened"=>$opened,
		":updated"=>$updated,
		":bal"=>$bal,
		":user"=>$user
	]);
	if($r){
		flash("Created successfully with id: " . $db->lastInsertId());
	}
	else{
		$e = $stmt->errorInfo();
		flash("Error creating: " . var_export($e, true));
	}
}
?>
<?php require(__DIR__ . "/partials/flash.php");?>