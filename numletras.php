<?php
  function numletras($ntot)
  {
    $mensaje='';
    $entero=substr($ntot,0,1);
    $decimal=strval(substr($ntot,2,1));
    $decimal2=strval(substr($ntot,2,2));
    $Largo=strlen($ntot);


    switch ($entero) {
      case '0':
          $mensaje=$mensaje."cero punto";
      break;
      case '1':
          $mensaje="uno punto";
      break;
      case '2':
          $mensaje="dos punto";
      break;
      case '3':
          $mensaje="tres punto";
      break;
      case '4':
          $mensaje="cuatro punto";
      break;
      case '5':
          $mensaje="cinco punto";
      break;
        } //fin funcion

        if($Largo<=3)
        {
          switch ($decimal) {
            case '0':
              $mensaje=$mensaje." cero.";
                break;
            case '1':
              $mensaje=$mensaje." uno.";
                break;
            case '2':
              $mensaje=$mensaje." dos.";
                break;
            case '3':
              $mensaje=$mensaje." tres.";
                break;
            case '4':
              $mensaje=$mensaje." cuatro.";
                break;
            case '5':
                $mensaje=$mensaje." cinco.";
                break;
            case '6':
                $mensaje=$mensaje." seis.";
                break;
            case '7':
                $mensaje=$mensaje." siete.";
                break;
            case '8':
                $mensaje=$mensaje." ocho.";
                break;
            case '9':
                $mensaje=$mensaje." nueve.";
                break;
              }
        }
        else
        {

          switch ($decimal2) {
          case '00':
            $mensaje=$mensaje." cero cero";
          break;
          case '01':
            $mensaje=$mensaje." cero uno";
          break;
          case '02':
            $mensaje=$mensaje." cero dos";
          break;
          case '03':
            $mensaje=$mensaje." cero tres";
          break;
          case '04':
            $mensaje=$mensaje." cero cuatro";
          break;
          case '05':
            $mensaje=$mensaje." cero cinco";
          break;
          case '06':
            $mensaje=$mensaje." cero seis";
          break;
          case '07':
            $mensaje=$mensaje." cero siete";
          break;
          case '08':
            $mensaje=$mensaje." cero ocho";
          break;
          case '09':
            $mensaje=$mensaje." cero nueve";
          break;
          case '10':
            $mensaje=$mensaje." diez.";
          break;
          case '11':
            $mensaje=$mensaje." once.";
          break;
          case '12':
            $mensaje=$mensaje." dosce.";
          break;
          case '13':
            $mensaje=$mensaje." trece.";
          break;
          case '14':
            $mensaje=$mensaje." catorce";
            break;
          case '15':
            $mensaje=$mensaje." quince.";
            break;
          case '16':
            $mensaje=$mensaje." diez y seis.";
            break;
          case '17':
            $mensaje=$mensaje." diez y siete.";
            break;
          case '18':
            $mensaje=$mensaje." diez y ocho";
            break;
          case '19':
            $mensaje=$mensaje." diez y nueve.";
            break;
          case '20':
              $mensaje=$mensaje." veinte.";
            break;
          case '21':
              $mensaje=$mensaje." veinti uno.";
            break;
          case '22':
              $mensaje=$mensaje." veinti dos.";
            break;
          case '23':
              $mensaje=$mensaje." veinti tres.";
            break;
          case '24':
              $mensaje=$mensaje." veinti cuatro";
            break;
          case '25':
              $mensaje=$mensaje." veinti cinco.";
            break;
          case '26':
              $mensaje=$mensaje." veinti seis.";
            break;
          case '27':
              $mensaje=$mensaje." veinti siete.";
            break;
          case '28':
              $mensaje=$mensaje." veinti ocho";
            break;
          case '29':
              $mensaje=$mensaje." veinti nueve.";
            break;
        case '30':
            $mensaje=$mensaje." treinta.";
            break;
        case '31':
            $mensaje=$mensaje." treinta y uno.";
            break;
        case '32':
            $mensaje=$mensaje." treinta y dos.";
            break;
        case '33':
            $mensaje=$mensaje." treinta y tres.";
            break;
        case '34':
            $mensaje=$mensaje." treinta y cuatro";
            break;
        case '35':
            $mensaje=$mensaje." treinta y cinco.";
            break;
        case '36':
            $mensaje=$mensaje." treinta y seis.";
            break;
        case '37':
            $mensaje=$mensaje." treinta y siete.";
            break;
        case '38':
            $mensaje=$mensaje." treinta y ocho";
            break;
        case '39':
            $mensaje=$mensaje." treinta y  nueve.";
            break;

            case '40':
                $mensaje=$mensaje." cuarenta.";
              break;
              case '41':
                $mensaje=$mensaje." cuarenta y uno.";
              break;
              case '42':
                $mensaje=$mensaje." cuarenta y dos.";
              break;
              case '43':
                $mensaje=$mensaje." cuarenta y tres.";
              break;
              case '44':
                $mensaje=$mensaje." cuarenta y cuatro";
              break;
              case '45':
                $mensaje=$mensaje." cuarenta y cinco.";
              break;
              case '46':
                  $mensaje=$mensaje." cuarenta y seis.";
              break;
              case '47':
                  $mensaje=$mensaje." cuarenta y siete.";
              break;
              case '48':
                  $mensaje=$mensaje." cuarenta y ocho";
              break;
              case '49':
                  $mensaje=$mensaje." cuarenta y  nueve.";
              break;
              case '50':
                   $mensaje=$mensaje." cincuenta.";
                 break;
                 case '51':
                   $mensaje=$mensaje." cincuenta y uno.";
                 break;
                 case '52':
                   $mensaje=$mensaje." cincuenta y dos.";
                 break;
                 case '53':
                   $mensaje=$mensaje." cincuenta y tres.";
                 break;
                 case '54':
                   $mensaje=$mensaje." cincuenta y cuatro";
                 break;
                 case '55':
                   $mensaje=$mensaje." cincuenta y cinco.";
                 break;
                 case '56':
                     $mensaje=$mensaje." cincuenta y seis.";
                 break;
                 case '57':
                     $mensaje=$mensaje." cincuenta y siete.";
                 break;
                 case '58':
                     $mensaje=$mensaje." cincuenta y ocho";
                 break;
                 case '59':
                     $mensaje=$mensaje." cincuenta y  nueve.";
                 break;

                 case '60':
  $mensaje=$mensaje." sesenta.";
break;
case '61':
  $mensaje=$mensaje." sesenta y uno.";
break;
case '62':
  $mensaje=$mensaje." sesenta y dos.";
break;
case '63':
  $mensaje=$mensaje." sesenta y tres.";
break;
case '64':
  $mensaje=$mensaje." sesenta y cuatro";
break;
case '65':
  $mensaje=$mensaje." sesenta y cinco.";
break;
case '66':
    $mensaje=$mensaje." sesenta y seis.";
break;
case '67':
    $mensaje=$mensaje." sesenta y siete.";
break;
case '68':
    $mensaje=$mensaje." sesenta y ocho";
break;
case '69':
    $mensaje=$mensaje." sesenta y  nueve.";
break;

case '70':
    $mensaje=$mensaje." setenta.";
break;
case '71':
    $mensaje=$mensaje." setenta y uno.";
break;
case '72':
    $mensaje=$mensaje." setenta y dos.";
break;
case '73':
    $mensaje=$mensaje." setenta y tres.";
break;
case '74':
    $mensaje=$mensaje." setenta y cuatro";
break;
case '75':
    $mensaje=$mensaje." setenta y cinco.";
break;
case '76':
    $mensaje=$mensaje." setenta y seis.";
break;
case '77':
    $mensaje=$mensaje." setenta y siete.";
break;
case '78':
    $mensaje=$mensaje." setenta y ocho";
break;
case '79':
    $mensaje=$mensaje." setenta y  nueve.";
break;
case '80':
    $mensaje=$mensaje." ochenta.";
break;
case '81':
    $mensaje=$mensaje." ochenta y uno.";
break;
case '82':
    $mensaje=$mensaje." ochenta y dos.";
break;
case '83':
    $mensaje=$mensaje." ochenta y tres.";
break;
case '84':
  $mensaje=$mensaje." ochenta y cuatro";
break;
case '85':
  $mensaje=$mensaje." ochenta y cinco.";
break;
case '86':
  $mensaje=$mensaje." ochenta y seis.";
break;
case '87':
  $mensaje=$mensaje." ochenta y siete.";
break;
case '88':
  $mensaje=$mensaje." ochenta y ocho";
break;
case '89':
  $mensaje=$mensaje." ochenta y  nueve.";
break;
case '90':
  $mensaje=$mensaje." noventa.";
break;
case '91':
  $mensaje=$mensaje." noventa y uno.";
break;
case '92':
  $mensaje=$mensaje." noventa y dos.";
break;
case '93':
  $mensaje=$mensaje." noventa y tres.";
break;
case '94':
  $mensaje=$mensaje." noventa y cuatro";
break;
case '95':
  $mensaje=$mensaje." noventa y cinco.";
break;
case '96':
  $mensaje=$mensaje." noventa y seis.";
break;
case '97':
  $mensaje=$mensaje." noventa y siete.";
break;
case '98':
  $mensaje=$mensaje." noventa y ocho";
break;
case '99':
  $mensaje=$mensaje." noventa y  nueve.";
break;
          }

        }
          $valo_return="(".$mensaje.")";
          return $valo_return;
    }

?>
