<?php
class Fm{
	function validacion($conn, $usuario, $clave, &$tipo_usuario){
	 // ESTO ES LA VALIDACION ORIGINAL ==========================

		if($usuario == "" and $clave==""){
			$tipo_usuario = "ADMINISTRADOR";
			return true;
		}else{
			sleep(2);
			return false;
		}
	}

	function celda_simple($dato = "&nbsp;"){
		return "<td>" . $dato . "</td>";
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

	function  fila($cad=""){
		return "<tr>" . $cad . "</tr>";
	}

	function celda_h($dato="",$centrar=0,$estilo=""){
		$cad = "";
		
		$cEstilo = "";
		if(strlen($estilo)>0)
			$cEstilo = "style=\"$estilo\"";

		if($centrar==1){
			$cad .= "<th align=\"center\" $cEstilo>$dato</th>";
		}elseif($centrar==2){
			$cad .= "<th align=\"right\" $cEstilo>$dato</th>"; // style=\"$estilo\"
		}else{
			$cad .= "<th align=\"left\" $cEstilo>$dato</th>";
		}
		
		return $cad;
	}

	function espacio($n){
		$cad = "";
		for($i=0; $i < $n; $i++){
			$cad .= "&nbsp;";
		}
		return $cad;
	}

	function mostrado($msg,$bandera){
		if($bandera){
			echo $msg . "<br>";
		}
	}

	function traer_campo($conn, $table, $campo, $where){
		$cSql = "select $campo from $table where $where";
		$pdo = $conn->prepare($cSql);
		$pdo->execute();
		$result = $pdo->fetchAll();
		foreach($result as $r){
			return $r[$campo];
		}
		return "";
	}

	function traer_campo2($conn, $cSql, $campo){
		$pdo = $conn->prepare($cSql);
		$pdo->execute();
		$result = $pdo->fetchAll();
		foreach($result as $r){
			return $r[$campo];
		}
		return "";
	}

	function result($conn, $cSql, $var1=null){
		$pdo = $conn->prepare($cSql);
		$pdo->bindParam(1,$var1);
		$pdo->execute();
		return $pdo->fetchAll();
	}

	function alertas($mensaje="",$tipo_alerta="success"){
		$mensaje = "<div class=\"alert alert-$tipo_alerta\">$mensaje</div>";
		return $mensaje;
	}

	/*
	function obtener_ip(){
		if(getenv('HTTP_CLIENT_IP')){
			$ip = getenv('HTTP_CLIENT_IP');
		}elseif(getenv('HTTP_X_FORWARDED_FOR')){
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		}elseif(getenv('HTTP_X_FORWARDED')){
			$ip = getenv('HTTP_X_FORWARDED');
		}elseif(getenv('HTTP_FORWARDED_FOR')){
			$ip = getenv('HTTP_FORWARDED_FOR');
		}elseif(getenv('HTTP_FORWARDED')){
			$ip = getenv('HTTP_FORWARDED');
		}else{
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return $ip;
	}
	*/

	function obtener_ip() {
	    // 1. Intentar obtener la IP de las cabeceras de proxy (si existen)
	    $headers = [
	        'HTTP_CLIENT_IP',
	        'HTTP_X_FORWARDED_FOR',
	        'HTTP_X_FORWARDED',
	        'HTTP_X_CLUSTER_CLIENT_IP',
	        'HTTP_FORWARDED_FOR',
	        'HTTP_FORWARDED'
	    ];

	    foreach ($headers as $header) {
	        if (isset($_SERVER[$header]) && !empty($_SERVER[$header])) {
	            // Algunas cabeceras pueden contener una lista de IPs separadas por coma
	            foreach (explode(',', $_SERVER[$header]) as $ip) {
	                $ip = trim($ip);
	                // Validamos que sea una IP real y que no sea de rango privado (opcional)
	                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
	                    return $ip;
	                }
	            }
	        }
	    }

	    // 2. Si no hay proxies o no son confiables, devolvemos la IP de conexión directa
	    return $_SERVER['REMOTE_ADDR'];
	}

