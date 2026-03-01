<?php

declare(strict_types=1);
	class UnifiProtectEvents extends IPSModuleStrict
	{
		public const DEFAULT_WS_URL = '192.168.1.1';
		public function Create():void
		{
			//Never delete this line!
			parent::Create();		
			$this->RegisterPropertyString( 'ServerAddress', '192.168.178.1' );
			$this->RegisterPropertyString( 'APIKey', '' );
			$this->RegisterPropertyString( 'SmartDetectSelections', '[]' );
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
			
		}

		public function GetCompatibleParents(): string
        {
            return json_encode([
                'type' => 'connect',
                'moduleIDs' => [
                    '{D68FD31F-0E90-7019-F16C-1949BD3079EF}'
                ]
            ]);
        }
		public function Destroy():void
		{
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges():void
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
			$this->synchronizeConfiguredVariables();
			// Set status
			$APIKey = $this->ReadPropertyString( 'APIKey' );
        	if (empty($APIKey))
			{
				// instance inactive
				$this->SetStatus( 104 );
			} else {
				// instance active
				$this->SetStatus( 102 );
				$this->SetSummary($this->ReadPropertyString("ServerAddress"));        
			}
		}

		public function Send():void
		{
			$this->SendDataToParent(json_encode(['DataID' => '{900DB6C2-D4A8-5EC0-59E6-DB62AFB88853}']));
		}

		public function ReceiveData($JSONString):string
		{
			$data = json_decode($JSONString);
			if (isset($data->Buffer)) {
				$this->HandleEvent(hex2bin($data->Buffer));
			} else {
				$this->SendDebug('UNIFIPEV', 'ReceiveData: Ungültige Daten empfangen', 0);
			}
			return "";
		}

		/**
		 * Verarbeitet Events aus events.php und setzt je nach Event-Typ eine Variable auf true/false.
		 * @param string $eventJson JSON-String eines Events (wie bsp1-4)
		 */
		public function HandleEvent(string $eventJson):void
		{
			$this->SendDebug('UNIFIPEV', $eventJson, 0);
			$event = json_decode($eventJson, true);
			if (!isset($event['item']['type'])) {
				$this->SendDebug('UNIFIPEV', 'Ungültiges Event empfangen', 0);
				return;
			}
			$typeRaw = (string)$event['item']['type'];
			$type = $this->normalizeEventType($typeRaw);
			$deviceID = $event['item']['device'];
			$eventID = $event['item']['id'];
			$eventType=$event['type'];		
			$smartDetectionEvent = in_array($type, ['smartDetectZone', 'smartDetectLine', 'smartDetectLoiterZone'], true);
			$Bufferdata = $this->GetBuffer("activeEvents");
			$this->SendDebug('UNIFIPEV',$Bufferdata,0);
			if ($Bufferdata=="") {
				$activeEvents=array();
			} else {
				$activeEvents=json_decode($Bufferdata,true);
			}
			$smartDetectTypesPayload = $event['item']['smartDetectTypes'] ?? [];
			if ($eventType=='add') {
				$eventData = [
					'deviceID'=> $deviceID,
					'type' => $type,
					'Start'=> $event['item']['start'] ?? 0
				];
				if ($smartDetectionEvent) {
					$eventData['smartDetectTypes'] = $smartDetectTypesPayload;
				}
				$activeEvents[]=[$eventID => $eventData];
			} else {			
				if ($smartDetectionEvent && !empty($smartDetectTypesPayload)) {
					foreach ($activeEvents as $index => $storedEvent) {
						if (array_key_exists($eventID, $storedEvent)) {
							$activeEvents[$index][$eventID]['smartDetectTypes'] = $smartDetectTypesPayload;
						}
					}
				}
				if (isset($event['item']['end'])) {
					$this->SendDebug('UNIFIPEV',$eventID,0);
					foreach ($activeEvents as $index => $storedEvent) {
						if (array_key_exists($eventID, $storedEvent)) {
							unset($activeEvents[$index]);							
						}
					}
				}
			}
			$this->SetBuffer("activeEvents", json_encode($activeEvents));
			$this->SendDebug('UNIFIPEV',json_encode($activeEvents),0);
			// Logik für Smart Detection Events
			$allowedTypes = ['smartDetectZone', 'motion', 'sensorMotion', 'smartDetectLine', 'smartAudioDetect', 'ring', 'sensorExtremeValues', 'sensorWaterLeak', 'sensorTamper', 'sensorBatteryLow', 'sensorAlarm', 'sensorOpened', 'sensorClosed', 'sensorSmokeTest', 'lightMotion', 'smartDetectLoiterZone'];
			if (!in_array($type, $allowedTypes, true)) {
				$this->SendDebug('UNIFIPEV', "Unbekannter Event-Typ: $type", 0);
				return; // Unbekannter Event-Typ
			}
			$this->updateSmartDetectSelectionStates($deviceID, $activeEvents);
			$this->updateStandardEventSelectionStates($deviceID, $activeEvents);
			

			$globalActive=false;
			foreach ($activeEvents as $storedEvent) {
				foreach ($storedEvent as $details) {
					if ($details['type'] === $type) {
						$globalActive = true;
						break 2; // Bricht beide Schleifen ab, sobald ein Treffer gefunden wurde
					}
				}
			}			
			if( $type === 'smartDetectZone' && $this->ReadPropertyBoolean('smartGlobal')) {
				$this->SetValue('smartGlobal',$globalActive);
			}
			if ( $type === 'motion' && $this->ReadPropertyBoolean('motionGlobal')) {
				$this->SetValue('motionGlobal',$globalActive);
			}
			if ( $type === 'sensorMotion' && $this->ReadPropertyBoolean('sensorGlobal')) {
				$this->SetValue('sensorGlobal',$globalActive);
			}
			if ( $type === 'smartDetectLine' && $this->ReadPropertyBoolean('lineGlobal')) {
				$this->SetValue('lineGlobal',$globalActive);
			}
			if ( $type === 'smartAudioDetect' && $this->ReadPropertyBoolean('smartAudioGlobal')) {
				$this->SetValue('smartAudioGlobal',$globalActive);
			}
			if ( $type === 'ring' && $this->ReadPropertyBoolean('ringGlobal')) {
				$this->SetValue('ringGlobal',$globalActive);
			}
			if ( $type === 'sensorExtremeValues' && $this->ReadPropertyBoolean('sensorExtremeValuesGlobal')) {
				$this->SetValue('sensorExtremeValuesGlobal',$globalActive);
			}
			if ( $type === 'sensorWaterLeak' && $this->ReadPropertyBoolean('sensorWaterLeakGlobal')) {
				$this->SetValue('sensorWaterLeakGlobal',$globalActive);
			}
			if ( $type === 'sensorTamper' && $this->ReadPropertyBoolean('sensorTamperGlobal')) {
				$this->SetValue('sensorTamperGlobal',$globalActive);
			}
			if ( $type === 'sensorBatteryLow' && $this->ReadPropertyBoolean('sensorBatteryLowGlobal')) {
				$this->SetValue('sensorBatteryLowGlobal',$globalActive);
			}
			if ( $type === 'sensorAlarm' && $this->ReadPropertyBoolean('sensorAlarmGlobal')) {
				$this->SetValue('sensorAlarmGlobal',$globalActive);
			}
			if ( $type === 'sensorOpened' && $this->ReadPropertyBoolean('sensorOpenedGlobal')) {
				$this->SetValue('sensorOpenedGlobal',$globalActive);
			}
			if ( $type === 'sensorClosed' && $this->ReadPropertyBoolean('sensorClosedGlobal')) {
				$this->SetValue('sensorClosedGlobal',$globalActive);
			}
			if ( $type === 'lightMotion' && $this->ReadPropertyBoolean('lightMotionGlobal')) {
				$this->SetValue('lightMotionGlobal',$globalActive);
			}
			if ( $type === 'smartDetectLoiterZone' && $this->ReadPropertyBoolean('smartDetectLoiterZoneGlobal')) {
				$this->SetValue('smartDetectLoiterZoneGlobal',$globalActive);
			}

		}

		public function GetConfigurationForParent():string
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
			return json_encode($jsonArray);			
		}


		public function getApiData(string $endpoint = ''): array {
			$maxRetries = 5;
			$retry = 0;
			do {
				if (!IPS_SemaphoreEnter("UnifiProtectAPI", 50)) {
					$this->SendDebug("UNIFIPEV", "Semaphore Timeout - Request abgebrochen", 0);
					return [];
				}
				try {
					$starttime = microtime(true);
					$ServerAddress = $this->ReadPropertyString('ServerAddress');
					$APIKey = $this->ReadPropertyString('APIKey');
					$responseHeaders = [];
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, 'https://' . $ServerAddress . '/proxy/protect/integration/v1' . $endpoint);
					curl_setopt($ch, CURLOPT_HTTPGET, true);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-API-KEY:' . $APIKey]);
					curl_setopt($ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1');
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
					curl_setopt($ch, CURLOPT_TIMEOUT, 10);
					curl_setopt($ch, CURLOPT_HEADERFUNCTION, static function ($chCurl, $header) use (&$responseHeaders) {
						$len = strlen($header);
						$parts = explode(':', $header, 2);
						if (count($parts) === 2) {
							$responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
						}
						return $len;
					});
					$RawData = curl_exec($ch);
					$curl_error = curl_error($ch);
					$httpCode = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
					curl_close($ch);

					$this->SendDebug("UNIFIPEV", "API Endpoint: " . $RawData, 0);

					if ($RawData === false) {
						$this->SendDebug("UNIFIPEV", "Curl error: " . $curl_error, 0);
						$this->SetStatus(201);
						return [];
					}

					if ($httpCode === 429) {
						$retryAfterHeader = $responseHeaders['retry-after'] ?? '';
						$retryAfter = is_numeric($retryAfterHeader) ? max(0.5, (float)$retryAfterHeader) : 0.5;
						$this->SendDebug("UNIFIPEV", "Rate Limit erreicht, warte " . $retryAfter . "s", 0);
						$retry++;
						usleep((int)($retryAfter * 1_000_000));
						continue;
					}

					$JSONData = json_decode($RawData, true);
					if (isset($JSONData['statusCode']) && $JSONData['statusCode'] !== 200) {
						$this->SendDebug("UNIFIPEV", "Curl error: " . $JSONData['statusCode'], 0);
						$this->SetStatus($JSONData['statusCode']);
					}

					$elapsed = microtime(true) - $starttime;
					if ($elapsed < 0.05) {
						usleep((int)((0.05 - $elapsed) * 1_000_000));
					}
					return $JSONData;
				} finally {
					IPS_SemaphoreLeave("UnifiProtectAPI");
				}
			} while ($retry < $maxRetries);

			return [];
		}
		public function GetConfigurationForm():string{
			$arrayStatus = array();
			$arrayStatus[] = array( 'code' => 102, 'icon' => 'active', 'caption' => $this->Translate('Instance is active') );
			$arrayStatus[] = array( 'code' => 104, 'icon' => 'inactive', 'caption' => $this->Translate('Instance is inactive') );
			$arrayStatus[] = array( 'code' => 201, 'icon' => 'inactive', 'caption' => $this->Translate('API Error') );


			$arrayElements = array();
			$arrayElements[] = array( 'type' => 'Label','bold' => true, 'label' => $this->Translate('UniFi Protect Events')); 
			$arrayElements[] = array( 'type' => 'Label', 'label' => $this->Translate('Please create API Key under "UniFi Network > Settings > Control Plane > Integrations"'));
			$arrayElements[] = array( 'type' => 'ValidationTextBox', 'name' => 'ServerAddress', 'caption' => $this->Translate('Unifi Protect Host IP'), 'validate' => "^(([a-zA-Z0-9\\.\\-\\_]+(\\.[a-zA-Z]{2,3})+)|(\\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\b))$" );
			$arrayElements[] = array( 'type' => 'ValidationTextBox', 'name' => 'APIKey', 'caption' => $this->Translate('APIKey') );

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

			$eventList = $this->getEventConfiguratorValues();
			$this->SendDebug('UNIFIPEV', 'Device Event List: ' . json_encode($eventList), 0);
			$arrayElements[] = array('type' => 'Label', 'bold' => true, 'label' => $this->Translate('Device event variables'));
			if (!empty($eventList)) {
				$arrayElements[] = array(
					'type' => 'List',
					'name' => 'SmartDetectSelections',
					'rowCount' => 12,
					'add' => false,
					'delete' => false,
					'loadValuesFromConfiguration' => false,
					'columns' => array(
						array('caption' => $this->Translate('Device'), 'name' => 'CameraName', 'width' => '180px', 'save' => true),
						array('caption' => $this->Translate('Device ID'), 'name' => 'CameraID', 'width' => '200px', 'save' => true),
						array('caption' => $this->Translate('Category'), 'name' => 'Category', 'width' => '120px', 'save' => true),
						array('caption' => $this->Translate('Event / type'), 'name' => 'DetectType', 'width' => '200px', 'save' => true),
						array('caption' => 'Mode', 'name' => 'Mode', 'visible' => false, 'save' => true),
						array('caption' => 'Normalized type', 'name' => 'NormalizedType', 'visible' => false, 'save' => true),
						array('caption' => $this->Translate('Variable ident'), 'name' => 'Ident', 'width' => '200px', 'visible' => false, 'save' => true),
						array('caption' => $this->Translate('Create variable'), 'name' => 'Enabled', 'width' => '140px', 'edit' => array('type' => 'CheckBox'), 'save' => true)
					),
					'values' => $eventList					
				);
				$this->SendDebug('UNIFIPEV', 'Device Event List: ' . json_encode($eventList), 0);
			} else {
				$arrayElements[] = array('type' => 'Label', 'label' => $this->Translate('No compatible devices found or API connection failed.'));
			}

		

			$arrayActions = array();
			unset($arrayOptions);			
			return JSON_encode( array( 'status' => $arrayStatus, 'elements' => $arrayElements, 'actions' => $arrayActions ) );
	    }


		private function getEventConfiguratorValues(): array
		{
			$values = array_merge(
				$this->getStandardEventConfiguratorValues(),
				$this->getSmartDetectConfiguratorValues()
			);
			usort($values, static function (array $a, array $b): int {
				$nameCompare = strcasecmp((string)($a['CameraName'] ?? ''), (string)($b['CameraName'] ?? ''));
				if ($nameCompare !== 0) {
					return $nameCompare;
				}
				return strcasecmp((string)($a['DetectType'] ?? ''), (string)($b['DetectType'] ?? ''));
			});
			return $values;
		}

		private function getStandardEventConfiguratorValues(): array
		{
			$selectionIndex = $this->getSelectionIndex();
			$rows = array();
			foreach ($this->getCameraList() as $camera) {
				$cameraID = (string)($camera['id'] ?? '');
				if ($cameraID === '') {
					continue;
				}
				$cameraName = (string)($camera['name'] ?? $cameraID);
				$rows[] = $this->buildEventSelectionRow($selectionIndex, $cameraName, $cameraID, 'camera', 'cameraMotionEvent');
				$rows[] = $this->buildEventSelectionRow($selectionIndex, $cameraName, $cameraID, 'camera', 'smartDetectLineEvent');
				$hasSpeaker = (bool)($camera['featureFlags']['hasSpeaker'] ?? $camera['hasSpeaker'] ?? false);
				$hasMic = (bool)($camera['featureFlags']['hasMic'] ?? $camera['hasMic'] ?? false);
				if ($hasSpeaker && $hasMic) {
					$rows[] = $this->buildEventSelectionRow($selectionIndex, $cameraName, $cameraID, 'camera', 'ringEvent');
				}
			}
			foreach ($this->getLights() as $light) {
				$lightID = (string)($light['id'] ?? '');
				if ($lightID === '') {
					continue;
				}
				$lightName = (string)($light['name'] ?? $lightID);
				$rows[] = $this->buildEventSelectionRow($selectionIndex, $lightName, $lightID, 'light', 'lightMotionEvent');
			}
			$sensorEvents = ['sensorExtremeValueEvent', 'sensorWaterLeakEvent', 'sensorTamperEvent', 'sensorBatteryLowEvent', 'sensorAlarmEvent', 'sensorOpenEvent', 'sensorClosedEvent', 'sensorSmokeTestEvent', 'sensorMotionEvent'];
			foreach ($this->getSensors() as $sensor) {
				$sensorID = (string)($sensor['id'] ?? '');
				if ($sensorID === '') {
					continue;
				}
				$sensorName = (string)($sensor['name'] ?? $sensorID);
				foreach ($sensorEvents as $eventType) {
					$rows[] = $this->buildEventSelectionRow($selectionIndex, $sensorName, $sensorID, 'sensor', $eventType);
				}
			}
			return array_values(array_filter($rows));
		}

		private function buildEventSelectionRow(array $selectionIndex, string $deviceName, string $deviceID, string $category, string $eventType): array
		{
			$ident = $this->buildEventVariableIdent($deviceID, $eventType);
			return array(
				'CameraName' => $deviceName,
				'CameraID' => $deviceID,
				'Category' => $category,
				'DetectType' => $eventType,
				'NormalizedType' => $this->normalizeEventType($eventType),
				'Mode' => 'event',
				'Ident' => $ident,
				'Enabled' => isset($selectionIndex[$ident]) ? (bool)($selectionIndex[$ident]['Enabled'] ?? false) : false
			);
		}

		private function getSmartDetectConfiguratorValues(): array
		{
			$storedSelections = $this->getSelectionIndex();
			$cameras = $this->getCameraList();
			$values = array();
			foreach ($cameras as $camera) {
				$cameraID = (string)($camera['id'] ?? '');
				if ($cameraID === '') {
					continue;
				}
				$cameraName = (string)($camera['name'] ?? $cameraID);
				$smartSettings = $camera['smartDetectSettings'] ?? array();
				$objectTypes = $this->normalizeSmartDetectTypes($smartSettings['objectTypes'] ?? array());
				$audioTypes = $this->normalizeSmartDetectTypes($smartSettings['audioTypes'] ?? array());
				foreach ($objectTypes as $type) {
					$ident = $this->buildSmartDetectTypeIdent($cameraID, 'object', $type);
					$values[] = array(
						'CameraName' => $cameraName,
						'CameraID' => $cameraID,
						'Category' => 'smart-object',
						'DetectType' => $type,
						'NormalizedType' => strtolower($type),
						'Mode' => 'smart',
						'Ident' => $ident,
						'Enabled' => isset($storedSelections[$ident]) ? (bool)($storedSelections[$ident]['Enabled'] ?? false) : false
					);
				}
				foreach ($audioTypes as $type) {
					$ident = $this->buildSmartDetectTypeIdent($cameraID, 'audio', $type);
					$values[] = array(
						'CameraName' => $cameraName,
						'CameraID' => $cameraID,
						'Category' => 'smart-audio',
						'DetectType' => $type,
						'NormalizedType' => strtolower($type),
						'Mode' => 'smart',
						'Ident' => $ident,
						'Enabled' => isset($storedSelections[$ident]) ? (bool)($storedSelections[$ident]['Enabled'] ?? false) : false
					);
				}
			}
			return $values;
		}

		private function getSelectionIndex(): array
		{
			$stored = json_decode($this->ReadPropertyString('SmartDetectSelections'), true);
			if (!is_array($stored)) {
				return array();
			}
			$indexed = array();
			foreach ($stored as $row) {
				if (!is_array($row) || !isset($row['Ident'])) {
					continue;
				}
				$indexed[(string)$row['Ident']] = $row;
			}
			return $indexed;
		}

		private function getCameraList(): array
		{
			$response = $this->getApiData('/cameras');
			if (isset($response['cameras']) && is_array($response['cameras'])) {
				$response = $response['cameras'];
			}
			if (!is_array($response)) {
				return array();
			}
			return array_values(array_filter($response, static function ($camera): bool {
				return is_array($camera) && isset($camera['id']);
			}));
		}

		private function getLights(): array
		{
			$response = $this->getApiData('/lights');
			if (isset($response['lights']) && is_array($response['lights'])) {
				$response = $response['lights'];
			}
			if (!is_array($response)) {
				return array();
			}
			return array_values(array_filter($response, static function ($light): bool {
				return is_array($light) && isset($light['id']);
			}));
		}

		private function getSensors(): array
		{
			$response = $this->getApiData('/sensors');
			if (isset($response['sensors']) && is_array($response['sensors'])) {
				$response = $response['sensors'];
			}
			if (!is_array($response)) {
				return array();
			}
			return array_values(array_filter($response, static function ($sensor): bool {
				return is_array($sensor) && isset($sensor['id']);
			}));
		}

		private function normalizeSmartDetectTypes(array $types): array
		{
			$normalized = array();
			foreach ($types as $type) {
				if (!is_string($type) || $type === '') {
					continue;
				}
				$normalized[] = $type;
			}
			return array_values(array_unique($normalized));
		}

		private function buildSmartDetectTypeIdent(string $cameraID, string $category, string $type): string
		{
			$slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $type));
			return 'SmartDetect_' . $category . '_' . $cameraID . '_' . $slug;
		}

		private function buildEventVariableIdent(string $deviceID, string $eventType): string
		{
			$slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $eventType));
			return 'EventVar_' . $deviceID . '_' . $slug;
		}

		private function synchronizeConfiguredVariables(): void
		{
			$rows = json_decode($this->ReadPropertyString('SmartDetectSelections'), true);
			if (!is_array($rows)) {
				$rows = array();
			}
			$validIdents = array();
			foreach ($rows as $row) {
				if (!is_array($row)) {
					continue;
				}
				$ident = (string)($row['Ident'] ?? '');
				if ($ident === '') {
					continue;
				}
				$enabled = (bool)($row['Enabled'] ?? false);
				if (!$enabled) {
					$this->MaintainVariable($ident, '', 0, '', 0, 0);
					continue;
				}
				$validIdents[] = $ident;
				$deviceName = (string)($row['CameraName'] ?? ($row['CameraID'] ?? ''));
				$detectType = (string)($row['DetectType'] ?? '');
				if ($deviceName === '' || $detectType === '') {
					continue;
				}
				$mode = (string)($row['Mode'] ?? '');
				if ($mode === '' && strpos($ident, 'SmartDetect_') === 0) {
					$mode = 'smart';
				} elseif ($mode === '') {
					$mode = 'event';
				}
				$label = $deviceName . ' - ';
				if ($mode === 'event') {
					$label .= $this->formatEventTypeCaption($detectType) . ' ' . $this->Translate('active');
				} else {
					$label .= $detectType . ' ' . $this->Translate('active');
				}
				$this->MaintainVariable($ident, $label, 0, $this->buildSmartDetectBooleanPresentation(), 0, 1);
				$existingId = @$this->GetIDForIdent($ident);
				if ($existingId === false) {
					$this->SetValue($ident, false);
				}
			}
			$this->cleanupObsoleteConfiguredVariables($validIdents);
		}

		private function formatEventTypeCaption(string $eventType): string
		{
			if ($eventType === '') {
				return '';
			}
			$spaced = preg_replace('/([a-z])([A-Z])/', '$1 $2', $eventType);
			$spaced = preg_replace('/\s+event$/i', '', $spaced ?? '');
			return ucwords(trim((string)$spaced));
		}

		private function cleanupObsoleteConfiguredVariables(array $validIdents): void
		{
			$validLookup = array_flip($validIdents);
			foreach (IPS_GetChildrenIDs($this->InstanceID) as $childID) {
				$object = IPS_GetObject($childID);
				if (($object['ObjectType'] ?? 0) !== 2) {
					continue;
				}
				$ident = (string)($object['ObjectIdent'] ?? '');
				if (strpos($ident, 'SmartDetect_') !== 0 && strpos($ident, 'EventVar_') !== 0) {
					continue;
				}
				if (!isset($validLookup[$ident])) {
					$this->MaintainVariable($ident, '', 0, '', 0, 0);
				}
			}
		}

		private function buildSmartDetectBooleanPresentation(): array
		{
			return array(
				'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
				'USAGE_TYPE' => 0,
				'ICON' => 'sensor',
				'OPTIONS' => json_encode(array(
					array('ColorDisplay' => 16077123, 'Value' => false, 'Caption' => $this->Translate('no motion'), 'IconValue' => 'sensor', 'IconActive' => true, 'ColorActive' => true, 'ColorValue' => 16077123, 'Color' => -1),
					array('ColorDisplay' => 1692672, 'Value' => true, 'Caption' => $this->Translate('motion detected'), 'IconValue' => 'sensor-on', 'IconActive' => true, 'ColorActive' => true, 'ColorValue' => 1692672, 'Color' => -1)
				), JSON_UNESCAPED_SLASHES)
			);
		}

		private function updateSmartDetectSelectionStates(string $deviceID, array $activeEvents): void
		{
			$selectionMap = $this->getEnabledSmartDetectSelectionMap();
			if (!isset($selectionMap[$deviceID])) {
				return;
			}
			$activeTypes = $this->extractActiveSmartDetectTypes($activeEvents, $deviceID);
			foreach ($selectionMap[$deviceID] as $type => $ident) {
				if (@$this->GetIDForIdent($ident) === false) {
					continue;
				}
				$this->SetValue($ident, in_array($type, $activeTypes, true));
			}
		}

		private function updateStandardEventSelectionStates(string $deviceID, array $activeEvents): void
		{
			$selectionMap = $this->getEnabledStandardEventSelectionMap();
			if (!isset($selectionMap[$deviceID])) {
				return;
			}
			$activeTypes = $this->extractActiveStandardEventTypes($activeEvents, $deviceID);
			foreach ($selectionMap[$deviceID] as $type => $ident) {
				if (@$this->GetIDForIdent($ident) === false) {
					continue;
				}
				$this->SetValue($ident, in_array($type, $activeTypes, true));
			}
		}

		private function getEnabledSmartDetectSelectionMap(): array
		{
			$rows = json_decode($this->ReadPropertyString('SmartDetectSelections'), true);
			if (!is_array($rows)) {
				return array();
			}
			$map = array();
			foreach ($rows as $row) {
				if (!is_array($row)) {
					continue;
				}
				if (!(bool)($row['Enabled'] ?? false)) {
					continue;
				}
				$ident = (string)($row['Ident'] ?? '');
				$mode = (string)($row['Mode'] ?? '');
				if ($ident === '') {
					continue;
				}
				if ($mode === 'event' || ($mode === '' && strpos($ident, 'SmartDetect_') !== 0)) {
					continue;
				}
				$cameraID = (string)($row['CameraID'] ?? '');
				$detectType = strtolower((string)($row['DetectType'] ?? ''));
				if ($cameraID === '' || $detectType === '') {
					continue;
				}
				if (!isset($map[$cameraID])) {
					$map[$cameraID] = array();
				}
				$map[$cameraID][$detectType] = $ident;
			}
			return $map;
		}

		private function getEnabledStandardEventSelectionMap(): array
		{
			$rows = json_decode($this->ReadPropertyString('SmartDetectSelections'), true);
			if (!is_array($rows)) {
				return array();
			}
			$map = array();
			foreach ($rows as $row) {
				if (!is_array($row)) {
					continue;
				}
				if (!(bool)($row['Enabled'] ?? false)) {
					continue;
				}
				$ident = (string)($row['Ident'] ?? '');
				$mode = (string)($row['Mode'] ?? '');
				if ($ident === '') {
					continue;
				}
				if ($mode !== 'event' && strpos($ident, 'EventVar_') !== 0) {
					continue;
				}
				$deviceID = (string)($row['CameraID'] ?? '');
				$normalizedType = (string)($row['NormalizedType'] ?? '');
				if ($normalizedType === '') {
					$normalizedType = $this->normalizeEventType((string)($row['DetectType'] ?? ''));
				}
				if ($deviceID === '' || $normalizedType === '') {
					continue;
				}
				if (!isset($map[$deviceID])) {
					$map[$deviceID] = array();
				}
				$map[$deviceID][$normalizedType] = $ident;
			}
			return $map;
		}

		private function extractActiveSmartDetectTypes(array $activeEvents, string $deviceID): array
		{
			$activeTypes = array();
			foreach ($activeEvents as $storedEvent) {
				if (!is_array($storedEvent)) {
					continue;
				}
				foreach ($storedEvent as $details) {
					if (!is_array($details)) {
						continue;
					}
					if (($details['deviceID'] ?? '') !== $deviceID) {
						continue;
					}
					$types = $details['smartDetectTypes'] ?? array();
					if (is_string($types)) {
						$types = array($types);
					}
					if (!is_array($types)) {
						continue;
					}
					foreach ($types as $type) {
						if (!is_string($type) || $type === '') {
							continue;
						}
						$activeTypes[] = strtolower($type);
					}
				}
			}
			return array_values(array_unique($activeTypes));
		}

		private function extractActiveStandardEventTypes(array $activeEvents, string $deviceID): array
		{
			$activeTypes = array();
			foreach ($activeEvents as $storedEvent) {
				if (!is_array($storedEvent)) {
					continue;
				}
				foreach ($storedEvent as $details) {
					if (!is_array($details)) {
						continue;
					}
					if (($details['deviceID'] ?? '') !== $deviceID) {
						continue;
					}
					$type = (string)($details['type'] ?? '');
					if ($type === '') {
						continue;
					}
					$activeTypes[] = $type;
				}
			}
			return array_values(array_unique($activeTypes));
		}

		private function normalizeEventType(string $type): string
		{
			$lower = strtolower($type);
			$map = array(
				'ringevent' => 'ring',
				'ring' => 'ring',
				'smartdetectlineevent' => 'smartDetectLine',
				'smartdetectline' => 'smartDetectLine',
				'sensorextremevalueevent' => 'sensorExtremeValues',
				'sensorextremevalues' => 'sensorExtremeValues',
				'sensorwaterleakevent' => 'sensorWaterLeak',
				'sensorwaterleak' => 'sensorWaterLeak',
				'sensortamperevent' => 'sensorTamper',
				'sensortamper' => 'sensorTamper',
				'sensorbatterylowevent' => 'sensorBatteryLow',
				'sensorbatterylow' => 'sensorBatteryLow',
				'sensoralarmevent' => 'sensorAlarm',
				'sensoralarm' => 'sensorAlarm',
				'sensoropenevent' => 'sensorOpened',
				'sensoropen' => 'sensorOpened',
				'sensorclosedevent' => 'sensorClosed',
				'sensorclosed' => 'sensorClosed',
				'sensorsmoketestevent' => 'sensorSmokeTest',
				'sensorsmoketest' => 'sensorSmokeTest',
				'sensormotionevent' => 'sensorMotion',
				'sensormotion' => 'sensorMotion',
				'lightmotionevent' => 'lightMotion',
				'lightmotion' => 'lightMotion',
				'cameramotionevent' => 'motion',
				'motionevent' => 'motion'
			);
			return $map[$lower] ?? $type;
		}

	}


	