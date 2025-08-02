<?php

declare(strict_types=1);
	class UnifiProtectEvents extends IPSModule
	{
		public const DEFAULT_WS_URL = 'wss://192.168.178.1/proxy/protect/integration/v1/subscribe/events/';
		public function Create()
		{
			//Never delete this line!
			parent::Create();

			
			$this->RegisterPropertyString( 'ServerAddress', '192.168.178.1' );
			$this->RegisterPropertyString( 'APIKey', '' );
			$this->RegisterPropertyBoolean( 'smartEvents', false );
			$this->RegisterPropertyBoolean( 'motionEvents', false );


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
		}

		public function Send()
		{
			$this->SendDataToParent(json_encode(['DataID' => '{900DB6C2-D4A8-5EC0-59E6-DB62AFB88853}']));
		}

		public function ReceiveData($JSONString)
		{
			$data = json_decode($JSONString);
			IPS_LogMessage('Device RECV', utf8_decode($data->Buffer));
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
			if ($type !== 'smartDetectZone' && $type !== 'motion') {
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
					$camName='Unbekannt';
					$this->SendDebug('HandleEvent', "Sende Event an Kamera $camName (ID: $idCam)", 0);   
				}
			} 			
			$varIdent = 'EventActive_' . $type . '_' . $camID;
			$this->MaintainVariable( $varIdent,  $camName . '-' . $type .' '. 'active' , 0, '', 0, 1 );
			$this->SendDebug('HandleEvent', 'Var Name: ' . $camName . '-' . $type .' '. 'active' . " (ID: $idCam)", 0);
			if ($type === 'smartDetectZone') {
				$eventTypes = $event['item']['smartDetectTypes'] ?? [];
			} else {
				IPS_LogMessage('UnifiProtectEvents', "Unbekannter Event-Typ: $type");
			}
			// Setze die Variable für den Event-Typ
			$active = !isset($event['item']['end']);
			$this->SetValue($varIdent,$active);

			// if (!empty($eventTypes) && $active) {
			// 	// Wenn es Smart Detection Typen gibt, logge sie
			// 	$types = implode(', ', $eventTypes);
			// 	$this->SetValue('smartEventsType', $types);
			// 	IPS_LogMessage('UnifiProtectEvents', "Event $eventId vom Typ $type mit Smart Detection Typen: $types");
			// } else {
			// 	IPS_LogMessage('UnifiProtectEvents', "Event $eventId vom Typ $type ohne Smart Detection Typen");
			// }			
		}

		public function GetConfigurationForParent()
		{
			#'wss://'. $ip ? $ip : self::DEFAULT_WS_URL.'/proxy/protect/integration/v1/subscribe/events/'
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
			$arrayStatus[] = array( 'code' => 102, 'icon' => 'active', 'caption' => 'Instanz ist aktiv' );

			$arrayElements = array();
			$arrayElements[] = array( 'type' => 'Label', 'label' => $this->Translate('UniFi Protect Events')); 
			$arrayElements[] = array( 'type' => 'Label', 'label' => 'Bitte API Key unter "UniFi Network > Settings > Control Plane > Integrations" erzeugen');
			$arrayElements[] = array( 'type' => 'ValidationTextBox', 'name' => 'ServerAddress', 'caption' => 'Unifi Protect Host IP', 'validate' => "^(([a-zA-Z0-9\\.\\-\\_]+(\\.[a-zA-Z]{2,3})+)|(\\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\b))$" );
			$arrayElements[] = array( 'type' => 'ValidationTextBox', 'name' => 'APIKey', 'caption' => 'APIKey' );

			unset($arrayOptions);

			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'smartEvents', 'width' => '220px','caption' => $this->Translate('Smart Detections') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'motionEvents', 'width' => '220px','caption' => $this->Translate('Motion Detections') );

			$arrayElements[] = array( 'type' => 'RowLayout',  'items' => $arrayOptions );

		

			$arrayActions = array();
			unset($arrayOptions);
			// $arrayOptions[] = array( 'type' => 'Button', 'label' => 'Devices Holen', 'width' => '220px','onClick' => 'UNIFIPDV_Send($id,"getCameras","");' );
			// $arrayOptions[] = array( 'type' => 'Button', 'label' => 'Streams auslesen','width' => '220px', 'onClick' => 'UNIFIPDV_Send($id,"getStreams","");' );
			// $arrayOptions[] = array( 'type' => 'Button', 'label' => 'Snapshot holen', 'width' => '220px','onClick' => 'UNIFIPDV_Send($id,"getSnapshot","");' );			
			// $arrayOptions[] = array( 'type' => 'Button', 'label' => 'Daten holen', 'width' => '220px','onClick' => 'UNIFIPDV_Send($id,"getCameraData","");' );			
			// $arrayActions[] = array( 'type' => 'RowLayout',  'items' => $arrayOptions	 );
			return JSON_encode( array( 'status' => $arrayStatus, 'elements' => $arrayElements, 'actions' => $arrayActions ) );
	    }
	}