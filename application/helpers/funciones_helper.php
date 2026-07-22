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

function menu_principal($tienda_nombre=""){
    ?>
<div class="admin-sidebar text-white d-flex flex-column py-4 px-3">
    <div class="text-center mb-4">
        <i class="bi bi-bag-heart-fill fs-2 d-block mb-1"></i>
        <span class="fw-bold small"><?php echo $tienda_nombre ?></span>
        <div class="text-white-50 x-small">Admin Panel</div>
    </div>
    <nav class="flex-grow-1">
        <a href="<?php echo base_url('admin/pedidos'); ?>" class="d-flex align-items-center gap-2 text-white-50 text-decoration-none py-2 px-3 rounded mb-1">
            <i class="bi bi-list-ul"></i> Pedidos
        </a>
        <a href="<?php echo base_url('admin/chats'); ?>" class="d-flex align-items-center gap-2 text-white-50 text-decoration-none py-2 px-3 rounded mb-1">
            <i class="bi bi-chat-dots-fill"></i> Chats
        </a>
        <a href="<?php echo base_url('admin/chats/historial'); ?>" class="d-flex align-items-center gap-2 text-white-50 text-decoration-none py-2 px-3 rounded mb-1">
            <i class="bi bi-clock-history"></i> Historial
        </a>
        <a href="<?php echo base_url('admin/chats_ia'); ?>" class="d-flex align-items-center gap-2 text-white text-decoration-none py-2 px-3 rounded mb-1 bg-white bg-opacity-10">
            <i class="bi bi-robot"></i> Chats IA
        </a>
        <a href="<?php echo base_url('admin/whatsapp'); ?>" class="d-flex align-items-center gap-2 text-white-50 text-decoration-none py-2 px-3 rounded mb-1">
            <i class="bi bi-whatsapp"></i> WhatsApp
        </a>
        <a href="<?php echo base_url('tia_conversa'); ?>" class="d-flex align-items-center gap-2 text-white-50 text-decoration-none py-2 px-3 rounded mb-1">
            <i class="bi bi-chat-square-heart-fill"></i> Conversaciones IA
        </a>
        <a href="<?php echo base_url(); ?>" class="d-flex align-items-center gap-2 text-white-50 text-decoration-none py-2 px-3 rounded mb-1" target="_blank">
            <i class="bi bi-shop"></i> Ver tienda
        </a>
    </nav>
    <div class="border-top border-secondary pt-3 mt-3">
        <div class="text-white-50 small mb-2">
            <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($admin_nombre ?? 'Admin'); ?>
        </div>
        <a href="<?php echo base_url('admin/logout'); ?>" class="btn btn-outline-light btn-sm w-100">
            <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
        </a>
    </div>
</div>
    <?php
}
?>