	function guardar_ip($conn, $mi_ip, $padre, $hijo){
		$mi_fecha = date("Y-m-d H:i:s");
		$cSql = "insert into visitas(padre, hijo, ip, fecha) values('$padre','$hijo','$mi_ip','$mi_fecha')";
		$pdo = $conn->prepare($cSql);
		$pdo->execute();
	}

	function casilla($nombre, $valor_default, $size=10){
		$cad = "<input type='text' id='" . $nombre . "' name='" . $nombre . "' value='" . $valor_default . "' size='" . $size . "'>";
		return $cad;
	}

	function query_a_array($result,$key,$valor){
	    $ar = array();
	    foreach($result as $r){
	        $ar[$r[$key]] = $r[$valor];
	    }
	    return $ar;
	}

	function option($id, $cad="vacio", $valor=""){
		$selected = ""; //$codo = "";
		if(strlen($valor)>0){
			//$codo .= "valor: $valor = id: $id";
			if($valor == $id){
				$selected = " selected";
			}
		}
		return "<option value=\"$id\" " . $selected . ">" . $cad . "</option>";
	}

	function message($cad="", $alerta=0){
		if ($alerta == 0){
			$class = "success";
			$color = "rgb(250,255,230)";
		}elseif($alerta == 1){
			$class = "warning";
			$color = "rgb(255,255,225)";
		}elseif($alerta == 2){
			$class = "danger";
			$color = "rgb(255,160,140)";
		}else{
			$class = "cualquiera";
			$color = "rgb(240,240,255)";
		}
		return "<div class=\"alert-$class\" style=\"height:40px;background-color:$color;padding:9px\"><strong>" . $cad . "</strong></div>";
	}

    function ymd_dmy($cad=""){
        $n = strlen($cad);
        if($n >= 10){
            return substr($cad,8,2) . "-" . substr($cad,5,2) . "-" . substr($cad,0,4) . substr($cad,10);
        }else{
            return "vacio";
        }
    }

    function floor_dec($nU,$precision=0){
	    $cU = $nU . "";
	    $nLim = strlen($cU);
	    for($n=0; $n<$nLim; $n++){
	        if(substr($cU,$n,1)=="."){
	           $nDecimales = $nLim - $n - 1;
	           $nPos = $n;
	           
	           // Extrayendo o mejor dicho truncando.
	           $nQuitar = $nDecimales - $precision;

	           $nU = substr($cU,0,$nLim-$nQuitar)*1;
	           return $nU;
	        }
	    }
	    return $nU;
	}

	function basico($numero) {
		$valor = array ('uno','dos','tres','cuatro','cinco','seis','siete','ocho',
		'nueve','diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciseis', 'diecisiete', 'dieciocho', 'diecinueve', 'veinte', 'veintiuno', 'veintidos', 'veintitres', 'veinticuatro','veinticinco',
		'veintiséis','veintisiete','veintiocho','veintinueve');
		return $valor[$numero - 1];
	}

	function decenas($n) {
		$decenas = array (30=>'treinta',40=>'cuarenta',50=>'cincuenta',60=>'sesenta',
		70=>'setenta',80=>'ochenta',90=>'noventa');
		if( $n <= 29) return $this->basico($n);
		$x = $n % 10;
		if ( $x == 0 ) {
		return $decenas[$n];
		} else return $decenas[$n - $x].' y '. $this->basico($x);
	}

	function centenas($n) {
		$cientos = array (100 =>'cien',200 =>'doscientos',300=>'trecientos',
		400=>'cuatrocientos', 500=>'quinientos',600=>'seiscientos',
		700=>'setecientos',800=>'ochocientos', 900 =>'novecientos');
		if( $n >= 100) {
		if ( $n % 100 == 0 ) {
		return $cientos[$n];
		} else {
		$u = (int) substr($n,0,1);
		$d = (int) substr($n,1,2);
		return (($u == 1)?'ciento':$cientos[$u*100]).' '.$this->decenas($d);
		}
		} else return $this->decenas($n);
	}

