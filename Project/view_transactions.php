<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
    if (!is_logged_in()) {
    flash("You need to login first!");
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    die(header("Location: login.php"));
    }
    
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

function viewAll($id, $offset, $per_page){

    $db = getDB();
    $stmt = $db->prepare("SELECT id, act_dest_id, action_type, amount, memo, expected_total from Transactions WHERE act_src_id =:id ORDER BY created DESC LIMIT :offset, :count");    
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
    $stmt->bindValue(":id", $id);
    $stmt->execute();
    $e = $stmt->errorInfo();
    if($e[0] != "00000"){
        flash(var_export($e, true), "alert");
    }
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $results;

}
function viewWithType($id, $type, $offset, $per_page){
    $db = getDB();
    $stmt = $db->prepare("SELECT id, act_dest_id, action_type, amount, memo, expected_total from Transactions WHERE act_src_id =:q AND action_type=:type ORDER BY created DESC LIMIT 10");
    $r = $stmt->execute([":q" => $id, ":type" => $type]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }
    else {
        flash("There was a problem fetching the results");
    }
}

function dateFilter($id, $offset, $per_page, $postat_from, $post_at_todate){
    $stmt = $db->prepare("SELECT id, act_dest_id, action_type, amount, memo, expected_total from Transactions WHERE created BETWEEN :postat AND :todate ORDER BY created DESC");
    $r = $stmt->execute([":postat" => $postat_from ,":todate" => $post_at_todate]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    } 
}

$db = getDB();
$results = [];

$page = 1;
$per_page = 10;
$filter = "viewAll";
if(isset($_GET["page"])){
    try {
        $page = (int)$_GET["page"];
    }
    catch(Exception $e){

    }
}else{
    $results = viewAll($id, 1, 10);
}

// $acc_results = [];
//get user's src 
// $user = get_user_id();
// $stmt = $db->prepare("SELECT * FROM Accounts where user_id = :id");
// $stmt->execute([":id" => $user]);
// $acc_results = $stmt->fetchall(PDO::FETCH_ASSOC);

$src = get_acc_num($id);

