<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
    if (!is_logged_in()) {
    flash("You need to login first!");
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    die(header("Location: login.php"));
    }
   
    $type='';
    if (isset($_GET["type"])) {
        $type = $_GET["type"];
    }else{
            flash("An error has occured");
    }

    //fetching accounts
    $acc_results = [];
    $user = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Accounts where user_id = :id");
    $stmt->execute([":id" => $user]);
    $acc_results = $stmt->fetchall(PDO::FETCH_ASSOC);
    // var_dump($acc_results)

    function do_bank_extTransfer($account1, $lastName, $accNum, $amountChange, $type, $memo, $date){
        $db = getDB();
        $stmt = $db ->prepare("SELECT balance FROM Accounts WHERE id=:id");
        $r = $stmt->execute([ ":id" => $account1]);
        $src =$stmt->fetch(PDO::FETCH_ASSOC);
        $src_total =$src['balance'];
    
        if ($src_total < $amountChange){
            flash ("You do not have enough money available for this transaction");
            return false;
        }
    
        $src_total -= $amountChange;

        $stmt = $db ->prepare("SELECT Accounts.id FROM Accounts JOIN Users on Users.id=Accounts.user_id WHERE Accounts.account_number like :accNum AND Users.lastName like :lastName");
        $r = $stmt->execute([ ":accNum" => "%$accNum", ":lastName" => "%$lastName%"]);
        if ($r) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        else {
            $e = $stmt->errorInfo();
        flash($e[2]);
        }
        $account2 = $result['id'];
        $stmt = $db ->prepare("SELECT balance FROM Accounts WHERE id=:id");
        $r = $stmt->execute([ ":id" => $account2]);
        $dest = $stmt->fetch(PDO::FETCH_ASSOC);
        $dest_total =$dest['balance'];
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
            updateAccount($account1, $src_total, $date);
            updateAccount($account2, $dest_total, $date);
        }
        else{
            $e = $stmt->errorInfo();
            flash("Error creating: " . var_export($e, true));
        }
        return $r;
    }
?>

<form method="POST">

    <label>Source Account</label>
    <select name="src_acc" required>
        <?php foreach ($acc_results as $account): ?>
            <option value="<?php safer_echo($account["id"]); ?>">
                <?php safer_echo($account["account_number"]); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Destination Account Number (Last 4 Digits)</label>
	<input type="number" min="0" name="accnum" required/>

    <label for="lastName">Destination Account Holder Last Name</label>
    <input type="text" maxlength="60" name="lastName" required/>

    <label>Memo</label>
	<input name="memo"/>

	<label>Amount</label>
	<input type="number" min="0" name="amount" required/>
	<input type="submit" name="save" value="Complete Transaction"/>
</form>

<?php


if(isset($_POST["save"])){
    $src_id = $_POST["src_acc"];
    $amount = $_POST["amount"];
    $dest_id='';
    // check if amount greater than balance

    $memo = $_POST["memo"];
    $created = date('Y-m-d H:i:s');//calc
    if(isset($_POST["lastName"]) && isset($_POST["accnum"])){
        $lastName = $_POST["lastName"];
        $accNum = $_POST["accnum"];
        $res = do_bank_extTransfer($src_id, $lastName, $accNum, $amount, "Ext-Transfer", $memo, $created);
        if ($res){
            flash("Transaction Successful");
        }
    }else{
        flash("Please fill the required fields");
    }
}
?>
<?php require(__DIR__ . "/partials/flash.php"); ?>