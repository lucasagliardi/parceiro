<?php
 	require_once("../Rest.inc.php");
	
	class API extends REST {
		public $data = "";
 		const DB_SERVER = "db_drvip.mysql.dbaas.com.br";
		const DB_USER = "db_drvip";
		const DB_PASSWORD = "flav0409";
		const DB = "db_drvip";

		private $db = NULL;
		private $mysqli = NULL;
		public function __construct(){
			parent::__construct();				// Init parent contructor
			$this->dbConnect();					// Initiate Database connection
		}
		
		/*
		 *  Connect to Database
		*/
		private function dbConnect(){
			$this->mysqli = new mysqli(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD, self::DB);
		}
		
		/*
		 * Dynmically call the method based on the query string
		 */
		public function processApi(){
			$func = strtolower(trim(str_replace("/","",$_REQUEST['x'])));
			if((int)method_exists($this,$func) > 0)
				$this->$func();
			else
				$this->response('',404); // If the method not exist with in this class "Page not found".
		}
		//listagem de formaPagamento
		private function formaPagamento(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$query="SELECT distinct fp.id, fp.descricao FROM FORMA_RECEBIMENTO fp order by fp.id desc";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}
		private function aceitar(){	
			$id = (int)$this->_request['id'];
			$placa = (string)$this->_request['placa'];
 			$r = $this->mysqli->query("SET NAMES 'utf8';") or die($this->mysqli->error.__LINE__);
 			$call ="CALL ACEITA_VIAGEM(".$id.", '".$placa."', @idSolicitacao, @nome,@nomeCorp, @telefone, @status, @resutado);";
			$r = $this->mysqli->query($call) or die($this->mysqli->error.__LINE__);
 			$query ="SELECT @nomeCorp as nomeCorp, @idSolicitacao as idSolicitacao, @nome as nome, @telefone as telefone, @status as status, @resutado as resultado;";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}
		private function statusViagem(){	
			$id = (int)$this->_request['id'];
			$latitude = (string)$this->_request['latitude'];
			$longitude = (string)$this->_request['longitude'];
 			$r = $this->mysqli->query("SET NAMES 'utf8';") or die($this->mysqli->error.__LINE__);
 			$call ="CALL STATUS_VIAGEM(".$id.",'".$latitude."', '".$longitude."', @estimativaTempo, @status, @result);";
			$r = $this->mysqli->query($call) or die($this->mysqli->error.__LINE__);
 			$query ="SELECT  @estimativaTempo as estimativaTempo, @status as status, @result as result;";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}
		private function avaliarUsuario(){	
			$id = (int)$this->_request['id'];
			$nota = (int)$this->_request['nota'];
 			$r = $this->mysqli->query("SET NAMES 'utf8';") or die($this->mysqli->error.__LINE__);
 			$call ="CALL AVALIAR_USUARIO(".$id.",".$nota.");";
			$r = $this->mysqli->query($call) or die($this->mysqli->error.__LINE__);
			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}
		private function recusar(){	
			$id = (int)$this->_request['id'];
			$placa = (string)$this->_request['placa'];
 			$r = $this->mysqli->query("SET NAMES 'utf8';") or die($this->mysqli->error.__LINE__);
 			$call ="CALL PARCEIRO_RECUSA_VIAGEM(".$id.", '".$placa."', @status, @resutado);";
			$r = $this->mysqli->query($call) or die($this->mysqli->error.__LINE__);
 			$query ="SELECT @status as status, @resutado as resultado;";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}
		private function iniciar(){	
			$id = (int)$this->_request['id'];
			$placa = (string)$this->_request['placa'];
 			$r = $this->mysqli->query("SET NAMES 'utf8';") or die($this->mysqli->error.__LINE__);
 			$call ="CALL INICIA_VIAGEM(".$id.", '".$placa."', @destino, @tarifa);";
			$r = $this->mysqli->query($call) or die($this->mysqli->error.__LINE__);
 			$query ="SELECT @destino as destino, @tarifa as tarifa;";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}
		private function avisoChegada(){	
			$id = (int)$this->_request['id'];
			$placa = (string)$this->_request['placa'];
 			$r = $this->mysqli->query("SET NAMES 'utf8';") or die($this->mysqli->error.__LINE__);
 			$call ="CALL AVISO_CHEGADA_PARCEIRO(".$id.", '".$placa."');";
			$r = $this->mysqli->query($call) or die($this->mysqli->error.__LINE__);
			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}
		private function finalizar(){	
			$id = (int)$this->_request['id'];
			$pagamento = (int)$this->_request['formaPagamento'];
			$placa = (string)$this->_request['placa'];
 			$r = $this->mysqli->query("SET NAMES 'utf8';") or die($this->mysqli->error.__LINE__);
 			$call ="CALL ENCERRA_VIAGEM(".$id.", '".$placa."', ".$pagamento.");";
			$r = $this->mysqli->query($call) or die($this->mysqli->error.__LINE__);
			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}
		private function buscarViagem(){	
			$id = (int)$this->_request['id'];
			$placa = (string)$this->_request['placa'];
			$latitude = (string)$this->_request['latitude'];
			$longitude = (string)$this->_request['longitude'];
 			$r = $this->mysqli->query("SET NAMES 'utf8';") or die($this->mysqli->error.__LINE__);
 			$call ="CALL BUSCA_VIAGEM(".$id.", '".$placa."', '".$latitude."', '".$longitude."', @local, @referencia, @tarifa, @status, @resutado);";
			$r = $this->mysqli->query($call) or die($this->mysqli->error.__LINE__);
 			$query ="SELECT @local as local, @referencia as referencia,@tarifa as tarifa, @status as status, @resutado as resultado;";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}
		
		private function parceiroOnline(){	
			$id = (int)$this->_request['id'];
			$placa = (string)$this->_request['placa'];
			$latitude = (string)$this->_request['latitude'];
			$longitude = (string)$this->_request['longitude'];
 			$r = $this->mysqli->query("SET NAMES 'utf8';") or die($this->mysqli->error.__LINE__);
 			$call ="CALL PARCEIRO_ONLINE(".$id.",'".$placa."', '".$latitude."', '".$longitude."', @idResult, @result);";
			$r = $this->mysqli->query($call) or die($this->mysqli->error.__LINE__);
			$query ="SELECT @idResult as idResult, @result as result;";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}
		private function parceiroOffline(){	
			$id = (int)$this->_request['id'];
			$placa = (string)$this->_request['placa'];
 			$r = $this->mysqli->query("SET NAMES 'utf8';") or die($this->mysqli->error.__LINE__);
 			$call ="CALL PARCEIRO_OFFLINE(".$id.",'".$placa."', @status, @resutado);";
			$r = $this->mysqli->query($call) or die($this->mysqli->error.__LINE__);
			$query ="SELECT @status as status, @resutado as resultado;";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}
		private function cancelarViagem(){	
			$id = (int)$this->_request['id'];
			$placa = (string)$this->_request['placa'];
 			$r = $this->mysqli->query("SET NAMES 'utf8';") or die($this->mysqli->error.__LINE__);
 			$call ="CALL PARCEIRO_CANCELA_VIAGEM(".$id.", '".$placa."');";
			$r = $this->mysqli->query($call) or die($this->mysqli->error.__LINE__);
			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}
		
		/*
		 *	Encode array into JSON
		*/
		private function json($data){
			if(is_array($data)){
				return json_encode($data);
			}
		}
	}
	
	// Initiiate Library
	
	$api = new API;
	$api->processApi();
?>