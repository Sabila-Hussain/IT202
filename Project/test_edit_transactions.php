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
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>

<?php
    function updateTransactions($db, $id, $newMemo, $newTotal, $amount){
        $stmt = $db->prepare("UPDATE Transactions set amount=:amount, memo=:memo, expected_total=:total where id=:id");
        $r = $stmt->execute([
            ":amount"=>$amount,
            ":memo"=>$newMemo,
            ":total"=>$newTotal,
            ":id"=>$id
        ]);
        if(!$r){
            $e = $stmt->errorInfo();
            flash("Error updating: " . var_export($e, true));
        }
        return;
    }
?>

<?php
//fetching and saving
if(isset($id)){
    $src_result = [];
    $dest_result = [];
    $id2 = $id + 1;

    $db = getDB();

    $stmt = $db->prepare("SELECT * FROM Transactions where id = :id");
    $stmt->execute([":id"=>$id]);
    $src_result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $db->prepare("SELECT * FROM Transactions where id = :id");
    $stmt->execute([":id"=>$id2]);
    $dest_result = $stmt->fetch(PDO::FETCH_ASSOC);
    

    // var_dump($src_result, $dest_result);

    if (isset($_POST["save"])){
        $newamount = $_POST["amount"];
        $newMemo = $_POST["memo"];
        $newSrc_EstTotal = $src_result['expected_total'] + floatval($src_result['amount'])*-1 - $newamount;
        $newDest_EstTotal = $dest_result['expected_total'] - floatval($dest_result['amount']) + $newamount;
        updateTransactions($db, $id, $newMemo, $newSrc_EstTotal, $newamount*-1);
        updateTransactions($db, $id2, $newMemo, $newDest_EstTotal, $newamount);
    }
}else{
    flash("Something went wrong. ID might not be set.");
}
?>

<form method="POST">

    <label>Memo</label>
	<input name="memo" value= "<?php echo $src_result["memo"]; ?>"/>

	<label>Amount</label>
	<input type="number" min="0" name="amount" value="<?php echo $dest_result["amount"];?>"/>
	<input type="submit" name="save" value="Update"/>
</form>
<?php require(__DIR__ . "/partials/flash.php"); ?>