<?php
$session = true;
if( session_status() === PHP_SESSION_DISABLED  )
    $session = false;
elseif( session_status() !== PHP_SESSION_ACTIVE ){
  session_start();

  $logoutdisabled=false;
  $logindisabled=false;
  $display=true;

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

  if(isset($_SESSION["username"]) && !empty($_SESSION["username"]) && $_SESSION["username"]!=="ANONIMO"){
    $logindisabled=true;
  }

  if(isset($_REQUEST["pulisci"]) && $_REQUEST["pulisci"]=="pulisci"){
    $display=false;
  }
  $title="NUOVO UTENTE";
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <link href="homework.css" rel="stylesheet"/>
    <link rel="icon" href="/PoliTo.ico" />
    <meta name="author" content="Lorenzo Cascioli" >
    <title>NEW</title>

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

    function validaForm(a,b,c){
      if(validaNome(a)&&validaPassword(b)&&validaPassword(c)){
        if(b.value==c.value){
        return true;
      } else {
        alert("Inserire lo stesso valore per i due campi della password!");
        return false;
      }
      } else {
        return false;
      }
    }

  /**  function pulisci(){
      document.f.u.value="";
      document.f.p1.value="";
      document.f.p2.value="";
      return true;
    } fatta in php **/

    </script>


</head>
<body>
  <div class="grid-container">

  <?php require_once("header.inc"); ?>
  <?php require_once("menu.inc"); ?>

<div class="theMain">
<div class="section">
<p>Qui puoi registrare un nuovo utente.</p>
<p>Scegli username e password da usare per accedere ai nostri servizi.</p>

<p>Lo username dell’utente pu&ograve; contenere solo caratteri alfabetici o numerici o il simbolo %, deve iniziare con un carattere alfabetico o con %, deve essere lungo da un minimo di tre ad un massimo di sei caratteri e deve contenere almeno un carattere non numerico ed uno numerico.</p>
<p>La password dell’utente pu&ograve; contenere solo caratteri alfabetici, deve essere lunga minimo quattro e massimo otto caratteri ed avere almeno un carattere minuscolo ed uno maiuscolo.</p>


<form name="f" action="register.php" method="post" onsubmit="return validaForm(u,p1,p2);">
<div>Username:</div><div><input type="text" name="u" <?php if(!$display){echo("value=''");}?>></div>
<div>Password:</div><div><input type="password" name="p1" <?php if(!$display){echo("value=''");}?>></div>
<div>Ripeti password:</div><div><input type="password" name="p2" <?php if(!$display){echo("value=''");}?>></div>
<div><input type="submit" value="REGISTRAMI"></form></div><!--<input type="button" name="pulizia" value="Pulisci" onclick="return pulisci();">-->

<div><form name="pulizia" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
  <input name="pulisci" type="hidden" value="pulisci">
  <input type="submit" value="PULISCI">
</form></div>

</div>
</div>
<?php
include("footer.inc");
?></div>
</body>
</html>
