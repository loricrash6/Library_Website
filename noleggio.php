<?php
$session = true;
if( session_status() === PHP_SESSION_DISABLED  )
    $session = false;
elseif( session_status() !== PHP_SESSION_ACTIVE ){
  session_start();

$title="NOLEGGIO";

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

$echo=0;
$erroreSuo=array();
$erroreTuo=array();
$nonEsiste=array();

function check(){
global $giorni,$giorninoleggio,$nrichiesti,$erroreSuo,$erroreTuo,$nonEsiste,$echo;

if(!($_SESSION["username"]=="ANONIMO")){
if(isset($_REQUEST["giorni"]) && !empty($_REQUEST["giorni"]) && preg_match('/^[1-9][0-9]*$/',$_REQUEST["giorni"]) && strlen($_REQUEST["giorni"])<3){

$giorni=trim($_REQUEST["giorni"]);
$nrichiesti=0;

foreach($_REQUEST as $i=>$v){
  if($v=="si"){
    ++$nrichiesti;
  }
}
//innanzitutto leggo per controllare che non stiano venendo richiesti libri non validi
$con = mysqli_connect( "localhost","uReadWrite","SuperPippo!!!","biblioteca");
if (mysqli_connect_errno()){
  //printf ("<p>Errore - collegamento al DB impossibile: %s</p>\n", mysqli_connect_error());
  $echo=1;
  return $echo;
} else {

foreach($_REQUEST as $i=>$v){
  if($v=="si"){
    $id=$i;
    //ID del libro esiste?
    $stmt=mysqli_prepare($con,"SELECT COUNT(*) FROM books WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if(!mysqli_stmt_execute($stmt)){
      //echo("<p>Errore nella lettura del database!</p>");
      $echo=2;
      return $echo;
    } else {
      mysqli_stmt_execute($stmt);
      mysqli_stmt_bind_result($stmt,$numid);
      mysqli_stmt_fetch($stmt);
      if(!($numid==1)){
        $nonEsiste[$i]=$i;
        --$nrichiesti;
        unset($_REQUEST[$i]);
      }
    }
    mysqli_stmt_close($stmt);
  }
}


//libri già in possesso dell'utente attuale?
$stmt=mysqli_prepare($con,"SELECT id,titolo FROM books WHERE prestito=?");
mysqli_stmt_bind_param($stmt, "s", $_SESSION["username"]);
if(!mysqli_stmt_execute($stmt)){
  //echo("<p>Errore nella lettura del database!</p>");
  $echo=2;
  return $echo;
} else {
  mysqli_stmt_execute($stmt);
  mysqli_stmt_bind_result($stmt,$booksid,$titoli);
  while(mysqli_stmt_fetch($stmt)){

    foreach($_REQUEST as $i=>$v){
      if($v=="si"){
        if($i==$booksid){
          //echo("<p>Errore! Sembra che il libro '".$titoli."' sia gi&agrave; in tuo possesso!</p>");
          $erroreTuo[$i]=$titoli;
            --$nrichiesti;
            unset($_REQUEST[$i]);
        }
      }
    }

  }
}
mysqli_stmt_close($stmt);

//controllo ora che i libri richiesti non siano attualmente in prestito ad altri utenti
$stmt=mysqli_prepare($con,"SELECT id,titolo FROM books WHERE NOT prestito=''");
if(!mysqli_stmt_execute($stmt)){
  //echo("<p>Errore nella lettura del database!</p>");
  $echo=2;
  return $echo;
} else {
  mysqli_stmt_execute($stmt);
  mysqli_stmt_bind_result($stmt,$booksid,$titoli);
  while(mysqli_stmt_fetch($stmt)){

    foreach($_REQUEST as $i=>$v){
      if($v=="si"){
        if($i==$booksid){
          //echo("<p>Errore! Sembra che il libro '".$titoli."' sia gi&agrave; in noleggio a qualcun altro!</p>");
            $erroreSuo[$i]=$titoli;
            --$nrichiesti;
            unset($_REQUEST[$i]);
        }
      }
    }

  }
}
mysqli_stmt_close($stmt);

if($nrichiesti<1){ //controllo anche in php che sia stato richiesto almeno un libro valido
  //echo("<p>Sembra tu non abbia ordinato nessun nuovo libro... Torna qui ai <a href='/libri.php'>libri</a>.");
  $echo=7;
  return $echo;
}else if($_SESSION["numlibri"]+$nrichiesti>3){
  //echo("<p>Attenzione! Non puoi avere in prestito pi&ugrave; di 3 libri per volta. Torna ai <a href='/libri.php'>libri</a> per effettuare una richiesta valida.</p>");
  $echo=8;
  return $echo;
} else {

    $stmt=mysqli_prepare($con,"UPDATE books SET prestito=?, data=?, giorni=? WHERE id=?");
    foreach($_REQUEST as $id=>$v){
      if($v=="si"){
        $data=date("Y-m-d H:i:s", strtotime("now"));
        mysqli_stmt_bind_param($stmt, "ssii", $_SESSION["username"], $data, $giorni, $id);
        if(!mysqli_stmt_execute($stmt)){
          //echo("<p>Errore nell'update del database!</p>");
          $echo=3;
          return $echo;
        } else {
          mysqli_stmt_execute($stmt);

        }
      }
  }
  mysqli_stmt_close($stmt);

  $_SESSION["numlibri"]+=$nrichiesti;
}}
mysqli_close($con);
$echo=6;
return $echo;
} else {
  $echo=4;
  return $echo;
  //echo("<p>Errore - ci &egrave; pervenuto un valore per i giorni di noleggio non valido. Inserire un valore intero maggiore o uguale a 1. Se &egrave; stato solo un errore, torna ai <a href='/libri.php'>libri</a> per effettuare una richiesta valida.</p>");
}}else {
  $echo=5;
  return $echo;
  //echo("<p>Errore: effettuare il <a href='/login.php'>login</a> per noleggiare dei libri.");
}
}
check();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <link href="homework.css" rel="stylesheet"/>
    <link rel="icon" href="/PoliTo.ico" />
    <meta name="author" content="Lorenzo Cascioli" >
    <title>NOLEGGIO</title>
</head>
<body>

<?php
if(!$session){
  echo("<p>Errore - sessioni non abilitate...</p>");
} else {
?>
<div class="grid-container">

<?php require_once("header.inc"); ?>
<?php require_once("menu.inc"); ?>

<div class="theMain">
  <div class="section"><?php

  foreach($nonEsiste as $i=>$t){
    echo("<p>Errore! Ci risulta che l'ID ".$t." non esista!</p>");
  }

  foreach($erroreTuo as $i=>$t){
    echo("<p>Errore! Sembra che il libro '".$t."' sia gi&agrave; in tuo possesso!</p>");
  }

  foreach($erroreSuo as $i=>$t){
    echo("<p>Errore! Sembra che il libro '".$t."' sia gi&agrave; in noleggio a qualcun altro!</p>");
  }

  $proceed=false;

  switch($echo){
    case 0: echo("<p>Ooops.. Qualcosa &egrave; andato storto!</p>");
            $proceed=false;
            break;
    case 1: echo("<p>Errore - collegamento al DB impossibile.</p>");
            $proceed=false;
            break;
    case 2: echo("<p>Errore nella lettura del database!</p>");
            $proceed=false;
            break;
    case 3: echo("<p>Errore nell'update del database!</p>");
            $proceed=false;
            break;
    case 4: echo("<p>Errore - ci &egrave; pervenuto un valore per i giorni di noleggio non valido. Inserire un valore intero maggiore o uguale a 1 e inferiore a 100. Se &egrave; stato solo un errore, torna ai <a href='/libri.php'>libri</a> per effettuare una richiesta valida.</p>");
            $proceed=false;
            break;
    case 5: echo("<p>Errore: effettuare il <a href='/login.php'>login</a> per noleggiare dei libri.");
            $proceed=false;
            break;
    case 6: $proceed=true;
            break;
    case 7: echo("<p>Sembra tu non abbia ordinato nessun nuovo libro... Torna qui ai <a href='/libri.php'>libri</a>.");
            $proceed=false;
            break;
    case 8: echo("<p>Attenzione! Non puoi avere in prestito pi&ugrave; di 3 libri per volta. Torna ai <a href='/libri.php'>libri</a> per effettuare una richiesta valida.</p>");
            $proceed=false;
            break;
    default: echo("Errore inatteso...");
             $proceed=false;
             break;

  }

  if($proceed){

      //procedo a ricapitolare richieste valide avvenute
      if($nrichiesti>1 && $giorni>1){
      echo("Hai richiesto ".$nrichiesti." libri per ".$giorni." giorni.");
    } elseif($nrichiesti>1 && $giorni=1){
      echo("Hai richiesto ".$nrichiesti." libri per ".$giorni." giorno.");
    } elseif ($nrichiesti==1 && $giorni>1){
      echo("Hai richiesto ".$nrichiesti." libro per ".$giorni." giorni.");
    } elseif ($nrichiesti==1 && $giorni==1){
      echo("Hai richiesto ".$nrichiesti." libro per ".$giorni." giorno.");
    }

    if($nrichiesti>1){
    echo("<p>Hai preso a noleggio i libri, operazione completata! Torna alla tua pagina <a href='/libri.php'>libri</a>.</p>");
  } elseif ($nrichiesti==1){
    echo("<p>Hai preso a noleggio il libro, operazione completata! Torna alla tua pagina <a href='/libri.php'>libri</a>.</p>");
  }} }?>
</div></div>
<?php
include("footer.inc");
?> </div>
</body>
</html>
