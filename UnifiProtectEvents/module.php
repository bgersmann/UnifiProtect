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
			$this->RegisterPropertyBoolean( 'lineEvents', false );
			$this->RegisterPropertyBoolean( 'smartAudioEvents', false );
			$this->RegisterPropertyBoolean( 'ringEvents', false );
			$this->RegisterPropertyBoolean( 'sensorExtremeValuesEvents', false );
			$this->RegisterPropertyBoolean( 'sensorWaterLeakEvents', false );
			$this->RegisterPropertyBoolean( 'sensorTamperEvents', false );
			$this->RegisterPropertyBoolean( 'sensorBatteryLowEvents', false );
			$this->RegisterPropertyBoolean( 'sensorAlarmEvents', false );
			$this->RegisterPropertyBoolean( 'sensorOpenedEvents', false );
			$this->RegisterPropertyBoolean( 'sensorClosedEvents', false );
			$this->RegisterPropertyBoolean( 'lightMotionEvents', false );
			$this->RegisterPropertyBoolean( 'smartDetectLoiterZoneEvents', false );
			$this->RegisterPropertyBoolean( 'motionGlobal', false );
			$this->RegisterPropertyBoolean( 'smartGlobal', false );
			$this->RegisterPropertyBoolean( 'sensorGlobal', false );
			$this->RegisterPropertyBoolean( 'lineGlobal', false );
			$this->RegisterPropertyBoolean( 'smartAudioGlobal', false );
			$this->RegisterPropertyBoolean( 'ringGlobal', false );
			$this->RegisterPropertyBoolean( 'sensorExtremeValuesGlobal', false );
			$this->RegisterPropertyBoolean( 'sensorWaterLeakGlobal', false );
			$this->RegisterPropertyBoolean( 'sensorTamperGlobal', false );
			$this->RegisterPropertyBoolean( 'sensorBatteryLowGlobal', false );
			$this->RegisterPropertyBoolean( 'sensorAlarmGlobal', false );
			$this->RegisterPropertyBoolean( 'sensorOpenedGlobal', false );
			$this->RegisterPropertyBoolean( 'sensorClosedGlobal', false );
			$this->RegisterPropertyBoolean( 'lightMotionGlobal', false );
			$this->RegisterPropertyBoolean( 'smartDetectLoiterZoneGlobal', false );

			//smartAudioDetect
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
			$this->MaintainVariable( 'lineGlobal',  $this->Translate('global line detect') , 0, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0 ,'ICON'=> 'sensor','OPTIONS'=>'[{"ColorDisplay":16077123,"Value":false,"Caption":"Keine Bewegung","IconValue":"sensor","IconActive":true,"ColorActive":true,"ColorValue":16077123,"Color":-1},{"ColorDisplay":1692672,"Value":true,"Caption":"Bewegung erkannt","IconValue":"sensor-on","IconActive":true,"ColorActive":true,"ColorValue":1692672,"Color":-1}]'], 0, $this->ReadPropertyBoolean('lineGlobal'));
			$this->MaintainVariable( 'smartAudioGlobal',  $this->Translate('global smart audio detect') , 0, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0 ,'ICON'=> 'sensor','OPTIONS'=>'[{"ColorDisplay":16077123,"Value":false,"Caption":"Keine Bewegung","IconValue":"sensor","IconActive":true,"ColorActive":true,"ColorValue":16077123,"Color":-1},{"ColorDisplay":1692672,"Value":true,"Caption":"Bewegung erkannt","IconValue":"sensor-on","IconActive":true,"ColorActive":true,"ColorValue":1692672,"Color":-1}]'], 0, $this->ReadPropertyBoolean('smartAudioGlobal'));
			$this->MaintainVariable( 'ringGlobal',  $this->Translate('global ring detect') , 0, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0 ,'ICON'=> 'sensor','OPTIONS'=>'[{"ColorDisplay":16077123,"Value":false,"Caption":"Keine Bewegung","IconValue":"sensor","IconActive":true,"ColorActive":true,"ColorValue":16077123,"Color":-1},{"ColorDisplay":1692672,"Value":true,"Caption":"Bewegung erkannt","IconValue":"sensor-on","IconActive":true,"ColorActive":true,"ColorValue":1692672,"Color":-1}]'], 0, $this->ReadPropertyBoolean('ringGlobal'));
			$this->MaintainVariable( 'sensorExtremeValuesGlobal',  $this->Translate('global sensor extreme values detect') , 0, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0 ,'ICON'=> 'sensor','OPTIONS'=>'[{"ColorDisplay":16077123,"Value":false,"Caption":"Keine Bewegung","IconValue":"sensor","IconActive":true,"ColorActive":true,"ColorValue":16077123,"Color":-1},{"ColorDisplay":1692672,"Value":true,"Caption":"Bewegung erkannt","IconValue":"sensor-on","IconActive":true,"ColorActive":true,"ColorValue":1692672,"Color":-1}]'], 0, $this->ReadPropertyBoolean('sensorExtremeValuesGlobal'));
			$this->MaintainVariable( 'sensorWaterLeakGlobal',  $this->Translate('global sensor water leak detect') , 0, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0 ,'ICON'=> 'sensor','OPTIONS'=>'[{"ColorDisplay":16077123,"Value":false,"Caption":"Keine Bewegung","IconValue":"sensor","IconActive":true,"ColorActive":true,"ColorValue":16077123,"Color":-1},{"ColorDisplay":1692672,"Value":true,"Caption":"Bewegung erkannt","IconValue":"sensor-on","IconActive":true,"ColorActive":true,"ColorValue":1692672,"Color":-1}]'], 0, $this->ReadPropertyBoolean('sensorWaterLeakGlobal'));
			$this->MaintainVariable( 'sensorTamperGlobal',  $this->Translate('global sensor tamper detect') , 0, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0 ,'ICON'=> 'sensor','OPTIONS'=>'[{"ColorDisplay":16077123,"Value":false,"Caption":"Keine Bewegung","IconValue":"sensor","IconActive":true,"ColorActive":true,"ColorValue":16077123,"Color":-1},{"ColorDisplay":1692672,"Value":true,"Caption":"Bewegung erkannt","IconValue":"sensor-on","IconActive":true,"ColorActive":true,"ColorValue":1692672,"Color":-1}]'], 0, $this->ReadPropertyBoolean('sensorTamperGlobal'));
			$this->MaintainVariable( 'sensorBatteryLowGlobal',  $this->Translate('global sensor battery low detect') , 0, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0 ,'ICON'=> 'sensor','OPTIONS'=>'[{"ColorDisplay":16077123,"Value":false,"Caption":"Keine Bewegung","IconValue":"sensor","IconActive":true,"ColorActive":true,"ColorValue":16077123,"Color":-1},{"ColorDisplay":1692672,"Value":true,"Caption":"Bewegung erkannt","IconValue":"sensor-on","IconActive":true,"ColorActive":true,"ColorValue":1692672,"Color":-1}]'], 0, $this->ReadPropertyBoolean('sensorBatteryLowGlobal'));
			$this->MaintainVariable( 'sensorAlarmGlobal',  $this->Translate('global sensor alarm detect') , 0, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0 ,'ICON'=> 'sensor','OPTIONS'=>'[{"ColorDisplay":16077123,"Value":false,"Caption":"Keine Bewegung","IconValue":"sensor","IconActive":true,"ColorActive":true,"ColorValue":16077123,"Color":-1},{"ColorDisplay":1692672,"Value":true,"Caption":"Bewegung erkannt","IconValue":"sensor-on","IconActive":true,"ColorActive":true,"ColorValue":1692672,"Color":-1}]'], 0, $this->ReadPropertyBoolean('sensorAlarmGlobal'));
			$this->MaintainVariable( 'sensorOpenedGlobal',  $this->Translate('global sensor opened detect') , 0, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0 ,'ICON'=> 'sensor','OPTIONS'=>'[{"ColorDisplay":16077123,"Value":false,"Caption":"Keine Bewegung","IconValue":"sensor","IconActive":true,"ColorActive":true,"ColorValue":16077123,"Color":-1},{"ColorDisplay":1692672,"Value":true,"Caption":"Bewegung erkannt","IconValue":"sensor-on","IconActive":true,"ColorActive":true,"ColorValue":1692672,"Color":-1}]'], 0, $this->ReadPropertyBoolean('sensorOpenedGlobal'));
			$this->MaintainVariable( 'sensorClosedGlobal',  $this->Translate('global sensor closed detect') , 0, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0 ,'ICON'=> 'sensor','OPTIONS'=>'[{"ColorDisplay":16077123,"Value":false,"Caption":"Keine Bewegung","IconValue":"sensor","IconActive":true,"ColorActive":true,"ColorValue":16077123,"Color":-1},{"ColorDisplay":1692672,"Value":true,"Caption":"Bewegung erkannt","IconValue":"sensor-on","IconActive":true,"ColorActive":true,"ColorValue":1692672,"Color":-1}]'], 0, $this->ReadPropertyBoolean('sensorClosedGlobal'));
			$this->MaintainVariable( 'lightMotionGlobal',  $this->Translate('global light motion detect') , 0, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0 ,'ICON'=> 'sensor','OPTIONS'=>'[{"ColorDisplay":16077123,"Value":false,"Caption":"Keine Bewegung","IconValue":"sensor","IconActive":true,"ColorActive":true,"ColorValue":16077123,"Color":-1},{"ColorDisplay":1692672,"Value":true,"Caption":"Bewegung erkannt","IconValue":"sensor-on","IconActive":true,"ColorActive":true,"ColorValue":1692672,"Color":-1}]'], 0, $this->ReadPropertyBoolean('lightMotionGlobal'));
			$this->MaintainVariable( 'smartDetectLoiterZoneGlobal',  $this->Translate('global smart detect loiter zone') , 0, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0 ,'ICON'=> 'sensor','OPTIONS'=>'[{"ColorDisplay":16077123,"Value":false,"Caption":"Keine Bewegung","IconValue":"sensor","IconActive":true,"ColorActive":true,"ColorValue":16077123,"Color":-1},{"ColorDisplay":1692672,"Value":true,"Caption":"Bewegung erkannt","IconValue":"sensor-on","IconActive":true,"ColorActive":true,"ColorValue":1692672,"Color":-1}]'], 0, $this->ReadPropertyBoolean('smartDetectLoiterZoneGlobal'));
		}

		public function Send()
		{
			$this->SendDataToParent(json_encode(['DataID' => '{900DB6C2-D4A8-5EC0-59E6-DB62AFB88853}']));
		}

		public function ReceiveData($JSONString)
		{
			$data = json_decode($JSONString);
			if (isset($data->Buffer)) {
				$this->HandleEvent($data->Buffer);
			} else {
				$this->SendDebug('UnifiProtectEvents', 'ReceiveData: Ungültige Daten empfangen', 0);
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
				$this->SendDebug('UnifiProtectEvents', 'Ungültiges Event empfangen', 0);
				return;
			}
			$type = $event['item']['type'];
			$camID = $event['item']['device'];
			$eventID = $event['item']['id'];
			$eventType=$event['type'];			
			$Bufferdata = $this->GetBuffer("activeEvents");
			$this->SendDebug('HandleEvent-1',$Bufferdata,0);
			if ($Bufferdata=="") {
				$activeEvents=array();
			} else {
				$activeEvents=json_decode($Bufferdata,true);
			}
			if ($eventType=='add') {
				$activeEvents[]=[$eventID => ['camID'=> $camID, 'type' => $type, 'Start'=> $event['item']['start']]];
			} else {				
				if (isset($event['item']['end'])) {
					$this->SendDebug('HandleEvent-Unset',$eventID,0);
					foreach ($activeEvents as $index => $event) {
						if (array_key_exists($eventID, $event)) {
							unset($activeEvents[$index]);
							#break; // Optional: wenn du nur einen Eintrag mit dieser ID erwartest
						}
					}
				}
			}
			$this->SetBuffer("activeEvents", json_encode($activeEvents));
			$this->SendDebug('HandleEvent-2',json_encode($activeEvents),0);
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
			if ( $type === 'smartDetectLine' && !$this->ReadPropertyBoolean('lineEvents')) {
				return; // Smart Detect Line Events sind deaktiviert
			}
			if ( $type === 'smartAudioDetect' && !$this->ReadPropertyBoolean('smartAudioEvents')) {
				return; // Smart Audio Detection Events sind deaktiviert
			}
			if ( $type === 'ring' && !$this->ReadPropertyBoolean('ringEvents')) {
				return;
			}
			if ( $type === 'sensorExtremeValues' && !$this->ReadPropertyBoolean('sensorExtremeValuesEvents')) {
				return;
			}
			if ( $type === 'sensorWaterLeak' && !$this->ReadPropertyBoolean('sensorWaterLeakEvents')) {
				return;
			}
			if ( $type === 'sensorTamper' && !$this->ReadPropertyBoolean('sensorTamperEvents')) {
				return;
			}
			if ( $type === 'sensorBatteryLow' && !$this->ReadPropertyBoolean('sensorBatteryLowEvents')) {
				return;
			}
			if ( $type === 'sensorAlarm' && !$this->ReadPropertyBoolean('sensorAlarmEvents')) {
				return;
			}
			if ( $type === 'sensorOpened' && !$this->ReadPropertyBoolean('sensorOpenedEvents')) {
				return;
			}
			if ( $type === 'sensorClosed' && !$this->ReadPropertyBoolean('sensorClosedEvents')) {
				return;
			}
			if ( $type === 'lightMotion' && !$this->ReadPropertyBoolean('lightMotionEvents')) {
				return;
			}
			if ( $type === 'smartDetectLoiterZone' && !$this->ReadPropertyBoolean('smartDetectLoiterZoneEvents')) {
				return;
			}
			if ($type !== 'smartDetectZone' && $type !== 'motion' && $type !== 'sensorMotion' && $type !== 'smartDetectLine' && $type !== 'smartAudioDetect' && $type !== 'ring' && $type !== 'sensorExtremeValues' && $type !== 'sensorWaterLeak' && $type !== 'sensorTamper' && $type !== 'sensorBatteryLow' && $type !== 'sensorAlarm' && $type !== 'sensorOpened' && $type !== 'sensorClosed' && $type !== 'lightMotion' && $type !== 'smartDetectLoiterZone') {
				$this->SendDebug('UnifiProtectEvents', "Unbekannter Event-Typ: $type", 0);
				return; // Unbekannter Event-Typ
			}
			$idCam=$this->getInstanceIDForGuid( $camID, '{F78D1159-D735-D23A-0A97-69F07962BB89}' );
			if ($idCam > 0) {				
				// Wenn eine Kamera-ID vorhanden ist, sende das Event an die Kamera-Instanz
				$IDName=@$this->GetIDForIdent('Name');
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
			$active=false;
			foreach ($activeEvents as $event) {
				foreach ($event as $details) {
					if ($details['camID'] === $camID && $details['type'] === $type) {
						$active = true;
						break 2; // Bricht beide Schleifen ab
					}
				}
			}
			$this->SetValue($varIdent,$active);

			$active=false;
			foreach ($activeEvents as $event) {
				foreach ($event as $details) {
					if ($details['type'] === $type) {
						$active = true;
						break 2; // Bricht beide Schleifen ab, sobald ein Treffer gefunden wurde
					}
				}
			}			
			if( $type === 'smartDetectZone' && $this->ReadPropertyBoolean('smartGlobal')) {
				$this->SetValue('smartGlobal',$active);
			}
			if ( $type === 'motion' && $this->ReadPropertyBoolean('motionGlobal')) {
				$this->SetValue('motionGlobal',$active);
			}
			if ( $type === 'sensorMotion' && $this->ReadPropertyBoolean('sensorGlobal')) {
				$this->SetValue('sensorGlobal',$active);
			}
			if ( $type === 'smartDetectLine' && $this->ReadPropertyBoolean('lineGlobal')) {
				$this->SetValue('lineGlobal',$active);
			}
			if ( $type === 'smartAudioDetect' && $this->ReadPropertyBoolean('smartAudioGlobal')) {
				$this->SetValue('smartAudioGlobal',$active);
			}
			if ( $type === 'ring' && $this->ReadPropertyBoolean('ringGlobal')) {
				$this->SetValue('ringGlobal',$active);
			}
			if ( $type === 'sensorExtremeValues' && $this->ReadPropertyBoolean('sensorExtremeValuesGlobal')) {
				$this->SetValue('sensorExtremeValuesGlobal',$active);
			}
			if ( $type === 'sensorWaterLeak' && $this->ReadPropertyBoolean('sensorWaterLeakGlobal')) {
				$this->SetValue('sensorWaterLeakGlobal',$active);
			}
			if ( $type === 'sensorTamper' && $this->ReadPropertyBoolean('sensorTamperGlobal')) {
				$this->SetValue('sensorTamperGlobal',$active);
			}
			if ( $type === 'sensorBatteryLow' && $this->ReadPropertyBoolean('sensorBatteryLowGlobal')) {
				$this->SetValue('sensorBatteryLowGlobal',$active);
			}
			if ( $type === 'sensorAlarm' && $this->ReadPropertyBoolean('sensorAlarmGlobal')) {
				$this->SetValue('sensorAlarmGlobal',$active);
			}
			if ( $type === 'sensorOpened' && $this->ReadPropertyBoolean('sensorOpenedGlobal')) {
				$this->SetValue('sensorOpenedGlobal',$active);
			}
			if ( $type === 'sensorClosed' && $this->ReadPropertyBoolean('sensorClosedGlobal')) {
				$this->SetValue('sensorClosedGlobal',$active);
			}
			if ( $type === 'lightMotion' && $this->ReadPropertyBoolean('lightMotionGlobal')) {
				$this->SetValue('lightMotionGlobal',$active);
			}
			if ( $type === 'smartDetectLoiterZone' && $this->ReadPropertyBoolean('smartDetectLoiterZoneGlobal')) {
				$this->SetValue('smartDetectLoiterZoneGlobal',$active);
			}

		}

		public function GetConfigurationForParent()
		{			
			$parent = IPS_GetInstance($this->InstanceID)['ConnectionID'];
			$ip = $this->ReadPropertyString('ServerAddress');
			$apiKey = $this->ReadPropertyString('APIKey');
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
			$arrayElements[] = array('type' => 'Label', 'bold' => true, 'label' => $this->Translate('Variable for events'));
			$arrayOptions = array(
				array( 'type' => 'CheckBox', 'name' => 'smartEvents', 'width' => '220px','caption' => $this->Translate('Smart Detections') ),
				array( 'type' => 'CheckBox', 'name' => 'motionEvents', 'width' => '220px','caption' => $this->Translate('Motion Detections') ),
				array( 'type' => 'CheckBox', 'name' => 'sensorMotionEvents', 'width' => '220px','caption' => $this->Translate('Sensor Motion Detections') ),
				array( 'type' => 'CheckBox', 'name' => 'lineEvents', 'width' => '220px','caption' => $this->Translate('Line Events') ),
				array( 'type' => 'CheckBox', 'name' => 'smartAudioEvents', 'width' => '220px','caption' => $this->Translate('Smart Audio Detections') ),
				array( 'type' => 'CheckBox', 'name' => 'ringEvents', 'width' => '220px','caption' => $this->Translate('Ring Events') ),
				array( 'type' => 'CheckBox', 'name' => 'sensorExtremeValuesEvents', 'width' => '220px','caption' => $this->Translate('Sensor Extreme Values Events') ),
				array( 'type' => 'CheckBox', 'name' => 'sensorWaterLeakEvents', 'width' => '220px','caption' => $this->Translate('Sensor Water Leak Events') ),
				array( 'type' => 'CheckBox', 'name' => 'sensorTamperEvents', 'width' => '220px','caption' => $this->Translate('Sensor Tamper Events') ),
				array( 'type' => 'CheckBox', 'name' => 'sensorBatteryLowEvents', 'width' => '220px','caption' => $this->Translate('Sensor Battery Low Events') ),
				array( 'type' => 'CheckBox', 'name' => 'sensorAlarmEvents', 'width' => '220px','caption' => $this->Translate('Sensor Alarm Events') ),
				array( 'type' => 'CheckBox', 'name' => 'sensorOpenedEvents', 'width' => '220px','caption' => $this->Translate('Sensor Opened Events') ),
				array( 'type' => 'CheckBox', 'name' => 'sensorClosedEvents', 'width' => '220px','caption' => $this->Translate('Sensor Closed Events') ),
				array( 'type' => 'CheckBox', 'name' => 'lightMotionEvents', 'width' => '220px','caption' => $this->Translate('Light Motion Events') ),
				array( 'type' => 'CheckBox', 'name' => 'smartDetectLoiterZoneEvents', 'width' => '220px','caption' => $this->Translate('Smart Detect Loiter Zone Events') )
			);
			$chunkSize = (int)ceil(count($arrayOptions) / 3);
			foreach (array_chunk($arrayOptions, $chunkSize) as $optionRow) {
				$arrayElements[] = array( 'type' => 'RowLayout',  'items' => $optionRow );
			}
			
			
			unset($arrayOptions);#Variable for Global 
			$arrayElements[] = array('type' => 'Label', 'bold' => true, 'label' => $this->Translate('Variable for global events'));
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'smartGlobal', 'width' => '220px','caption' => $this->Translate('Smart Detection') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'motionGlobal', 'width' => '220px','caption' => $this->Translate('Motion Detections') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'sensorGlobal', 'width' => '220px','caption' => $this->Translate('Sensor Motion Detections') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'lineGlobal', 'width' => '220px','caption' => $this->Translate('Line Events') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'smartAudioGlobal', 'width' => '220px','caption' => $this->Translate('Smart Audio Detections') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'ringGlobal', 'width' => '220px','caption' => $this->Translate('Ring Events') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'sensorExtremeValuesGlobal', 'width' => '220px','caption' => $this->Translate('Sensor Extreme Values Events') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'sensorWaterLeakGlobal', 'width' => '220px','caption' => $this->Translate('Sensor Water Leak Events') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'sensorTamperGlobal', 'width' => '220px','caption' => $this->Translate('Sensor Tamper Events') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'sensorBatteryLowGlobal', 'width' => '220px','caption' => $this->Translate('Sensor Battery Low Events') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'sensorAlarmGlobal', 'width' => '220px','caption' => $this->Translate('Sensor Alarm Events') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'sensorOpenedGlobal', 'width' => '220px','caption' => $this->Translate('Sensor Opened Events') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'sensorClosedGlobal', 'width' => '220px','caption' => $this->Translate('Sensor Closed Events') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'lightMotionGlobal', 'width' => '220px','caption' => $this->Translate('Light Motion Events') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'smartDetectLoiterZoneGlobal', 'width' => '220px','caption' => $this->Translate('Smart Detect Loiter Zone Events') );
			$chunkSize = (int)ceil(count($arrayOptions) / 3);
			foreach (array_chunk($arrayOptions, $chunkSize) as $optionRow) {
				$arrayElements[] = array( 'type' => 'RowLayout',  'items' => $optionRow );
			}

		

			$arrayActions = array();
			unset($arrayOptions);			
			return JSON_encode( array( 'status' => $arrayStatus, 'elements' => $arrayElements, 'actions' => $arrayActions ) );
	    }
	}