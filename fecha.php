<?php
  date_default_timezone_set('UTC');  
  function f_actual($fechaactual)
{
 $dia = substr($fechaactual,0,2);
 $mes = substr($fechaactual,2,2);
 $ano = substr($fechaactual,4,4);
 switch ($mes) {
  case '01':
      $mes=" enero ";
      break;
  case '02':
      $mes=" febrero ";
      break;
  case '03':
      $mes=" marzo ";
      break;
  case '04':
      $mes=" abril ";
      break;
  case '05':
      $mes=" mayo ";
      break;
  case '06':
    $mes=" junio ";
    break;
  case '07':
    $mes=" julio ";
    break;
  case '08':
    $mes=" agosto ";
    break;
  case '09':
    $mes=" septiembre ";
    break;
  case '10':
    $mes=" octubre ";
    break;
  case '11':
    $mes=" noviembre ";
    break;
  case '12':
    $mes=" diciembre ";
    break;
  }
 $valor_devuelto=$dia." de ".$mes." del ".$ano;
 return $valor_devuelto;
}
?>
