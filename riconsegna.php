<?php

$session = true;
if( session_status() === PHP_SESSION_DISABLED  ){
    $session = false;
    //echo("<p>Errore - sessioni non abilitate...</p>");
  }
elseif( session_status() !== PHP_SESSION_ACTIVE ){
  session_start();
  $echo=0;

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

function check(){
  global $echo,$giorni,$giorninoleggio;
  if(!($_SESSION["username"]=="ANONIMO")){

  if(isset($_REQUEST["riconsegna"]) && !empty($_REQUEST["riconsegna"]) && preg_match('/^[0-9]+$/',$_REQUEST["riconsegna"])){
    $id=$_REQUEST["riconsegna"];

    $con = mysqli_connect( "localhost","uReadWrite","SuperPippo!!!","biblioteca");
    if (mysqli_connect_errno()){
      //printf ("<p>Errore - collegamento al DB impossibile: %s</p>\n", mysqli_connect_error());
      $echo=1;
      return $echo;
    } else {
      //controllo che il libro fosse effettivamente dell'utente che sta cercando di fare la riconsegna
      $stmt=mysqli_prepare($con,"SELECT id,titolo FROM books WHERE NOT prestito=?");
      mysqli_stmt_bind_param($stmt, "s", $_SESSION["username"]);
      if(!mysqli_stmt_execute($stmt)){
        //echo("<p>Errore nella lettura del database!</p>");
        $echo=2;
        return $echo;
      } else {
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt,$booksid,$titoli);
        while(mysqli_stmt_fetch($stmt)){
          if($id==$booksid){
              //echo("<p>Errore! Sembra che il libro ".$titoli." non sia attualmente in tuo possesso!</p>");
              $echo=8;
              unset($_REQUEST["riconsegna"]);
              return $echo;
              }
            }
      }
      mysqli_stmt_close($stmt);

      //controllo giorni in cui libro è stato tenuto
      $stmt=mysqli_prepare($con,"SELECT data,giorni FROM books WHERE id=?");
      mysqli_stmt_bind_param($stmt,"i",$id);
      if(!mysqli_stmt_execute($stmt)){
        //echo("<p>Errore - query di lettura fallita...</p>");
        $echo=2;
        return $echo;
      } else {
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt,$data,$giorni);
        mysqli_stmt_fetch($stmt);

        if(!empty($data) && !empty($giorni)){
        $data1=strtotime($data);
        $today=strtotime("now");
        $giorninoleggio=ceil(($today-$data1)/(60*60*24));
        mysqli_stmt_close($stmt);


      //Faccio update: restituisco libro
      $stmt=mysqli_prepare($con,"UPDATE books SET prestito='', data='0000-00-00 00:00:00', giorni='0' WHERE id=? AND NOT prestito=''");
      mysqli_stmt_bind_param($stmt, "i", $id);
      if(!mysqli_stmt_execute($stmt)){
        //echo("<p>Errore - query di update fallita...</p>");
        $echo=3;
        return $echo;
      } else {
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $echo=4;
        --$_SESSION["numlibri"];
        return $echo;
        //mex di restituzione


      }
    } else {
      mysqli_stmt_close($stmt);
      //echo("<p>Errore - sembra che questo libro non fosse in prestito a te...</p>");
      $echo=5;
      return $echo;

    }
    }
    mysqli_close($con);
  }

} else {
   //echo("<p>Oops! Sembra tu sia stato erroneamente indirizzato a questa pagina di riconsegna libri.</p>");
   $echo=6;
   return $echo;
  }
}
 else {
  $echo=7; //non autenticato
  return $echo;
}
}

check();
$title="RICONSEGNA";
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <link href="homework.css" rel="stylesheet"/>
    <link rel="icon" href="/PoliTo.ico" />
    <meta name="author" content="Lorenzo Cascioli" >
    <title>RICONSEGNA</title>
</head>
<body>
  <div class="grid-container">
  <?php require_once("header.inc");?>
  <?php require_once("menu.inc"); ?>
  <div class="theMain">
    <div class="section">
      <?php if($session==false){
        echo("<p>Errore - sessioni non abilitate...</p>");
      } else {
        $proceed=false;
        switch($echo){

          case 0: echo("<p>Ooops.. Qualcosa &egrave; andato storto!</p>");
                  $proceed=false;
                  break;
          case 1: echo("<p>Errore - collegamento al DB impossibile.</p>");
                  $proceed=false;
                  break;
          case 2: echo("<p>Errore - query di lettura fallita...</p>");
                  $proceed=false;
                  break;
          case 3: echo("<p>Errore - query di update fallita...</p>");
                  $proceed=false;
                  break;
          case 4: $proceed=true;
                  break;
          case 5: echo("<p>Errore - sembra che questo libro non fosse in prestito al momento..</p>");
                  $proceed=false;
                  break;
          case 6: echo("<p>Oops! Sembra tu sia stato erroneamente indirizzato a questa pagina di riconsegna libri.</p>");
                  $proceed=false;
                  break;
          case 7: echo("<p>Errore! Per riconsegnare un libro devi essere autenticato. Effettua <a href='/login.php'>qui</a> il login.");
                  $proceed=false;
                  break;
          case 8: echo("<p>Errore! Sembra che il libro selezionato non sia attualmente in tuo possesso!</p>");
                  $proceed=false;
                  break;
          default: echo("Errore inatteso...");
                  $proceed=false;
                  break;
        }

        if($proceed){
          if($giorninoleggio>1 && $giorni>1 && $giorninoleggio<=$giorni){
          echo("<p>Hai restituito correttamente il libro!</p><p>L'hai tenuto ".$giorninoleggio." giorni, e l'avevi richiesto per un totale di ".$giorni." giorni.</p>");
        } elseif($giorninoleggio>1 && $giorni>1 && $giorninoleggio>$giorni){
           echo("<p>Hai restituito correttamente il libro!</p><p>L'hai tenuto ".$giorninoleggio." giorni, ma l'avevi richiesto per un totale di ".$giorni." giorni.<br>La prossima volta richiedilo per il vero tempo in cui ti serve!</p>");
        } elseif($giorninoleggio==1 && $giorni>1){
           echo("<p>Hai restituito correttamente il libro!</p><p>L'hai tenuto ".$giorninoleggio." giorno, e l'avevi richiesto per un totale di ".$giorni." giorni.</p>");
        } elseif($giorninoleggio>1 && $giorni==1){
          echo("<p>Hai restituito correttamente il libro!</p><p>L'hai tenuto ".$giorninoleggio." giorni, ma l'avevi richiesto per un totale di ".$giorni." giorno.<br>La prossima volta richiedilo per il vero tempo in cui ti serve!</p>");
        } elseif($giorninoleggio==1 && $giorni==1){
          echo("<p>Hai restituito correttamente il libro!</p><p>L'hai tenuto ".$giorninoleggio." giorno, e l'avevi richiesto per un totale di ".$giorni." giorno.</p>");
        }
      }

        echo("<p>Torna ai tuoi <a href='/libri.php'>libri</a> o alla <a href='/home.php'>home</a>.</p>");
      }

        ?>

</div>
</div>

<?php
include("footer.inc");
?></div>
</body>
</html>
