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

<?php

function do_bank_action($account1, $account2, $amountChange, $type, $memo, $date){
    $db = getDB();
    // $stmt = $db->prepare("SELECT * FROM Accounts where id = :id");
    // $r = $stmt->execute([":id" => $account1]);
    // $src_acc = $stmt->fetch(PDO::FETCH_ASSOC);

    // $src_acc_total = $src_acc['balance'];

    // $stmt = $db->prepare("SELECT * FROM Accounts where id = :id");
    // $r = $stmt->execute([":id" => $account2]);
    // $dest_acc = $stmt->fetch(PDO::FETCH_ASSOC);

    // $dest_acc_total = $dest_acc['balance'];


    $stmt = $db ->prepare("SELECT SUM(amount) AS Total FROM Transactions WHERE Transactions.act_src_id = :id");
    $r = $stmt->execute([ ":id" => $account1]);
    $src =$stmt->fetch(PDO::FETCH_ASSOC);
    $src_total =$src['Total'];

    $src_total -= $amountChange;

    $stmt = $db ->prepare("SELECT SUM(amount) AS Total FROM Transactions WHERE Transactions.act_src_id = :id");
    $r = $stmt->execute([ ":id" => $account2]);
    $dest = $stmt->fetch(PDO::FETCH_ASSOC);
    $dest_total =$dest['Total'];
    $dest_total += $amountChange;

	$query = "INSERT INTO `Transactions` (`act_src_id`, `act_dest_id`, `amount`, `action_type`, `memo`, `expected_total`, `created`) 
	VALUES(:p1a1, :p1a2, :p1change, :type, :memo, :a1total, :date), 
			(:p2a1, :p2a2, :p2change, :type, :memo, :a2total, :date)";
	
	$stmt = $db->prepare($query);
	$stmt->bindValue(":p1a1", $account1);
	$stmt->bindValue(":p1a2", $account2);
	$stmt->bindValue(":p1change", $amountChange*-1);
    $stmt->bindValue(":type", $type);
    $stmt->bindValue(":memo", $memo);
    $stmt->bindValue(":a1total", $src_total);
    $stmt->bindValue(":date", $date);
	//flip data for other half of transaction
	$stmt->bindValue(":p2a1", $account2);
	$stmt->bindValue(":p2a2", $account1);
	$stmt->bindValue(":p2change", ($amountChange));
    $stmt->bindValue(":type", $type);
    $stmt->bindValue(":memo", $memo);
    $stmt->bindValue(":a2total", $dest_total);
    $stmt->bindValue(":date", $date);
	$r = $stmt->execute();
	if($r){
		flash("Transaction successful");
	}
	else{
		$e = $stmt->errorInfo();
		flash("Error creating: " . var_export($e, true));
	}
	return $r;
}
?>

<?php

//fetching accounts
    $acc_results = [];
    $user = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Accounts where user_id = :id");
    $stmt->execute([":id" => $user]);
    $acc_results = $stmt->fetchall(PDO::FETCH_ASSOC);
    // var_dump($acc_results)
?>

<form method="POST">

	<label>Transaction Type</label>
	<select name="type">
		<option value="Deposit">Deposit</option>
		<option value="Withdraw">Withdraw</option>
		<option value="Transfer">Transfer</option>
	</select>

    <label>Source Account</label>
    <select name="src_acc" required>
        <?php foreach ($acc_results as $account): ?>
            <option value="<?php safer_echo($account["id"]); ?>">
                <?php safer_echo($account["account_number"]); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Destination Account</label>
    <select name="dest_acc" required >
        <?php foreach ($acc_results as $account): ?>
            <option value="<?php safer_echo($account["id"]); ?>">
                <?php safer_echo($account["account_number"]); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Memo</label>
	<input name="memo"/>

	<label>Amount</label>
	<input type="number" min="0" name="amount" required/>
	<input type="submit" name="save" value="Create"/>
</form>



<?php

if(isset($_POST["save"])){
    $src_id = $_POST["src_acc"];
    $dest_id = $_POST["dest_acc"];
    $type =  $_POST["type"];
    $amount = $_POST["amount"];

    // check if amount greater than balance

    $memo = $_POST["memo"];
    $created = date('Y-m-d H:i:s');//calc

    if($type == "Deposit"){
        $src_id = getWorldAccountId();
    }elseif($type == "Withdraw"){
        $dest_id = getWorldAccountId();
    }

    do_bank_action($src_id, $dest_id, $amount, $type, $memo, $created);
}
?>
<?php require(__DIR__ . "/partials/flash.php"); ?>