//get total # of  transactions for source
$stmt = $db->prepare("SELECT count(*) as total from Transactions where act_src_id = :id");
$stmt->execute([":id"=>$id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$total = 0;
if($result){
    $total = (int)$result["total"];
}
$total_pages = ceil($total / $per_page);
$offset = ($page-1) * $per_page;

if(isset($_GET["filter"])){
    $filter = $_GET["filter"];
    if ($filter = "Deposit"){
        $results = viewWithType($id, "Deposit", $offset, $per_page);
    }elseif($filter = "Withdraw"){
        $results = viewWithType($id, "Withdraw", $offset, $per_page);
    }elseif($filter = "Transfer"){
        $results = viewWithType($id, "Transfer", $offset, $per_page);
    }elseif($filter = "byDate"){
    }elseif($filter = "Ext-Transfer"){
        $results = viewWithType($id, "Ext-Transfer", $offset, $per_page);
    }elseif($filter = "byDate"){
        $results = dateFilter($id, $offset, $per_page, $_GET["postat_from"], $_GET["post_at_todate"]);
    }else{
        $results = viewAll($id, $offset, $per_page);
    }

}


?>

<?php 
$post_at = "";
$post_at_to_date = "";
$postat_from = "";
$post_at_todate="";
if(!empty($_POST["search"]["post_at"])) {			
    $post_at = $_POST["search"]["post_at"];
    list($fim,$fid,$fiy) = explode("-",$post_at);
    $postat_from = "$fiy-$fim-$fid";


    if(!empty($_POST["search"]["post_at_to_date"])) {
        $post_at_to_date = $_POST["search"]["post_at_to_date"];
        list($tim,$tid,$tiy) = explode("-",$post_at_to_date);
        $post_at_todate = "$tiy-$tim-$tid";
        $results = dateFilter($id, 1, 10, $postat_from, $post_at_todate);
    
    }
    
}
    if(!empty($_POST["viewAll"])){
        $filter = "viewAll";
        $results = viewAll($id, 1, 10);
    }elseif(!empty($_POST["Deposit"])){
        $filter = "Deposit";
        $results = viewWithType($id, "Deposit",1, 10);
    }elseif(!empty($_POST["Withdraw"])){
        $filter = "Withdraw";
        $results = viewWithType($id, "Withdraw", 1, 10);
    }elseif(!empty($_POST["Transfer"])){
        $filter = "Transfer";
        $results = viewWithType($id, "Transfer", 1, 10);
    }elseif(!empty($_POST["Ext-Transfer"])){
        $filter = "Ext-Transfer";
        $results = viewWithType($id, "Ext-Transfer", 1, 10);
    }
?>
<h3 class="text-center page-title"> Account: <?php safer_echo($src); ?> </h3>

<form name="frmSearch" method="post" action="">
	 <p class="search_input">
		<input type="text" placeholder="From mm-dd-yyyy" id="post_at" name="search[post_at]"  value="<?php echo $post_at; ?>" class="input-control" />
	    <input type="text" placeholder="To mm-dd-yyyy" id="post_at_to_date" name="search[post_at_to_date]"  value="<?php echo $post_at_to_date; ?>" class="input-control"  />			 
        <input type="submit" name="go" value="Search" >
        <input type="submit" name="viewAll" value="View All Transactions" >
        <input type="submit" name="Deposit" value="View All Deposits" >
        <input type="submit" name="Withdraw" value="View All Withdrawals" >
        <input type="submit" name="Transfer" value="View All Transfers" >
        <input type="submit" name="Ext-Transfer" value="View All External Transfers" >


	</p>
</form>


<div class="results">
    <?php if (count($results) > 0): ?>

    <div class="row text-center title">
        <div class="col myCol">
            <div>Account Number (Dest)</div>
        </div>
        <div class="col myCol">
            <div>Trans Type</div>
        </div>
        <div class="col myCol">
            <div>Amount</div>
        </div>
        <div class="col myCol">
            <div>Memo</div>
        </div>
        <div class="col myCol">
            <div>Balance</div>
        </div>
    </div>

            <?php foreach ($results as $r): 
                    $dest = get_acc_num($r["act_dest_id"]);
                ?>
                <div class="row text-center">

                    <div class="col myCol">
                        <div><?php safer_echo($dest); ?></div>
                    </div>
                    <div class="col myCol">
                        <div><?php safer_echo($r["action_type"]); ?></div>
                    </div>
                    <div class="col myCol myBal">
                        <div><?php safer_echo($r["amount"]); ?></div>
                    </div>
                    <div class="col myCol">
                        <div><?php safer_echo($r["memo"]); ?></div>
                    </div>
                    <div class="col myCol myBal">
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

<nav aria-label="My Transactions">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo ($page-1) < 1?"disabled":"";?>">
            <a class="page-link" href="?id= <?php echo $id;?>&page= <?php echo $page-1;?>&filter=<?php $filter?>;?>&fromD=<?php $postat_from?>;?>&toD=<?php $post_at_todate?>" tabindex="-1">Previous</a>
        </li>
        <?php for($i = 0; $i < $total_pages; $i++):?>
        <li class="page-item <?php echo ($page-1) == $i?"active":"";?>"><a class="page-link" href="?id= <?php echo $id;?>&page=<?php echo ($i+1);?>&filter=<?php $filter?>;?>&fromD=<?php $postat_from?>;?>&toD=<?php $post_at_todate?>"><?php echo ($i+1);?></a></li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($page) >= $total_pages?"disabled":"";?>">
            <a class="page-link" href="?id= <?php echo $id;?>&page=<?php echo $page+1;?>&filter=<?php $filter?>;?>&fromD=<?php $postat_from?>;?>&toD=<?php $post_at_todate?>">Next</a>
        </li>
    </ul>
</nav>


<?php require(__DIR__ . "/partials/flash.php"); ?>