	function miles($n) {
		if($n > 999) {
		if( $n == 1000) {return 'mil';}
		else {
		$l = strlen($n);
		$c = (int)substr($n,0,$l-3);
		$x = (int)substr($n,-3);
		if($c == 1) {$cadena = 'mil '. $this->centenas($x);}
		else if($x != 0) {$cadena = $this->centenas($c).' mil '. $this->centenas($x);}
		else $cadena = $this->centenas($c). ' mil';
		return $cadena;
		}
		} else return $this->centenas($n);
	}

	function millones($n) {
		if($n == 1000000) {return 'un millón';}
		else {
		$l = strlen($n);
		$c = (int)substr($n,0,$l-6);
		$x = (int)substr($n,-6);
		if($c == 1) {
		$cadena = ' millón ';
		} else {
		$cadena = ' millones ';
		}
		return $this->miles($c).$cadena.(($x > 0)? $this->miles($x):'');
		}
	}

	function convertir($n){
		switch (true) {
			case ($n >= 1 && $n <= 29) : return $this->basico($n); break;
			case ($n >= 30 && $n < 100) : return $this->decenas($n); break;
			case ($n >= 100 && $n < 1000) : return $this->centenas($n); break;
			case ($n >= 1000 && $n <= 999999): return $this->miles($n); break;
			case ($n >= 1000000): return $this->millones($n);
		}
	}

	function traza($msg){
	    $nombre_file = "traza.txt";
        $gestor = fopen($nombre_file,"a+");
        $msg .= "\n";
        fputs($gestor,$msg);
        fclose($gestor);
    }

