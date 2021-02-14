<?php

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

  $echo=9;
  if(isset($_REQUEST["u"]) && ($_SESSION["username"]=="ANONIMO" || !isset($_SESSION["username"]))){ //procedo solo se non sono già autenticato
  unset($_SESSION["username"]);

$out=0;
$username;
$password;
function check(){
  global $username, $password, $echo, $out;
  //controllo esistenza parametri
  if(isset($_REQUEST["u"]) && !empty($_REQUEST["u"]) && isset($_REQUEST["p"]) && !empty($_REQUEST["p"]) ){
    $username=trim($_REQUEST["u"]);
    $password=trim($_REQUEST["p"]);
    //controllo lunghezza parametri
    if(strlen($username)>2 && strlen($username)<7 && strlen($password)>3 && strlen($password)<9){
      //controllo validità parametri
    if(preg_match('/^[a-zA-Z%][a-zA-z0-9%]+$/',$username) && preg_match('/[0-9]/',$username) && preg_match('/[^0-9]/',$username) && preg_match('/^[a-zA-Z]+$/',$password) && preg_match('/[A-Z]/',$password) && preg_match('/[a-z]/',$password)){
      //dati forniti sono validi: apro database e controllo se lo username è tra quelli già noti

      $con = mysqli_connect( "localhost","uReadOnly","posso_solo_leggere","biblioteca");
      if (mysqli_connect_errno()){
        $echo=1;
        return $echo;
        //printf ("<p>Errore - collegamento al DB impossibile: %s</p>\n", mysqli_connect_error());
      } else {
        //aperta la connessione, estraggo gli username e controllo se c'è match col dato fornito
        $stmt=mysqli_prepare( $con, "SELECT COUNT(*) FROM users WHERE username=?" );
        mysqli_stmt_bind_param($stmt, "s", $username);

        if(!mysqli_stmt_execute($stmt)){
          $echo=2;
          return $echo;
        } else {
          mysqli_stmt_execute($stmt);
          mysqli_stmt_bind_result($stmt,$num);
          mysqli_stmt_fetch($stmt);
          mysqli_stmt_close($stmt);
          //echo($num);
          if($num>0){
          //username presente in tabella utenti
          //apro altra connessione, e leggo la password
          $stmt=mysqli_prepare($con,"SELECT pwd FROM users WHERE username=?");
          mysqli_stmt_bind_param($stmt,"s",$username);

          if(!mysqli_stmt_execute($stmt)){
            $echo=2;
            return $echo; //query fallita
          } else {
            $nrows=0;
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt,$rows);
            while(mysqli_stmt_fetch($stmt)){
              //echo($nrows);
              $pwd=$rows; //prendo la vera password, ma la andrò a usare solo se il risultato sarà fatto di una sola riga
              ++$nrows;
            }
            //echo($pwd);
            //echo($nrows);
            mysqli_stmt_close($stmt);
            if($nrows==1){
              //C'è un solo match, situazione corretta
              //Controlliamo correttezza password
              if($password==$pwd){
                //Impostiamo cookie di 48 ore per lo username
                $scadenza = time() + 3600*48;
                setcookie("username", $username, $scadenza,"/");
                //impostiamo variabile di sessione username
                $_SESSION["username"]=$username;
                $_SESSION["numlibri"]=0;
                $stmt=mysqli_prepare($con,"SELECT COUNT(*) FROM books WHERE prestito=?");
                mysqli_stmt_bind_param($stmt,"s",$username);
                if(!mysqli_stmt_execute($stmt)){
                  $echo=2;
                  return $echo; //query fallita
                } else {
                  mysqli_stmt_execute($stmt);
                  mysqli_stmt_bind_result($stmt,$tot);
                  mysqli_stmt_fetch($stmt);
                  $_SESSION["numlibri"]=$tot;
                  mysqli_stmt_close($stmt);
                }
                $echo=9;
                $out=1;
              } else{
                $echo=8;
                return $echo;
                //echo("Errore- password non corretta per l'utente");
              }
            } else {
              $echo=3;
              return $echo;
              //echo("Errore - utente duplicato in database...");
            }
          }
          } else {
            $echo=4;
            return $echo;
            //echo("Errore - username non valido!");
          }
          //mysqli_free_result($result);
        }

      }
      mysqli_close($con);

    } else{
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
  } else{
    $echo=7;
    return $echo;
    //echo("<p>Errore! Username e/o password mancante...</p>");
  }
}


check();
} else if (isset($_REQUEST["u"]) && !($_SESSION["username"]=="ANONIMO" || !isset($_SESSION["username"]))){
  $echo=10; //cerco di arrivare con dati da form (o simili) ma c'è già utente loggato
} else {
  //accedo alla pagina ma non da login: sono già autenticato da prima oppure non sono autenticato
  if(isset($_SESSION["username"]) && !empty($_SESSION["username"]) && $_SESSION["username"]!=="ANONIMO"){
    //se avevo già fatto autenticazione e sono loggato
    $out=1;
  } else {
    if(!isset($_SESSION["username"]) || empty($_SESSION["username"])){
      $_SESSION["username"]="ANONIMO";
      $logoutdisabled=true;
    }

    if($_SESSION["username"]=="ANONIMO"){
      $logoutdisabled=true;
    }
    //arrivo a login senza essere autenticato nè avere dati in sessione
    $out=0;
  }
}

if(isset($_SESSION["username"]) && !empty($_SESSION["username"]) && $_SESSION["username"]!=="ANONIMO"){
  $logindisabled=true;
}
$title="LIBRI";
}
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <link href="homework.css" rel="stylesheet"/>
    <link rel="icon" href="/PoliTo.ico" />
    <meta name="author" content="Lorenzo Cascioli" >
    <title>LIBRI</title>
    <script>
    function validagg(){
      var valid=false;
      var g=document.noleggio.giorni.value;
      if(g==""){
        alert("Inserire un numero valido di giorni tra 1 e 365!");
        valid=false;
        return valid;
      } else {
        var regexp=/^[1-9][0-9]*$/;
        if(!regexp.test(g)){
          alert("Errore! L'indicazione dei giorni di noleggio deve contenere solo valori numerici interi maggiori di zero!");
          valid=false;
          return valid;
        } else if(g<1 || g>100){
          alert("Inserire un numero di giorni tra 1 e 100!");
          valid=false;
          return valid;
        } else {
          valid=true;
          return valid;
        }
      }
    }

    function checked(){
      var checked=false;
      for(i=0; i<noleggio.elements.length-2 && !checked; ++i){ //controllo i checkbox, tolgo numgiorni e il pulsante di invio
        if(noleggio.elements[i].checked){
          checked=true;
        }
      }
      if(!checked){
        alert("Seleziona almeno un libro da noleggiare!");
      }
      return checked;
    }

    function validaForm(){
      if(validagg() && checked()){
        return true;
      } else {
        return false;
      }
    }

    </script>
