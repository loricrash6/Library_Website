<?php
$echo=0;
$username;
$password;
function check(){
  global $username, $password, $echo;
  //controllo esistenza parametri
  if(isset($_REQUEST["u"]) && !empty($_REQUEST["u"]) && isset($_REQUEST["p1"]) && !empty($_REQUEST["p1"]) && isset($_REQUEST["p2"]) && !empty($_REQUEST["p2"])){
    if($_REQUEST["p1"]==$_REQUEST["p2"]){
    $username=trim($_REQUEST["u"]);
    $password=trim($_REQUEST["p1"]);
    //controllo lunghezza parametri
    if(strlen($username)>2 && strlen($username)<7 && strlen($password)>3 && strlen($password)<9){
      //controllo validità parametri
    if(preg_match('/^[a-zA-Z%][a-zA-z0-9%]+$/',$username) && preg_match('/[0-9]/',$username) && preg_match('/[^0-9]/',$username) && preg_match('/^[a-zA-Z]+$/',$password) && preg_match('/[A-Z]/',$password) && preg_match('/[a-z]/',$password)){
      //dati forniti sono validi: apro database e controllo se lo username è tra quelli già noti

      $con = mysqli_connect( "localhost","uReadWrite","SuperPippo!!!","biblioteca");
      if (mysqli_connect_errno()){
        $echo=1;
        return $echo;
        //printf ("<p>Errore - collegamento al DB impossibile: %s</p>\n", mysqli_connect_error());
      } else {
        //aperta la connessione, estraggo gli username e controllo se c'è già quello username in uso
        $stmt=mysqli_prepare( $con, "SELECT COUNT(*) FROM users WHERE username=?" );
        mysqli_stmt_bind_param($stmt, "s", $username);
        if(!mysqli_stmt_execute($stmt)){
          $echo=2;
          return $echo; //query fallita
        } else {

          mysqli_stmt_execute($stmt);
          mysqli_stmt_bind_result($stmt,$num);
          mysqli_stmt_fetch($stmt);

          mysqli_stmt_close($stmt);
          if($num>0){
          //username già presente in tabella utenti
          $echo=3;
          return $echo;
          //echo("Errore - username già in uso!");
          } else {
          //username libero: aggiungo in database nuova entry
          //NOTA: sarebbe sicuramente preferibile per ragioni di sicurezza non usare le password in chiaro ma i loro digest cifrati, ottenibili comodamente con la funzione di hash md5();
          //il sistema non è stato così implementato perchè non era richiesto e per non modificare la tabella users fornita (dove le password sono in chiaro)
          $query = "INSERT INTO users(username,pwd) VALUES (?,?)";
          $stmt = mysqli_prepare($con, $query);
          mysqli_stmt_bind_param($stmt, "ss", $username, $password);
          $result = mysqli_stmt_execute($stmt);
          if (!$result){
            $echo=4;
            return $echo;
            //printf ("<p>Errore – query di inserimento fallita: %s<p>\n", mysqli_error($con));
          } else {
            $echo=9;
            return $echo; //mission accomplished
          }
          }
          mysqli_stmt_close($stmt);
        }

      }
      mysqli_close($con);

    } else {
      $echo=5;
      return $echo;
      //echo("<p>Errore. Lo username può contenere solo caratteri alfanumerici o il simbolo %, deve iniziare con un carattere alfabetico o con %, deve essere lungo da un minimo di tre ad un massimo di sei caratteri e deve contenere almeno un carattere non numerico ed uno numerico.<br>
      //      La password può contenere solo caratteri alfabetici, deve essere lunga minimo quattro e massimo otto caratteri ed avere almeno un carattere minuscolo ed uno maiuscolo.</p>");
    }
  } else {
    $echo=6;
    return $echo;
    //echo("<p>Errore. La lunghezza dello username dev'essere compresa tra 3 e 6 caratteri, quella della password tra 4 e 8 caratteri.</p>");
  }
} else {
  $echo=7;
  return $echo;
  //p1!=p2
}
  } else{
    $echo=8;
    return $echo;
    //echo("<p>Errore! Username e/o password mancante...</p>");
  }
}

check();

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

if(isset($_SESSION["username"]) && !empty($_SESSION["username"]) && $_SESSION["username"]!=="ANONIMO"){
  $logindisabled=true;
}

$title="REGISTRAZIONE";
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <link href="homework.css" rel="stylesheet"/>
    <link rel="icon" href="/PoliTo.ico" />
    <meta name="author" content="Lorenzo Cascioli" >
    <title>REGISTRAZIONE</title>
</head>
<body>
  <div class="grid-container">

  <?php require_once("header.inc"); ?>
  <?php require_once("menu.inc"); ?>

  <div class="theMain">
    <div class="section">
<?php
switch($echo){
  case 1: echo("<p>Errore - collegamento al DB impossibile.</p>");
          $proceed=false;
          break;
  case 2: echo("<p>Errore – query di lettura fallita</p>");
          $proceed=false;
          break;
  case 3: echo("<p>Errore - username gi&agrave; in uso... Riprova la <a href='/new.php'>registrazione</a>.</p>");
          $proceed=false;
          break;
  case 4: echo("<p>Errore - query di inserimento fallita...</p>");
          $proceed=false;
          break;
  case 5: echo("<p>Errore. Lo username pu&ograve; contenere solo caratteri alfanumerici o il simbolo %, deve iniziare con un carattere alfabetico o con %, deve essere lungo da un minimo di tre ad un massimo di sei caratteri e deve contenere almeno un carattere non numerico ed uno numerico.<br>
          La password pu&ograve; contenere solo caratteri alfabetici, deve essere lunga minimo quattro e massimo otto caratteri ed avere almeno un carattere minuscolo ed uno maiuscolo. Riprova la <a href='/new.php'>registrazione</a>.</p>");
          $proceed=false;
          break;
  case 6: echo("<p>Errore. La lunghezza dello username dev'essere compresa tra 3 e 6 caratteri, quella della password tra 4 e 8 caratteri. Riprova la <a href='/new.php'>registrazione</a>.</p>");
          $proceed=false;
          break;
  case 7: echo("<p>Errore - nei due campi per la password sono stati inseriti valori diversi! Riprova la <a href='/new.php'>registrazione</a>.</p>");
          $proceed=false;
          break;
  case 8: echo("<p>Errore! Username e/o password mancante... Riprova la <a href='/new.php'>registrazione</a>.</p>");
          $proceed=false;
          break;
  case 9: echo("Registrazione completata! Torna alla <a href='/home.php'>home</a> o fai <a href='/login.php'>login</a> per iniziare la navigazione.");
          $proceed=true;
          break;
  default: echo("Errore inatteso... Riprova la <a href='/new.php'>registrazione</a>.");
          $proceed=false;
          break;

} ?>
</div>
</div>
<?php
include("footer.inc");
?> </div>
</body>
</html>
