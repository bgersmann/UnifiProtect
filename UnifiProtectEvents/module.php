<?php

declare(strict_types=1);
	class UnifiProtectEvents extends IPSModule
	{
		public const DEFAULT_WS_URL = '192.168.1.1';
		public function Create()
		{
			//Never delete this line!
			parent::Create();

			
			$this->RegisterPropertyString( 'ServerAddress', '192.168.178.1' );
			$this->RegisterPropertyString( 'APIKey', '' );
			$this->RegisterPropertyBoolean( 'smartEvents', false );
			$this->RegisterPropertyBoolean( 'motionEvents', false );
			$this->RegisterPropertyBoolean( 'sensorMotionEvents', false );
			$this->RegisterPropertyBoolean( 'motionGlobal', false );
			$this->RegisterPropertyBoolean( 'smartGlobal', false );
			$this->RegisterPropertyBoolean( 'sensorGlobal', false );
			$this->RequireParent('{D68FD31F-0E90-7019-F16C-1949BD3079EF}');
		}

		public function Destroy()
		{
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
			$this->SetSummary($this->ReadPropertyString('ServerAddress'));
			$this->MaintainVariable( 'motionGlobal',  $this->Translate('global motion detect') , 0, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0 ,'ICON'=> 'sensor','OPTIONS'=>'[{"ColorDisplay":16077123,"Value":false,"Caption":"Keine Bewegung","IconValue":"sensor","IconActive":true,"ColorActive":true,"ColorValue":16077123,"Color":-1},{"ColorDisplay":1692672,"Value":true,"Caption":"Bewegung erkannt","IconValue":"sensor-on","IconActive":true,"ColorActive":true,"ColorValue":1692672,"Color":-1}]'], 0, $this->ReadPropertyBoolean('motionGlobal'));
			$this->MaintainVariable( 'smartGlobal',  $this->Translate('global smart detect') , 0, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0 ,'ICON'=> 'sensor','OPTIONS'=>'[{"ColorDisplay":16077123,"Value":false,"Caption":"Keine Bewegung","IconValue":"sensor","IconActive":true,"ColorActive":true,"ColorValue":16077123,"Color":-1},{"ColorDisplay":1692672,"Value":true,"Caption":"Bewegung erkannt","IconValue":"sensor-on","IconActive":true,"ColorActive":true,"ColorValue":1692672,"Color":-1}]'], 0, $this->ReadPropertyBoolean('smartGlobal'));
			$this->MaintainVariable( 'sensorGlobal',  $this->Translate('global sensor detect') , 0, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0 ,'ICON'=> 'sensor','OPTIONS'=>'[{"ColorDisplay":16077123,"Value":false,"Caption":"Keine Bewegung","IconValue":"sensor","IconActive":true,"ColorActive":true,"ColorValue":16077123,"Color":-1},{"ColorDisplay":1692672,"Value":true,"Caption":"Bewegung erkannt","IconValue":"sensor-on","IconActive":true,"ColorActive":true,"ColorValue":1692672,"Color":-1}]'], 0, $this->ReadPropertyBoolean('sensorGlobal'));
		}

		public function Send()
		{
			$this->SendDataToParent(json_encode(['DataID' => '{900DB6C2-D4A8-5EC0-59E6-DB62AFB88853}']));
		}

		public function ReceiveData($JSONString)
		{
			$data = json_decode($JSONString);
			#IPS_LogMessage('Device RECV', utf8_decode($data->Buffer));
			if (isset($data->Buffer)) {
				$this->HandleEvent($data->Buffer);
			} else {
				IPS_LogMessage('UnifiProtectEvents', 'ReceiveData: Ungültige Daten empfangen');
			}			
		}

		/**
		 * Verarbeitet Events aus events.php und setzt je nach Event-Typ eine Variable auf true/false.
		 * @param string $eventJson JSON-String eines Events (wie bsp1-4)
		 */
		public function HandleEvent(string $eventJson):void
		{
			$this->SendDebug('HandleEvent', $eventJson, 0);
			$event = json_decode($eventJson, true);
			if (!isset($event['item']['type'])) {
				IPS_LogMessage('UnifiProtectEvents', 'Ungültiges Event empfangen');
				return;
			}
			$type = $event['item']['type'];
			$camID = $event['item']['device'];
			
			// Logik für Smart Detection Events
			if( $type === 'smartDetectZone' && !$this->ReadPropertyBoolean('smartEvents')) {
				return; // Smart Detection Events sind deaktiviert
			}
			if ( $type === 'motion' && !$this->ReadPropertyBoolean('motionEvents')) {
				return; // Motion Detection Events sind deaktiviert
			}
			if ( $type === 'sensorMotion' && !$this->ReadPropertyBoolean('sensorMotionEvents')) {
				return; // Sensor Motion Detection Events sind deaktiviert
			}
			if ($type !== 'smartDetectZone' && $type !== 'motion' && $type !== 'sensorMotion') {
				IPS_LogMessage('UnifiProtectEvents', "Unbekannter Event-Typ: $type");
				return; // Unbekannter Event-Typ
			}
			$idCam=$this->getInstanceIDForGuid( $camID, '{F78D1159-D735-D23A-0A97-69F07962BB89}' );
			if ($idCam > 0) {
				// Wenn eine Kamera-ID vorhanden ist, sende das Event an die Kamera-Instanz
				$IDName=@IPS_GetObjectIDByIdent('Name',$idCam);
				if (!$IDName === false) {
					$camName=GetValueString($IDName);
					$this->SendDebug('HandleEvent', "Sende Event an Kamera $camName (ID: $idCam)", 0);   
				} else {
					$camName=$this->Translate('Unknown');
					$this->SendDebug('HandleEvent', "Sende Event an Kamera $camName (ID: $idCam)", 0);   
				}
			} else {
				// Wenn keine Kamera-ID vorhanden ist, setze einen generischen Namen
				$camName = $this->Translate('Unknown');
				$this->SendDebug('HandleEvent', "Keine Kamera-ID gefunden, setze generischen Namen: $camName", 0);
			}
			if ($camName==$this->Translate('Unknown')&& $type == 'sensorMotion') {
				//Sensor noch nicht integriert
				$camName='Sensor-'.$camID;
			}
			$varIdent = 'EventActive_' . $type . '_' . $camID;
			$this->MaintainVariable( $varIdent,  $camName . '-' . $type .' '. $this->Translate('active') , 0, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0 ,'ICON'=> 'sensor','OPTIONS'=>'[{"ColorDisplay":16077123,"Value":false,"Caption":"'.$this->Translate('no motion').'","IconValue":"sensor","IconActive":true,"ColorActive":true,"ColorValue":16077123,"Color":-1},{"ColorDisplay":1692672,"Value":true,"Caption":"'.$this->Translate('motion detected').'","IconValue":"sensor-on","IconActive":true,"ColorActive":true,"ColorValue":1692672,"Color":-1}]'], 0, 1 );
			$this->SendDebug('HandleEvent', 'Var Name: ' . $camName . '-' . $type .' '. 'active' . " (ID: $idCam)", 0);
			// Setze die Variable für den Event-Typ
			$active = !isset($event['item']['end']);
			$this->SetValue($varIdent,$active);
			if( $type === 'smartDetectZone' && $this->ReadPropertyBoolean('smartGlobal')) {
				$this->SetValue('smartGlobal',$active);
			}
			if ( $type === 'motion' && $this->ReadPropertyBoolean('motionGlobal')) {
				$this->SetValue('motionGlobal',$active);
			}
			if ( $type === 'sensorMotion' && $this->ReadPropertyBoolean('sensorGlobal')) {
				$this->SetValue('sensorGlobal',$active);
			}
		
		}

		public function GetConfigurationForParent()
		{
			$parent = IPS_GetInstance($this->InstanceID)['ConnectionID'];
			$ip = IPS_GetProperty($this->InstanceID, 'ServerAddress');
			$apiKey = IPS_GetProperty($this->InstanceID, 'APIKey');
			$status=false;
			if (!empty($ip) && !empty($apiKey)) {
				$status=true;
			}
			$jsonArray= [
				'URL'               => 'wss://'. ($ip ? $ip : self::DEFAULT_WS_URL).'/proxy/protect/integration/v1/subscribe/events/',
				'VerifyCertificate' => false,
				'Type'              => 0,
				'Headers'			=> json_encode([
					['Name' => 'X-API-KEY', 'Value' => $apiKey]
				]),
				'Active'		   => $status
			];
			#IPS_LogMessage('GetConfigurationForParent', 'URL: ' . json_encode($jsonArray));
			return json_encode($jsonArray);			
		}


		private function getInstanceIDForGuid( $id, $guid )
		{
			$instanceIDs = IPS_GetInstanceListByModuleID( $guid );
			foreach ( $instanceIDs as $instanceID ) {
				if ( IPS_GetProperty( $instanceID, 'ID' ) == $id ) {
					return $instanceID;
				}
			}
			return 0;
		}

		public function GetConfigurationForm(){
			$arrayStatus = array();
			$arrayStatus[] = array( 'code' => 102, 'icon' => 'active', 'caption' => $this->Translate('Instance is active') );
			$arrayStatus[] = array( 'code' => 104, 'icon' => 'inactive', 'caption' => $this->Translate('Instance is inactive') );


			$arrayElements = array();
			$arrayElements[] = array( 'type' => 'Label','bold' => true, 'label' => $this->Translate('UniFi Protect Events')); 
			$arrayElements[] = array( 'type' => 'Label', 'label' => $this->Translate('Please create API Key under "UniFi Network > Settings > Control Plane > Integrations"'));
			$arrayElements[] = array( 'type' => 'ValidationTextBox', 'name' => 'ServerAddress', 'caption' => $this->Translate('Unifi Protect Host IP'), 'validate' => "^(([a-zA-Z0-9\\.\\-\\_]+(\\.[a-zA-Z]{2,3})+)|(\\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\b))$" );
			$arrayElements[] = array( 'type' => 'ValidationTextBox', 'name' => 'APIKey', 'caption' => $this->Translate('APIKey') );

			unset($arrayOptions);
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'smartEvents', 'width' => '220px','caption' => $this->Translate('Smart Detections') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'motionEvents', 'width' => '220px','caption' => $this->Translate('Motion Detections') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'sensorMotionEvents', 'width' => '240px','caption' => $this->Translate('Sensor Motion Detections') );
			$arrayElements[] = array( 'type' => 'RowLayout',  'items' => $arrayOptions );
			
			
			unset($arrayOptions);#Variable for Global 
			$arrayElements[] = array('type' => 'Label', 'bold' => true, 'label' => $this->Translate('Variable for global events'));
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'smartGlobal', 'width' => '220px','caption' => $this->Translate('Smart Detection') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'motionGlobal', 'width' => '220px','caption' => $this->Translate('Motion Detections') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'sensorGlobal', 'width' => '240px','caption' => $this->Translate('Sensor Motion Detections') );
			$arrayElements[] = array( 'type' => 'RowLayout',  'items' => $arrayOptions );

		

			$arrayActions = array();
			unset($arrayOptions);			
			return JSON_encode( array( 'status' => $arrayStatus, 'elements' => $arrayElements, 'actions' => $arrayActions ) );
	    }
	}