    function menu_principal2($Admin, $store_id, $multi_store){ ?>
        <div class="text-center" style="margin-right:30px;margin-bottom:14px;">
    		<!--<img src="<?= base_url() ?>assets/images/escarapela1.png" height="100px">-->
    		<span style="font-size:14px;font-weight:bold;color:rgb(230,230,230)"><?= EMPRESA ?></span>
    	</div>
    	<script type="text/javascript">
    		function cambia_op(label_a){
    			var obj = document.getElementById(label_a)
    			if (obj.style.display != 'none'){
    				obj.style.display = 'none'
    			}else{
    				obj.style.display = 'block'
    			}
    		}
    	</script>
    	<a href="#" id="h2" onclick="cambia_op('ingresos')">
            <p class="tit-men-fm">I N G R E S O S</p>
        </a>
    	<ul id="ingresos" style="margin-left:-30px; list-style: none; display:block;">
		<?php
			$this->menu_opcion("caja/index","Listar");
			$this->menu_opcion("caja/ingreso_caja","Ingresar");
			$this->menu_opcion("caja/ingreso_caja_anular","Anular");
			$this->menu_opcion("caja/pago_afiliados","Ver Pagos");
		?>
    	</ul>		
    	<a href="#" onclick="cambia_op('egresos')">
            <p class="tit-men-fm">E G R E S O S</p>
        </a>
    	<ul id="egresos" style="margin-left:-30px; list-style: none; display:block;">
    	<?php
    		$this->menu_opcion("caja/index_egresos","Listar"); 
			$this->menu_opcion("caja/egreso_caja","Añadir Egreso");
			$this->menu_opcion("caja/egreso_caja_anular","Anular");
			$this->menu_opcion("caja/jalar_movimiento","Enlazar Movimiento");
			if(isset($_SESSION["caja_id"])){
				if($_SESSION["caja_id"] == '6'){
					$this->menu_opcion("caja/jalar_tesoreria","Enlazar Tesoreria");
				}
			}
    	?>
    	</ul>

    	<a href="#" onclick="cambia_op('afiliados')">
            <p class="tit-men-fm">A F I L I A D O S</p>
        </a>
    	<ul id="afiliados" style="margin-left:-30px; list-style: none; display:none;">
    	<?php
    		$this->menu_opcion("afiliados/index","Listar");
    		$this->menu_opcion("afiliados/add","Ingresar");
    		$this->menu_opcion("afiliados/estado_cta","Saldos/cta");
    		$this->menu_opcion("afiliados/estado_cta_completo","Estado/cta"); 
    		//$this->menu_opcion("afiliados/pagos","Pagos/cta"); 
    	?>
    	</ul>
		
    	<a href="#" onclick="cambia_op('asociado')">
            <p class="tit-men-fm">A S O C I A D O</p>
        </a>
    	<ul id="asociado" style="margin-left:-30px; list-style: none; display:none;">
    	<?php
    		$this->menu_opcion("asociados/index","Listar");
    		$this->menu_opcion("asociados/add","Ingresar");
    	?>
    	</ul>

    	<a href="#" onclick="cambia_op('reportes')">
            <p class="tit-men-fm" style="color:limegreen;font-size:16px;">R E P O R T E S X X </p>
        </a>
    	<ul id="reportes" style="margin-left:-30px; list-style: none; display:none;">
    	<?php
    		$this->menu_opcion("caja/diario_cobranza","Arqueo de Caja"); 
			$this->menu_opcion("reportes/r_intercajas","Control entre Cajas");
    		$this->menu_opcion("afiliados/reportes","Varios");
    	?>
    	</ul>

    	<a href="#" onclick="cambia_op('provision')">
            <p class="tit-men-fm">P R O V I S I O N</p>
        </a>
    	<ul id="provision" style="margin-left:-30px; list-style: none; display:none;">
    	<?php
    		$this->menu_opcion("provisiones/index","Listar");
    		$this->menu_opcion("provisiones/add","Ingresar");
    		$this->menu_opcion("provisiones/add_csv","Ingresar CSV");
    		//$this->menu_opcion("provisiones/index_energia","Listar Lecturas");
    		$this->menu_opcion("provisiones/index_bloque","Lecturas en Bloque");
    		$this->menu_opcion("provisiones/anulaciones_provisiones","Ver Anulados");
    		$this->menu_opcion("provisiones/prerecibos","Imprimir Pre-recibo");
    		$this->menu_opcion("provisiones/colocar_reconexion","Generar Reconexion");
    	?>
    	</ul>

    	<a href="#" onclick="cambia_op('conceptos')">
            <p class="tit-men-fm">C O N C E P T O S</p>
        </a>
    	<ul id="conceptos" style="margin-left:-30px; list-style: none; display:none;">
    	<?php
    		$this->menu_opcion("conceptos/index","Listar");
    		$this->menu_opcion("conceptos/add","Ingresar");
    	?>
    	</ul>

    	<a href="#" onclick="cambia_op('proveedores')">
            <p class="tit-men-fm">P R O V E E D O R E S</p>
        </a>
    	<ul id="proveedores" style="margin-left:-30px; list-style: none; display:none;">
    	<?php
    		$this->menu_opcion("proveedores/index","Listar");
    		$this->menu_opcion("proveedores/add","Ingresar");
    	?>
    	</ul>

    	<?php
			if ($Admin){
		?>
    	<a href="#" onclick="cambia_op('accesos')">
            <p class="tit-men-fm">A C C E S O S</p>
        </a>
    	<ul id="accesos" style="margin-left:-30px; list-style: none; display:none;">
    	<?php
    		$this->menu_opcion("seguridad/crear_usuarios","Crear Usuario");
    		$this->menu_opcion("seguridad/listar_usuarios","Ver Usuarios");
    		$this->menu_opcion("seguridad/crear_nivel","Crear Nivel");
    		$this->menu_opcion("seguridad/listar_permisos_niveles","Ver Niveles");
    		$this->menu_opcion("seguridad/listar_usuarios_cajas","Ver Usuarios x Caja");
			$this->menu_opcion("seguridad/ajustes","Ajustes");
	    	$this->menu_opcion("seguridad/cambio_clave","Cambiar Clave");
	    	$this->menu_opcion("seguridad/backup","Backup");
	    ?>
    	</ul>
    	<?php
    		}
    	?>
    	<a href="#" onclick="cambia_op('otros')">
            <p class="tit-men-fm">O T R O S</p>
        </a>
    	<ul id="otros" style="margin-left:-30px; list-style: none; display:none;">
    	<?php
    		$this->menu_opcion("grupos/index","Ver Grupos");
    		$this->menu_opcion("otros/index","Factores de Servicios");
    		//$this->menu_opcion("otros/ias","IA Reportes");
    		$this->menu_opcion("documentos/index","Registro de Arqueos");
    		$this->menu_opcion("calidad/index/" . date("Y-m-d"),"Verif Fact Electronic");
    		$this->menu_opcion("otros/manuales","Manual del Sistema");
    		$this->menu_opcion("otros/transcribir","Transcribir reuniones");
    		$this->menu_opcion("otros/codigo_qr","Codigos QR");
    	?>
    	</ul>

    	
		<a href="#" onclick="cambia_op('planillas')">
            <p class="tit-men-fm">P L A N I L L A S</p>
        </a>
    	<ul id="planillas" style="margin-left:-30px; list-style: none; display:none;">
    	<?php
    		$this->menu_opcion("personas/index","Personas");
    		$this->menu_opcion("fichas/index","Ficha de Ingreso");
    		$this->menu_opcion("beneficios/index","Beneficios Sociales");
    		$this->menu_opcion("regimen/","R&eacute;gimen");
    		$this->menu_opcion("beneficios_regimen/","Beneficios x R&eacute;gimen");
    		//$this->menu_opcion("formulas/","Formulas");
    		//$this->menu_opcion("planillas/","Planilla de Personal");
    		//$this->menu_opcion("factores/","Otros factores");
    	?>
    	</ul>
		

    	<a href="#" onclick="cambia_op('tramites')">
            <p class="tit-men-fm">T R A M I T E S</p>
        </a>
    	<ul id="tramites" style="margin-left:-30px; list-style: none; display:none;">
    	<?php
    		$this->menu_opcion("tramites/index","Listar");
    		$this->menu_opcion("constancia/add","Constancia");
    		$this->menu_opcion("tra_tipos_doc/index","Tipos de Clasificacion");
    	?>
    	</ul>

    	<a href="#" onclick="cambia_op('parqueo')">
            <p class="tit-men-fm">P A R Q U E O</p>
        </a>
    	<ul id="parqueo" style="margin-left:-30px; list-style: none; display:none;">
    	<?php
    		// $this->menu_opcion("parqueo/index","Listar");
    		$this->menu_opcion("parqueo/add","Registrar");
    	?>
    	</ul>

	    <ul style="margin-left:-20px; margin-top:20px; list-style: none;">
			<li>
    			<a href="<?= base_url("welcome/cierra_sesion"); ?>" class="btn btn-danger">
    				<span>Cierra Sesi&oacute;n</span>
    			</a>
    		</li>           	
        </ul>
<?php
    }

