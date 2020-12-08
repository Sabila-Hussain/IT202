
<link rel="stylesheet" href="static/css/styles.css">
<!-- CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

<!-- jQuery and JS bundle w/ Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>

<?php
//we'll be including this on most/all pages so it's a good place to include anything else we want on those pages
require_once(__DIR__ . "/../lib/helpers.php");
?>
<nav>
<ul class="nav">
    <li><a href="home.php">Home</a></li>
    <?php if (!is_logged_in()): ?>
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
    <?php endif; ?>
    <?php if (has_role("Admin")): ?>
            <li><a href="test_create_accounts.php">Create Account</a></li>
            <li><a href="test_list_accounts.php">View Accounts</a></li>
            <li><a href="test_create_transactions.php">Create Transaction</a></li>
            <li><a href="test_list_transactions.php">View Transactions</a></li>
    <?php endif; ?>
    <?php if (is_logged_in()): ?>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="create_account.php">Open Account</a></li>
        <li><a href="view_accounts.php">My Accounts</a></li>



    <?php endif; ?>
</ul>
</nav>