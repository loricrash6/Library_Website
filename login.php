<?php

if(!isset($_COOKIE["username"])){
  setcookie("username","","/");
}

$session = true;
if( session_status() === PHP_SESSION_DISABLED  )
    $session = false;
elseif( session_status() !== PHP_SESSION_ACTIVE ){
  session_start();

  $logoutdisabled=false;
  $logindisabled=false;

  if(isset($_REQUEST["logout"])){
    $_SESSION["username"]="ANONIMO";
    $logoutdisabled=true;
    $_SESSION["numlibri"]=0;
  }

  if(!isset($_SESSION["username"]) || empty($_SESSION["username"])){
    $_SESSION["username"]="ANONIMO";
    $logoutdisabled=true;
  }

  if($_SESSION["username"]=="ANONIMO"){
    $logoutdisabled=true;
  }

  $userdisplay=true;

  if(isset($_REQUEST["pulisci"]) && $_REQUEST["pulisci"]=="pulisci"){
    $userdisplay=false;
  }
$title="LOGIN";
}

 ?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <link href="homework.css" rel="stylesheet"/>
    <link rel="icon" href="/PoliTo.ico" />
    <meta name="author" content="Lorenzo Cascioli" >
    <title>LOGIN</title>

    <script>
    function validaNome(nome){
      var valid=false;
      var n=nome.value;
      if(n==""){
        alert("Inserire uno username!");
        valid=false;
        return valid;
      } else if(n.length<3 || n.length>6){
        alert("Errore! Lo username deve avere tra i tre e i sei caratteri!");
        valid=false;
        return valid;
      } else {
        var regexp=/^[a-zA-Z%][a-zA-z0-9%]+$/;
        if(!regexp.test(n)){
          alert("Errore! Lo username può contenere solo caratteri alfanumerici o '%', e deve iniziare con un carattere alfabetico o con %!");
          valid=false;
          return valid;
        } else {
          var regexp1=/[^0-9]/;
          var regexp2=/[0-9]/;
          if(!regexp1.test(n)||!regexp2.test(n)){
            alert("Errore! Lo username deve contenere almeno un carattere numerico e uno non numerico!");
            valid=false;
            return valid;
          } else {
            valid=true;
            return valid;
          }
        }
      }
    }

    function validaPassword(pass){
      var valid=false;
      var p=pass.value;
      if(p==""){
        alert("Inserire una password!");
        valid=false;
        return valid;
      } else if(p.length<4 || p.length>8){
        alert("Errore! La password deve avere tra i quattro e gli otto caratteri!");
        valid=false;
        return valid;
      } else {
        var regexp=/^[a-zA-Z]+$/;
        if(!regexp.test(p)){
          alert("Errore! La password può contenere solo caratteri alfabetici!");
          valid=false;
          return valid;
        } else {
          var regexp1=/[A-Z]/;
          var regexp2=/[a-z]/;
          if(!regexp1.test(p)||!regexp2.test(p)){
            alert("Errore! La password deve contenere almeno un carattere minuscolo e uno maiuscolo!");
            valid=false;
            return valid;
      } else {
        valid=true;
        return valid;
      }
    }
  }
}

    function validaForm(a,b){
      if(validaNome(a)&&validaPassword(b)){
        return true;
      } else {
        return false;
      }
    }

/**function pulisci(){
  document.ff.u.value="";
  return true;
} tolta perchè PULISCI fatto in php **/
    </script>

</head>
<body>
  <?php if(!$session){
    echo("<p>Errore - sessioni non supportate...</p>");
  } else { ?>
  <div class="grid-container">

  <?php require_once("header.inc"); ?>
  <?php require_once("menu.inc"); ?>

<div class="theMain">
<div class="section">
  <p>
    Inserire username e password per accedere al database:
  </p>
    <form name="ff" action="libri.php" method="post" onsubmit="return validaForm(u,p);">
      <p> Username: <input type="text" name="u" value="<?php if(isset($_COOKIE["username"]) && ($userdisplay)){echo($_COOKIE["username"]);} else {echo("");} ?>"> </p> <!--CHECK!!!-->
      <p> Password: <input type="password" name="p"> </p>
    <input type="submit" value="OK">
  </form>
  <form name="pulizia" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
    <input name="pulisci" type="hidden" value="pulisci">
    <input type="submit" value="PULISCI">
  </form>

</div>
</div>

<?php
include("footer.inc");
?>
</div>
</body>
</html>
<?php
}
?>