    function menu_opcion($url, $show){
?>
    		<li>
    			<a href="<?= base_url($url); ?>" id="link-fm">
    				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 448 512"><path d="M364.2 83.8c-24.4-24.4-64-24.4-88.4 0l-184 184c-42.1 42.1-42.1 110.3 0 152.4s110.3 42.1 152.4 0l152-152c10.9-10.9 28.7-10.9 39.6 0s10.9 28.7 0 39.6l-152 152c-64 64-167.6 64-231.6 0s-64-167.6 0-231.6l184-184c46.3-46.3 121.3-46.3 167.6 0s46.3 121.3 0 167.6l-176 176c-28.6 28.6-75 28.6-103.6 0s-28.6-75 0-103.6l144-144c10.9-10.9 28.7-10.9 39.6 0s10.9 28.7 0 39.6l-144 144c-6.7 6.7-6.7 17.7 0 24.4s17.7 6.7 24.4 0l176-176c24.4-24.4 24.4-64 0-88.4z" style="fill:rgb(218, 247, 166)"/></svg>&nbsp;<span><?= $show ?></span>
    			</a>
    		</li>
<?php
    }

	function crea_tabla_result($result, $cols, $cols_titulos, $ar_align = array(), $ar_pie = array()){
		// debe ser un result_array
		
		$cad = "<table class=\"table table-hover\"><tr>";
		
		// titulos ===============
		for($i=0; $i< count($cols); $i++){
			$cad .= "<th id=\"cabeza\" style=\"background-color:rgb(200,200,200);margin:0px;padding:10px\">" . $cols_titulos[$i] . "</th>";
		}

		// Añado operaciones
		//$cad .= "<th style=\"background-color:rgb(200,200,200);margin:0px;padding:10px\">Op.</th>";

		$cad .= "</tr>";

		// body ===============
		$totals = array();

		// Inicializando
		for($i=0; $i < count($cols); $i++){
			$totals[$i] = 0; 
		}

		foreach($result as $r){
			$cad .= "<tr>";
			
			$color = "";

			for($i=0; $i < count($cols); $i++){
				$cad .= $this->celda($r[$cols[$i]], $ar_align[$i]);

				if(strtolower($ar_pie[$i]) == "suma"){
					if(isset($r[$cols[$i]])){
						$totals[$i] += $r[$cols[$i]] * 1;
					}else{
						$totals[$i] += 0;
					}
					//echo "Mi suma es :" . $totals[$i] . " _ " . $r[$cols[$i]] . "<br>";
					//print_r($totals);
				}
			}
			
			// Añado operaciones
			/*$cad .= "<td style=\"$color\">";

			if($this->session->userdata["first_name"] == "Administrador"){ 
				$cad .= "<a href=\"" . base_url("insumos/modificar_insumos/") . $r["id"] . "\" alt=\"Editar\"><span class=\"glyphicon glyphicon-edit iconos\"></span></a>\n&nbsp;&nbsp;";
				$cad .= "<a href=\"#\" onclick=\"eliminar_insumo(" . $r["id"] . ")\"><span class=\"glyphicon glyphicon-remove iconos\"></span></a>";
			}
			$cad .= "</td>";*/		

			$cad .= "</tr>";
		}

		if (count($totals) > 0){
			for($i=0; $i<count($cols); $i++){
				if($totals[$i] > 0){
					$cad .= $this->celda_h(number_format($totals[$i],2));
				}else{
					$cad .= $this->celda_h($totals[$i]);
				}
			}
		}

		$cad .= "</table>";
		return $cad;
	}

