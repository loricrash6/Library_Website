
<div class="menu1"><a href="/home.php">HOME</a></div>
<div class="menu2"><?php if(!$logindisabled){echo("<a href='/login.php'>LOGIN</a>");} else {echo("LOGIN");}?></div>
<div class="menu3"><a href="/new.php">NEW</a></div>
<div class="menu4"><a href="/libri.php">LIBRI</a></div>
<div class="menu5">Utente: <?php echo($_SESSION["username"]); ?></div>
<div class="menu6">Libri in prestito: <?php if(isset($_SESSION["numlibri"])){echo($_SESSION["numlibri"]);}else{echo("0");}?></div>
<div class="menu7"><form name="pulsante" method="post" action="/home.php">
    <input name="logout" type="hidden" value="logout">
    <input class="bottone" type="submit" value="LOGOUT" <?php if($logoutdisabled){echo("disabled");} ?>>
</form></div>