</head>
<body>

  <?php

  if(!$session){
    echo("<p>Errore - sessioni disabilitate...</p>");
  } else {

  $proceed=false;

  switch($echo){
    case 1: echo("<p>Errore - collegamento al DB impossibile.</p>");
            $proceed=false;
            break;
    case 2: echo("<p>Errore – query fallita</p>");
            $proceed=false;
            break;
    case 3: echo("<p>Errore - utente duplicato in database...</p>");
            $proceed=false;
            break;
    case 4: echo("<p>Errore - username non valido! Riprova il <a href='/login.php'>login</a>.</p>");
            $proceed=false;
            break;
    case 5: echo("<p>Errore. Lo username pu&ograve; contenere solo caratteri alfanumerici o il simbolo %, deve iniziare con un carattere alfabetico o con %, deve essere lungo da un minimo di tre ad un massimo di sei caratteri e deve contenere almeno un carattere non numerico ed uno numerico.<br>
            La password pu&ograve; contenere solo caratteri alfabetici, deve essere lunga minimo quattro e massimo otto caratteri ed avere almeno un carattere minuscolo ed uno maiuscolo. Riprova il <a href='/login.php'>login</a>.</p>");
            $proceed=false;
            break;
    case 6: echo("<p>Errore. La lunghezza dello username dev'essere compresa tra 3 e 6 caratteri, quella della password tra 4 e 8 caratteri. Riprova il <a href='/login.php'>login</a>.</p>");
            $proceed=false;
            break;
    case 7: echo("<p>Errore! Username e/o password mancante...</p>");
            $proceed=false;
            break;
    case 8: echo("<p>Errore! Password errata per l'utente ".$username.". Riprova il <a href='/login.php'>login</a>.</p>");
            $proceed=false;
            break;
    case 9: $proceed=true;
            break;
    case 10: echo("<p>Sembra ci sia gi&agrave; un utente attivo! Torna a <a href='/home.php'>home</a> ed effettua il logout per cambiare utente.</p>");
            $proceed=false;
            break;
    default: echo("Errore inatteso...");
            $proceed=false;
            break;

  }
  if($proceed){

   ?>

   <div class="grid-container">

  <?php require_once("header.inc"); ?>
  <?php require_once("menu.inc"); ?>

<div class="theMain">
<?php

//out è 1 se sono autenticato, 0 se non lo sono
if($out){?>
  <div class='section prima'>
    <?php
  $daysdisplay=true;
  if(isset($_REQUEST["pulisci"]) && $_REQUEST["pulisci"]=="pulisci"){
    $daysdisplay=false;
  }

  $con = mysqli_connect( "localhost","uReadOnly","posso_solo_leggere","biblioteca");
  if (mysqli_connect_errno()){
    printf ("<p>Errore - collegamento al DB impossibile: %s</p>\n", mysqli_connect_error());
  } else {
    echo("<p>Benvenuto, ".$_SESSION["username"]."!</p><p> Ecco la tua pagina personale.</p>");
    //leggo qua DB per vedere se ho 0 o più libri a noleggio

    $stmt=mysqli_prepare($con,"SELECT id,autori,titolo,data,giorni FROM books WHERE prestito=?");
    mysqli_stmt_bind_param($stmt, "s", $_SESSION["username"]);
    if(!mysqli_stmt_execute($stmt)){
      echo("<p>Errore - query fallita...</p>");
    } else {
      mysqli_stmt_execute($stmt);
      mysqli_stmt_bind_result($stmt,$id,$autori,$titolo,$datanolo,$giorni);
      $i=0;
      while(mysqli_stmt_fetch($stmt)){
        if($i==0){ ?>
          <!--///////////////////////-->
          <div class="libri"><div class="row"><div class="head id">ID</div><div class="head author">Autori</div><div class="head title">Titolo</div><div class="head action">Azione</div><div class="head alert" style="color:black">Scaduto?</div></div>
            <?php
        }
        $alt=false;
        $datain=strtotime($datanolo);
        $today=strtotime("now");
        $giorninoleggio=ceil(($today-$datain)/(60*60*24));
        if($giorninoleggio>$giorni){
          $alt=true;
        }
        echo("<div class='row'><div class='id'>".$id."</div><div class='name'>".$autori."</div><div class='name'>".$titolo."</div>");
        if($alt){echo("<div class='action'><form action='riconsegna.php' method='post'><input type='hidden' name='riconsegna' value='$id'><input class='bottone 'type='submit' value='RESTITUISCI'></form></div><div class='alert'>SI!</div></div>");
        } else
        {echo("<div class='action'><form action='riconsegna.php' method='post'><input type='hidden' name='riconsegna' value='$id'><input class='bottone 'type='submit' value='RESTITUISCI'></form></div><div class='alert' style='color:black'>No</div></div>");}

        ++$i;
      }

      if($_SESSION["numlibri"]==0){
        echo("<p>Attualmente non hai alcun libro a noleggio.</p></div>");
      } else if($_SESSION["numlibri"]==1){
        echo("</div><p>Hai attualmente a noleggio ".$_SESSION["numlibri"]." libro.</p></div>");
      } else {
      echo("</div><p>Hai attualmente a noleggio ".$_SESSION["numlibri"]." libri.</p></div>");
    }
    }
    mysqli_stmt_close($stmt);

    //stampata la prima section, ora la seconda
    ?>
    <div class="section seconda">
      <p>Trovi qui sotto tutti i nostri libri.<br>
      Puoi controllare quali sono disponibili e, nel caso, richiederli in prestito selezionando quello/i che vuoi e premendo l'apposito pulsante.<br>
      Ricorda che non puoi avere in prestito pi&ugrave; di 3 libri contemporaneamente!</p>
      <form name="noleggio" method="post" action="/noleggio.php" onsubmit="return validaForm();">
    <div class="libri"><div class="row"><div class="head id2">ID</div><div class="head author2">Autori</div><div class="head title2">Titolo</div><div class="head action2">Stato</div></div>
    <?php
    $stmt=mysqli_prepare($con,"SELECT * FROM books");
    if(!mysqli_stmt_execute($stmt)){
      echo("</form></div><p>Errore - query fallita...</p></div>");
    } else {
      mysqli_stmt_execute($stmt);
      mysqli_stmt_bind_result($stmt,$id,$autori,$titolo,$prestito,$data,$giorni);
      while(mysqli_stmt_fetch($stmt)){
        if(empty($prestito)){
          $info="<input type='checkbox' name=$id value='si'>";
        } else {
          //controllare, per libro attualmente in prestito, se il tempo è scaduto o meno
          $data1=strtotime($data);
          $data2=date("Y/m/d",$data1+($giorni*24*60*60));
          $data1=date("Y/m/d",$data1);

          $today=date("Y/m/d",strtotime("now"));

          if($today>$data2){
            //libro è stato tenuto più del dovuto
            $info="PRESTITO SCADUTO";
          } else {
            //libro regolarmente in prestito
            $info="IN PRESTITO";
          }

        }
        echo("<div class='row'><div class='id2'>".$id."</div><div class='author2'>".$autori."</div><div class='title2'>".$titolo."</div><div class='action2'>".$info."</div></div>");
      }  ?>
    </div>
      <p>Numero giorni noleggio:<input type='text' name='giorni' <?php if(!$daysdisplay){echo("value=''");} ?> ></p>
      <input class="bottone" type='submit' value="PRESTITO"></form>
      <form name="pulizia" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
        <input name="pulisci" type="hidden" value="pulisci">
        <input class="bottone" type="submit" value="PULISCI">
      </form>
      <?php
      echo("</div>");
    } mysqli_stmt_close($stmt);
  }
  mysqli_close($con);
} else if(!$out){
  //non sono autenticato: carico solo numero libri presenti in biblioteca, liberi e totali
  //connessione in uReadOnly
  $con = mysqli_connect( "localhost","uReadOnly","posso_solo_leggere","biblioteca");
  if (mysqli_connect_errno()){
    printf ("<p>Errore - collegamento al DB impossibile: %s</p>\n", mysqli_connect_error());
  } else {
    $stmt=mysqli_prepare($con,"SELECT COUNT(*) FROM books");

    if(!mysqli_stmt_execute($stmt)){
      echo("<p>Errore - query fallita...</p>");
    } else {
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt,$num);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    //ora bisogna estrarre il numero di libri disponibili

    $stmt=mysqli_prepare($con,"SELECT COUNT(*) FROM books WHERE prestito=''");
    if(!mysqli_stmt_execute($stmt)){
      echo("<p>Errore - query fallita...</p>");
    } else {
      mysqli_stmt_execute($stmt);
      mysqli_stmt_bind_result($stmt,$numdisp);
      mysqli_stmt_fetch($stmt);
      mysqli_stmt_close($stmt);

    if($numdisp==0){
      echo("<div class='section'><p>In biblioteca al momento abbiamo un ammontare di ".$num." libri, dei quali purtroppo nessuno &egrave; attualmente disponibile per il noleggio gi&agrave; da ora.</p>");
      echo("<p>Effettua il <a href='login.php'>login</a> per gestire i tuoi libri.<br>Se non sei registrato, puoi farlo rapidamente <a href='new.php'>qui</a>.</p></div>");
    } else if($numdisp==1){
      echo("<div class='section'><p>In biblioteca al momento abbiamo un ammontare di ".$num." libri, dei quali ".$numdisp." disponibile per il noleggio gi&agrave; da ora.</p>");
      echo("<p>Effettua il <a href='login.php'>login</a> per gestire i tuoi libri e prenderne in prestito di nuovi!<br>Se non sei registrato, puoi farlo rapidamente <a href='new.php'>qui</a>.</p></div>");
    } else {
    echo("<div class='section'><p>In biblioteca al momento abbiamo un ammontare di ".$num." libri, dei quali ".$numdisp." disponibili per il noleggio gi&agrave; da ora.</p>");
    echo("<p>Effettua il <a href='login.php'>login</a> per gestire i tuoi libri e prenderne in prestito di nuovi!<br>Se non sei registrato, puoi farlo rapidamente <a href='new.php'>qui</a>.</p></div>");
      }
    }
  }
}
  mysqli_close($con);
}
} else {
  ?>
  <p>Torna alla <a href="/home.php">home</a>.</p>
  <?php
}
}

 ?>
</div>
<?php
include("footer.inc");
?>
</div>
</body>
</html>
