<?php 
//defines
include_once('HttpRequest/XmlRpc.php');
include_once('HttpRequest/XmlRpcZend.php');
include_once('data.php');
$time_start = microtime(true); 

$patron = '/t.co|goo.gl|facebook|twitter|google|youtube|\.gov|\.edu|\.de|\.fr|\.us|\.cc|\.ch|\.biz|\.cx|\.ru|\.pl/';
$patron2 = '/(\w{5,}\.\w{2,})$/';

	function check_dominio_dina($dominio){
		global $username, $password;		

		$request = HttpRequest_XmlRpc::factory( 'https://dinahosting.com/special/api.php' )->setAuthentication( $username, $password,  HttpRequest_XmlRpc::HTTP_AUTH_BASIC );

		$response = $request->verifySslPeer(false)->setTimeout(20)->sendRequest( 'Domain_CheckForRegister', array($dominio) );



    //$response = $request->setTimeout(90)->sendRequest( 'Domain_CheckForRegister', array('54330e48f1e63.com') );



		if( !$request->hasError() ){
			$content = $response->getBody();
			if( is_array( $content ) && isset( $content['responseCode'] ) && ( $content['responseCode'] == 1000 ) ){
            // command execution was OK
				$data = $content['data'];
				return($data);
			}else{
				return "error";
			}
		}else{
			return "error";
		}
	}


	$datos = file('checkdominios.txt', FILE_SKIP_EMPTY_LINES);
	if($datos != FALSE){
		$contador = count($datos);
		foreach($datos as $indice => $dominio){
			if(preg_match($patron, $dominio) == 0){
				//limpio las URLs que tengan /
				$intermedio = preg_split('/\//',$dominio);
				$dominio = $intermedio[0];
				//limpio los posibles subdominios que me encuentre
				//$coincidencias = preg_split($patron2, $dominio, -1, PREG_SPLIT_DELIM_CAPTURE);
				//$dominio = $coincidencias[1];
				if(strpos($dominio, '.') !== FALSE){
					//$dominio = parse_url($dominio,1);
					if($dominio != ''){
						echo "[Dominio $indice de $contador] => $dominio\n";
						if(check_dominio_dina(trim($dominio)) === TRUE){
							echo trim($dominio).'==> '.'BINGO';
							if(file_put_contents('bingos.txt', trim($dominio)."\n", FILE_APPEND | LOCK_EX) === FALSE){
								echo 'ERROR AL GUARDAR EN FICHERO';
							}
						}
						echo "\n";
					}
				}
		//echo '<br/>';
			}
		}
	}
	$time_end = microtime(true);
	$tiempo = ($time_end - $time_start)/60;
	//mando reporte cuando todo ha terminado
	$cabeceras = 'From: correo@correo.com' . "\r\n" . 'Reply-To: correo@correo.com';
	mail ( 'correo@correo.com' , 'SEOBINGOS ha hecho su trabajo' , 'SEOBINGOS ha hecho todo lo que le has pedido en un tiempo de '.$tiempo.' minutos', $cabeceras );

	?>
