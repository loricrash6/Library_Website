<?php
$session = true;
if( session_status() === PHP_SESSION_DISABLED  )
    $session = false;
elseif( session_status() !== PHP_SESSION_ACTIVE ){
  session_start();

//Gestione abilitazione o meno pulsanti login/logout potrebbe essere fatta in un unico file include, ma essendo la pagina libri più complicata si è tenuto il codice su ogni pagina per evitare riassemblamenti, che sarebbe comunque probabilmente consigliabile fare per facilità di gestione
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

}
$title="HOME";
?>
<!DOCTYPE html>
<html lang="it">
<head>

    <meta charset="UTF-8">
    <link href="homework.css" rel="stylesheet"/>
    <link rel="icon" href="/PoliTo.ico" />
    <meta name="author" content="Lorenzo Cascioli" >
    <title>HOME</title>

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
  <p>Benvenuto alla home page del nostro servizio online per consultare e gestire i libri della biblioteca.</p>

  <p>Una volta effettuato il login, <a href="/libri.php">qui</a> potrai controllare e modificare i tuoi libri.<br>
  Se non sei registrato, puoi creare il tuo profilo <a href="/new.php">qui</a>. <!-- inserire link --></p>

</div>
<div class="section">
  <p>Il servizio online della biblioteca Cascioli offre una piattaforma innovativa e sicura per poter gestire in estrema comodit&agrave; le attivit&agrave; di consultazione e noleggio dai nostri scaffali virtuali.</p>
  <p>Ti ricordiamo che il limite massimo di libri che un utente pu&ograve; avere in prestito contemporaneamente &egrave; pari a 3.</p>
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
