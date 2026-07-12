<?php
function ymd_dmy($cad=""){
    $n = strlen($cad);
    if($n >= 10){
        return substr($cad,8,2) . "-" . substr($cad,5,2) . "-" . substr($cad,0,4) . substr($cad,10);
    }else{
        return "vacio";
    }
}

function traza($msg, $nombre_file="traza.txt"){
    //$nombre_file = "traza.txt";
    $gestor = fopen($nombre_file,"a+");
    $msg .= "\n";
    $traza_temporal = "[".date("Y-m-d H:i:s")."] ";
    fputs($gestor,$traza_temporal . $msg);
    fclose($gestor);
}

function celda($dato="", $centrar=0, $estilo="", $cAtributo=""){
    if($dato=='0'){
        $dato = "<span style=\"color:#cccccc;\">0</span>";
    }

    $cad = "";

    $cEstilo = "";
    if(strlen($estilo)>0)
        $cEstilo = "style=\"$estilo\"";

    if($centrar==1)
        $cad .= "<td align=\"center\" $cEstilo $cAtributo>$dato</td>";
    elseif($centrar==2)
        $cad .= "<td align=\"right\" $cEstilo $cAtributo>$dato</td>";
    else
        $cad .= "<td align=\"left\" $cEstilo $cAtributo>$dato</td>";
    
    return $cad;
}

function celda_h($dato="", $centrar=0, $estilo="", $cAtributo=""){
    if($dato=='0'){
        $dato = "<span style=\"color:#cccccc;\">0</span>";
    }

    $cad = "";

    $cEstilo = "";
    if(strlen($estilo)>0)
        $cEstilo = "style=\"$estilo\"";

    if($centrar==1)
        $cad .= "<th align=\"center\" $cEstilo $cAtributo>$dato</th>";
    elseif($centrar==2)
        $cad .= "<th align=\"right\" $cEstilo $cAtributo>$dato</th>";
    else
        $cad .= "<th align=\"left\" $cEstilo $cAtributo>$dato</th>";
    
    return $cad;
}

function obtenerFechaAnterior($fecha) {
    // Convertir la fecha en texto a un objeto DateTime
    $fechaObjeto = new DateTime($fecha);

    // Restar un día a la fecha
    $fechaObjeto->modify('-1 day');

    // Devolver la fecha en formato texto (Y-m-d)
    return $fechaObjeto->format('Y-m-d');
}

function acceso_denegado($msg=""){
    echo "<a href=\"" . base_url("welcome/index") . "\">Volver a Ingresar</a><br>"; 
    if($msg==""){
        exit('Acceso denegado.');
    }else{
        exit($msg);
    }
}
?>