	function obtener_nombre_doc($cod=""){
		$cod = substr($cod,0,1);
		if(strlen($cod)>0){
			if($cod == "F"){
				return "Factura";
			}elseif($cod == "B"){
				return "Boleta";
			}elseif($cod == "G"){
				return "Guia";
			}else{
				return "clip";
			}
		}else{
			return "";
		}
	}

	function json_datatable($ar_campos,$result){ // Devuelve un json preparado para el datatable, el result debe ser result_array
		$nCols = count($ar_campos);

			$cad = "";
			$limite = count($ar_campos);

			foreach($result as $r){
				$cad .= "[";
				for($i=0; $i<$limite; $i++){
					$cad .=  '"' . $r[$ar_campos[$i]] . '",';
				}
				$cad = substr($cad,0,strlen($cad)-1); // quito la ultima coma
				$cad .= "],";
			}

		$cad = substr($cad,0,strlen($cad)-1);
		$cad = '{"data":[' . $cad . ']}';
		return $cad;

	}

	function conver_dropdown($result, $indice, $descrip, $agrega=null){
		
		$ar = array();
		if($agrega !== null){
			foreach($agrega as $key => $valor){
				$ar[$key] = $valor;
			}
		}
		
		foreach($result as $r){
			$ar[$r[$indice]] = $r[$descrip];
		}
		return $ar;
	}

	function contra_inyeccion($dato=""){
		if(strlen($dato)>0){
			$dato = str_replace(";","",$dato);
		}
		return $dato;
	}

