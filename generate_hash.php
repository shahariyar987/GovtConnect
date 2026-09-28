<?php
// generate_hash.php
$passwords = ['admin123','rahim123','police123'];
foreach($passwords as $pass){
    echo $pass . " => " . password_hash($pass, PASSWORD_BCRYPT) . "<br>";
}
?>
