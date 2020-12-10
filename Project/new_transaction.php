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
?>

    <nav class="navbar justify-content-center navbar-custom navbar-expand-sm">
        <ul class="navbar-nav mr-auto">
        <?php if ($type=="Deposit"):?>
            <li class="nav-item active nav-custom-primary "><a href="?type=Deposit" class="nav-link">Deposit</a>
            </li>
        <?php else: ?>
            <li class="nav-item "><a href="?type=Deposit" class="nav-link">Deposit</a>
            </li>
        <?php endif; ?>
        <?php if ($type=="Withdraw"):?>
            <li class="nav-item active nav-custom-primary"><a href="?type=deposit" class="nav-link">Withdraw</a>
            </li>
        <?php else: ?>
            <li class="nav-item "><a href="?type=Withdraw" class="nav-link">Withdraw</a>
            </li>
        <?php endif; ?>
        <?php if ($type=="Transfer"):?>
            <li class="nav-item active nav-custom-primary"><a href="?type=Transfer" class="nav-link">Transfer</a>
            </li>
        <?php else: ?>
            <li class="nav-item "><a href="?type=Transfer" class="nav-link">Transfer</a>
            </li>
        <?php endif; ?>

        </ul>
    </nav>

<form method="POST">

    <label>Source Account</label>
    <select name="src_acc" required>
        <?php foreach ($acc_results as $account): ?>
            <option value="<?php safer_echo($account["id"]); ?>">
                <?php safer_echo($account["account_number"]); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php if ($type=="Transfer"):?>
        <label>Destination Account</label>
        <select name="dest_acc" required>
            <?php foreach ($acc_results as $account): ?>
                <option value="<?php safer_echo($account["id"]); ?>">
                    <?php safer_echo($account["account_number"]); ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>
    <label>Memo</label>
	<input name="memo"/>

	<label>Amount</label>
	<input type="number" min="0" name="amount" required/>
	<input type="submit" name="save" value="Complete Transaction"/>
</form>

<?php


if(isset($_POST["save"])){
    $src_id = $_POST["src_acc"];
    if($src_id == '-1'){
        flash("please choose an account");
    }
    $amount = $_POST["amount"];
    $dest_id='';
    // check if amount greater than balance

    $memo = $_POST["memo"];
    $created = date('Y-m-d H:i:s');//calc

    if($type == "Deposit"){
        $dest_id = $src_id;
        $src_id = getWorldAccountId();
    }elseif($type == "Withdraw"){
        $dest_id = getWorldAccountId();
    }else{
        if($dest_id == '-1'){
            flash("please choose a destination account");
        }
        $dest_id = $_POST["dest_acc"];
    }

    $res = do_bank_action($src_id, $dest_id, $amount, $type, $memo, $created);
    if ($res){
        flash("Transaction Successful");
    }
}
?>
<?php require(__DIR__ . "/partials/flash.php"); ?>