	function parcial_moneda($query,$cod_mon,&$tot,&$existe_deuda){
		if($cod_mon == 'S'){$moneda = "SOLES";}
		if($cod_mon == 'D'){$moneda = "DOLARES";};
		
		$estilo = "padding:5px 10px;font-size:12px;";
		$estilo_estrecho = "padding:5px 10px;font-size:12px;";
		$nI 	= $nTotal = 0;
		$cad 	= "";
		foreach($query->result() as $r){
			$nI++;
			if($nI == 1){
				$cad .= "<table border='1'>";
				$cad .=  "<tr>";
				//echo $this->fm->celda_h("Cod.", 1, $estilo);
				$cad .=  $this->celda_h("Concepto", 1, $estilo); // ."min-width:150px;"
				$cad .=  $this->celda_h("M", 1, $estilo);
				$cad .=  $this->celda_h("Año", 1, $estilo);
				$cad .=  $this->celda_h("Mes", 1, $estilo);
				$cad .=  $this->celda_h("Monto", 2, $estilo);
				$cad .=  $this->celda_h("Referencia", 1, $estilo);
				$cad .=  "</tr>";
			}
			$cad .=  "<tr>";
			//echo $this->fm->celda($r->ccodigo, 0, $estilo_estrecho);
			
			// acortando el campo concepto:
			$ar_c = explode("-", trim($r->concepto));
			if(count($ar_c)>1){
				$concepto = $ar_c[1];
			}else{
				$concepto = $r->concepto;
			}

			$cad .=  $this->celda($concepto, 0, $estilo_estrecho);
			$cad .=  $this->celda($r->cod_mon, 1, $estilo_estrecho);
			$cad .=  $this->celda($r->anno, 1, $estilo_estrecho);
			$cad .=  $this->celda($r->mes, 1, $estilo_estrecho);
			$cad .=  $this->celda($r->total, 2, $estilo_estrecho);
			$cad .=  $this->celda($r->obs, 0, $estilo_estrecho);
			$cad .=  "</tr>";
			$nTotal += $r->total * 1;
		}
		if ($nI > 0){
			$cad .=  "<tr>";
			$cad .=  "<td colspan=\"4\" style=\"text-align:right;font-weight:bold;\">Total {$moneda}: </td>";
			$cad .=  $this->celda(number_format($nTotal,2), 2, $estilo_estrecho."font-weight:bold;");
			$cad .=  "<td></td></tr>";
			$cad .=  "</table>";
			$tot = $nTotal;
			$existe_deuda = true;
		}else{
			$cad .=  "<div style='text-align:center;border-style:solid; border-color:gray; border-width:1px;'>No tiene deudas.</div>";
			$existe_deuda = false;
		}
		return $cad;
	}

	function verificar_permisos($conn, $usuario, $modulo, $accion){
		$cSql = "select nm.listar, nm.agregar, nm.modificar, nm.anular, nm.modulo_id, m.descrip_modulo 
			from usuarios a
			inner join nivel_modulos nm on a.nivel = nm.nivel_id 
			inner join modulos m on nm.modulo_id = m.id 
			where a.usuario = '{$usuario}' and m.descrip_modulo = '{$modulo}'"; 
		
		$query = $conn->query($cSql); // ,array($usuario,$modulo)
		$rpta = true;
		foreach($query->result_array() as $r){
			
			$valor = $r[$accion];
			if($valor == '1'){
				$rpta = true;
			}else{
				$rpta = false;
			}
		}
		return $rpta;
	}

	function verificar_permisos2($conn, $usuario, $modulo, $accion){
		$cSql = "select nm.listar, nm.agregar, nm.modificar, nm.anular, nm.modulo_id, m.descrip_modulo 
			from usuario_niveles a
			inner join nivel_modulos nm on a.nivel_id = nm.nivel_id 
			inner join modulos m on nm.modulo_id = m.id 
			where a.usuario = '{$usuario}' and m.descrip_modulo = '{$modulo}'"; 
		
		//die($cSql);
		$query = $conn->query($cSql); // ,array($usuario,$modulo)
		$rpta = 0;
		foreach($query->result_array() as $r){
			$valor = $r[$accion];
			if($valor == '1'){
				$rpta += 1;
			}else{
				$rpta += 0;
			}
		}
		if($rpta>0)
			return true; 
		else
			return false;
	}

	function aMes($n=0){
		if($n==1){
			return "Enero";
		}elseif($n==2){
			return "Febrero";
		}elseif($n==3){
			return "Marzo";
		}elseif($n==4){
			return "Abril";
		}elseif($n==5){
			return "Mayo";
		}elseif($n==6){
			return "Junio";
		}elseif($n==7){
			return "Julio";
		}elseif($n==8){
			return "Agosto";
		}elseif($n==9){
			return "Setiembre";
		}elseif($n==10){
			return "Octubre";
		}elseif($n==11){
			return "Noviembre";
		}elseif($n==12){
			return "Diciembre";
		}else{
			return false;
		}
	}

