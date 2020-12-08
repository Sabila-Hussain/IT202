<?php
session_start();//we can start our session here so we don't need to worry about it on other pages
require_once(__DIR__ . "/db.php");
//this file will contain any helpful functions we create
//I have provided two for you
function is_logged_in() {
    return isset($_SESSION["user"]);
}

function has_role($role) {
    if (is_logged_in() && isset($_SESSION["user"]["roles"])) {
        foreach ($_SESSION["user"]["roles"] as $r) {
            if ($r["name"] == $role) {
                return true;
            }
        }
    }
    return false;
}

function get_username() {
    if (is_logged_in() && isset($_SESSION["user"]["username"])) {
        return $_SESSION["user"]["username"];
    }
    return "";
}

function get_email() {
    if (is_logged_in() && isset($_SESSION["user"]["email"])) {
        return $_SESSION["user"]["email"];
    }
    return "";
}

function get_user_id() {
    if (is_logged_in() && isset($_SESSION["user"]["id"])) {
        return $_SESSION["user"]["id"];
    }
    return -1;
}

function safer_echo($var) {
    if (!isset($var)) {
        echo "";
        return;
    }
    echo htmlspecialchars($var, ENT_QUOTES, "UTF-8");
}

//for flash feature
function flash($msg) {
    if (isset($_SESSION['flash'])) {
        array_push($_SESSION['flash'], $msg);
    }
    else {
        $_SESSION['flash'] = array();
        array_push($_SESSION['flash'], $msg);
    }

}
//end flash


function getMessages() {
    if (isset($_SESSION['flash'])) {
        $flashes = $_SESSION['flash'];
        $_SESSION['flash'] = array();
        return $flashes;
    }
    return array();
}

function randomNumber($length) {
    $result = '';

    for($i = 0; $i < $length; $i++) {
        $result .= mt_rand(0, 9);
    }

    return $result;
}
function getWorldAccountId(){
    $db = getDB();
    global $worldId;
    if (!isset($worldId) || empty($worldId)){
        $stmt = $db->prepare("SELECT id FROM Accounts where account_number = '000000000000'");
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if($r){
            $worldId = intval($r["id"]);
        }
    }
    return $worldId;
}
function do_bank_action($account1, $account2, $amountChange, $type, $memo, $date){
    $db = getDB();
    $stmt = $db ->prepare("SELECT balance FROM Accounts WHERE id=:id");
    $r = $stmt->execute([ ":id" => $account1]);
    $src =$stmt->fetch(PDO::FETCH_ASSOC);
    $src_total =$src['balance'];

    if ($src_total < $amountChange){
        flash ("You do not have enough money available for this transaction");
        return;
    }

    $src_total -= $amountChange;

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
function updateAccount($id, $bal, $date){
    $db = getDB();
    $stmt = $db->prepare("UPDATE Accounts set balance=:bal, last_updated=:updated where id=:id");
    $r = $stmt->execute([
        ":bal"=>$bal,
        ":updated"=>$date,
        ":id"=>$id
    ]);
    if($r){
        return $r;
    }
    else{
        $e = $stmt->errorInfo();
        flash("Error updating: " . var_export($e, true));
    }
    return $r;
}
?>