<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<form method="POST">
    <label for="email">Email Address:</label>
    <input type="text" id="email" name="email" required/>
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required/>
    <label for="p1">Password:</label>
    <input type="password" id="p1" name="password" required/>
    <label for="p2">Confirm Password:</label>
    <input type="password" id="p2" name="confirm" required/>
    <input type="submit" name="reset" value="Reset"/>
</form>
<?php
$username = '';
if (isset($_POST["reset"])) {
    if (isset($_POST["email"])) {
        $email = $_POST["email"];
    }
    if (isset($_POST["password"])) {
        $password = $_POST["password"];
    }
    if (isset($_POST["confirm"])) {
        $confirm = $_POST["confirm"];
    }
    if (isset($_POST["username"])) {
        $username = $_POST["username"];
    }
    $isValid = true;

    if (!isset($email) || !isset($username)) {
        $isValid = false;
    }

    if ($isValid) {
        $db = getDB();
        if (isset($db)) {
            if($username != ''){
                $stmt = $db->prepare("SELECT id, email, username, password from Users WHERE username = :username AND email=:email LIMIT 1");
                $params = array(":username" => $username, ":email" => $email);
            }
            $r = $stmt->execute($params);
            // echo "db returned: " . var_export($r, true);
            $e = $stmt->errorInfo();
            if ($e[0] != "00000") {
                echo "uh oh something went wrong: " . var_export($e, true);
            }
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if(!$result){
                    flash("No user found, please enter a valid username or email");
                    $isValid = false;

            }
            if ($password == $confirm) {
                //not necessary to show
                //echo "Passwords match <br>";
            }
            else {
                flash("Passwords don't match");
                $isValid = false;
            }

            if ($isValid){
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $db->prepare("UPDATE Users set password=:password where id=:id");
                $r = $stmt->execute([
                    ":password"=>$hash,
                    ":id"=>$result["id"]
                ]);
                if($r){
                    flash("Password succesfully reset!");
                }
            }
            
        }
    }
    else {
        echo "There was a validation issue";
    }
}
if (!isset($email)) {
    $email = "";
}
if (!isset($username)) {
    $username = "";
}
?>
<?php require(__DIR__ . "/partials/flash.php"); ?>