	function limpiar_para_json($cad){
		$cad = str_replace(chr(13),"",$cad);
		$cad = str_replace(chr(10),"",$cad);
		$cad = str_replace('"','',$cad);
		return $cad;
	}

	function extranios($nombre){
		$nombre = str_replace("Ñ","N",$nombre);
		$nombre = str_replace("Á","A",$nombre);
		$nombre = str_replace("É","E",$nombre);
		$nombre = str_replace("Í","I",$nombre);
		$nombre = str_replace("Ó","O",$nombre);
		$nombre = str_replace("Ú","U",$nombre);
		$nombre = str_replace("ñ","n",$nombre);
		$nombre = str_replace("á","a",$nombre);
		$nombre = str_replace("é","e",$nombre);
		$nombre = str_replace("í","i",$nombre);
		$nombre = str_replace("ó","o",$nombre);
		$nombre = str_replace("ú","u",$nombre);
		return $nombre;
	}

    function decodi($palabra){
        $cad = $palabra;
        $cad = str_replace(chr(241),"n",$cad);
        $cad = str_replace(chr(209),"N",$cad);
        $cad = str_replace(chr(225),"a",$cad);
        $cad = str_replace(chr(233),"e",$cad);
        $cad = str_replace(chr(237),"i",$cad);
        $cad = str_replace(chr(243),"o",$cad);
        $cad = str_replace(chr(250),"u",$cad);
        $cad = str_replace(chr(193),"A",$cad);
        $cad = str_replace(chr(201),"E",$cad);
        $cad = str_replace(chr(205),"I",$cad);
        $cad = str_replace(chr(211),"O",$cad);
        $cad = str_replace(chr(218),"U",$cad);
        return $cad;
    }

	function consulta_dato_api($dato1){
        if(strlen($dato1)<11){
        	return $this->consulta_dni($dato1);
    	}elseif(strlen($dato1)==11){
    		return $this->consulta_ruc($dato1);
    	}else{
    		return "";
    	}
    }

    function consulta_ruc($ruc){
        // Datos
        $token = 'apis-token-2984.2bqYVYjkpZ5a76iPOD1YFz3upAWChzs2';
        //

        // Iniciar llamada a API
        $curl = curl_init();

        // Buscar ruc sunat
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.apis.net.pe/v1/ruc?numero=' . $ruc,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
          'Referer: http://apis.net.pe/api-ruc',
          'Authorization: Bearer ' . $token
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        // Datos de empresas según padron reducido
        $empresa = json_decode($response);
          
          /*
            nombre' => string 'JFKSYS EIRL' (length=11)
            tipoDocumento' => string '6' (length=1)
            numeroDocumento' => string '10605495063' (length=11)
            estado' => string 'ACTIVO' (length=6)
            condicion' => string 'HABIDO' (length=6)
            direccion' => string 'JR. SAN GENARO NRO 645 DEP. 101 URB. SANTA CATALINA ' (length=58)
            ubigeo' => string '150115' (length=6)
            viaTipo' => string 'JR.' (length=3)
            viaNombre' => string 'SAN GENARO' (length=16)
            zonaCodigo' => string 'URB.' (length=4)
            zonaTipo' => string 'SANTA CATALINA' (length=14)
            numero' => string '645' (length=3)
            interior' => string '-' (length=1)
            lote' => string '-' (length=1)
            dpto' => string '101' (length=3)
            manzana' => string '-' (length=1)
            distrito' => string 'LA VICTORIA' (length=11)
            provincia' => string 'LIMA' (length=4)
            departamento' => string 'LIMA' (length=4)
          */
          //var_dump($empresa);
        return $empresa;
    }

    function consulta_dni($dni){
		$token = 'apis-token-2984.2bqYVYjkpZ5a76iPOD1YFz3upAWChzs2';

		// Iniciar llamada a API
		$curl = curl_init();

		// Buscar dni
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://api.apis.net.pe/v1/dni?numero=' . $dni,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 2,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => array(
		    'Referer: https://apis.net.pe/consulta-dni-api',
		    'Authorization: Bearer ' . $token
		  ),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		// Datos listos para usar
		$persona = json_decode($response);
		//var_dump($persona);
		return $persona;
    }

}
?>