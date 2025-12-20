<?php

declare(strict_types=1);
	class UnifiProtectGateway extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			$this->RegisterPropertyString( 'ServerAddress', '192.168.178.1' );
			$this->RegisterPropertyString( 'APIKey', '' );
			$this->RegisterPropertyBoolean('applicationVersion', 0);
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
			$vpos = 100;
			$this->MaintainVariable( 'applicationVersion', $this->Translate( 'Application Version' ), 3, '', $vpos++, $this->ReadPropertyBoolean("applicationVersion") );
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

		public function ForwardData($JSONString)
		{
			$data = json_decode( $JSONString );
			#Message('UnifiPGW', $JSONString);
			$APIKey = $this->ReadPropertyString( 'APIKey' );
			if (empty($APIKey))
			{
				// instance inactive
				$this->SetStatus( 104 );
			} else {
				// instance active
				$this->SetStatus( 102 );
			}

			if (isset($data->Api)) {
				switch ($data->Api) {
					case 'setAlarmManager':
						$this->setAlarmManager($data->Param1);
						return serialize([]);
					case "getDevices":
						$array = $this->getDevices($data->Param1);
						return serialize($array);						
					case "getStreams":
						$this->SendDebug("UnifiPGW", "Get Streams for CameraID: " . $data->ID, 0);
						$array = $this->getStreams($data->ID);
						return serialize($array);
					case "getSnapshot":
						$ServerAddress = $this->ReadPropertyString( 'ServerAddress' );
						$APIKey = $this->ReadPropertyString( 'APIKey' );
						$cameraID = $data->ID;
						$url = 'https://'.$ServerAddress.'/proxy/protect/integration/v1/cameras/' . $cameraID . '/snapshot?forceHighQuality=true';
						$array = array('apikey' => $APIKey, 'url' => $url);
						$this->SendDebug("UnifiPGW", "Snapshot: " . json_encode($array), 0);
						return serialize($array);
					case "createStream":
						$this->SendDebug("UnifiPGW", "Create Streams for CameraID: " . $data->ID, 0);
						$stream = $this->createStream($data->ID, $data->Param1);
						return serialize($stream);
					case "getDeviceData":
						$deviceData = $this->getDeviceData($data->ID, $data->Param1);
						return serialize($deviceData);
					case "patchSettingCamera":
						$setting = $this->patchSettingCamera($data->ID, $data->Param1);
						return serialize($setting);
					case "patchSettingSensor":
						$setting = $this->patchSettingSensor($data->ID, $data->Param1);
						return serialize($setting);
					case "getDevicesConfig":
						$config = $this->getDevicesConfig();
						return serialize($config);
					default:
						$this->SendDebug("UnifiPGW", "Unknown API: " . $data->Api, 0);
						break;
				}
			}
		}

		public function Send( int $id,string $Api, string $Text )
		{
			$this->SendDataToChildren( json_encode( [ 'DataID' => '{C7147748-F01B-E4F9-D11E-72DFA08E7048}',
					'id' =>  $id,
					'Api'=> $Api,
					'data'=> $Text ]));
		}



		#https://192.168.178.1/proxy/protect/v1/cameras

		public function getApiData(string $endpoint = ''): array {
			$maxRetries = 3;
			$retry = 0;
			do {
				if (!IPS_SemaphoreEnter("UnifiProtectAPI", 50)) {
					$this->SendDebug("UnifiPGW", "Semaphore Timeout - Request abgebrochen", 0);
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

					$this->SendDebug("UnifiPGW", "API Endpoint: " . $RawData, 0);

					if ($RawData === false) {
						$this->SendDebug("UnifiPGW", "Curl error: " . $curl_error, 0);
						$this->SetStatus(201);
						return [];
					}

					if ($httpCode === 429) {
						$retryAfterHeader = $responseHeaders['retry-after'] ?? '';
						$retryAfter = is_numeric($retryAfterHeader) ? max(0.5, (float)$retryAfterHeader) : 0.5;
						$this->SendDebug("UnifiPGW", "Rate Limit erreicht, warte " . $retryAfter . "s", 0);
						$retry++;
						usleep((int)($retryAfter * 1_000_000));
						continue;
					}

					$JSONData = json_decode($RawData, true);
					if (isset($JSONData['statusCode']) && $JSONData['statusCode'] !== 200) {
						$this->SendDebug("UnifiPGW", "Curl error: " . $JSONData['statusCode'], 0);
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

		public function deleteApiData( string $endpoint = '' ):array {
			if (!IPS_SemaphoreEnter("UnifiProtectAPI", 500)) {
				$this->SendDebug("UnifiPGW", "Semaphore Timeout - Request abgebrochen", 0);
				return [];
			}

			try {
				$starttime=microtime(true);
				$ServerAddress = $this->ReadPropertyString( 'ServerAddress' );
				$APIKey = $this->ReadPropertyString( 'APIKey' );

				$ch = curl_init();
				curl_setopt( $ch, CURLOPT_URL, 'https://'.$ServerAddress.'/proxy/protect/integration/v1'.$endpoint );
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
				curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'X-API-KEY:'.$APIKey ) );
				curl_setopt( $ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1' );
				$RawData = curl_exec($ch);
				$curl_error = curl_error($ch);  
				curl_close( $ch );
				$this->SendDebug("UnifiPGW", "API Endpoint: " .$RawData, 0);
				if ($RawData === false) {
					// Handle error
					$this->SendDebug("UnifiPGW", "Curl error: " . $curl_error, 0);
					$this->SetStatus( 201 ); // Set status to error
					return [];
				}
				$JSONData = json_decode( $RawData, true );
				if ( isset( $JSONData[ 'statusCode' ] ) ) {
					if ($JSONData[ 'statusCode' ]<> 200) {
						// instance inactive
						$this->SendDebug("UnifiPGW", "Curl error: " . $JSONData[ 'statusCode' ], 0);
						$this->SetStatus( $JSONData[ 'statusCode' ] );
					}
				}						
				$elapsed = microtime(true) - $starttime;
				if($elapsed < 0.05) {
					usleep((int)((0.05 - $elapsed) * 1000000));
				}
				return [];
			} finally {
				// Semaphore freigeben
				IPS_SemaphoreLeave("UnifiProtectAPI");
			}
		}

		public function patchApiData( string $endpoint = '', string $PostData = '' ):array {
			if (!IPS_SemaphoreEnter("UnifiProtectAPI", 500)) {
				$this->SendDebug("UnifiPGW", "Semaphore Timeout - Request abgebrochen", 0);
				return [];
			}

			try {
				$starttime=microtime(true);
				$ServerAddress = $this->ReadPropertyString( 'ServerAddress' );
				$APIKey = $this->ReadPropertyString( 'APIKey' );

				$ch = curl_init();
				curl_setopt( $ch, CURLOPT_URL, 'https://'.$ServerAddress.'/proxy/protect/integration/v1'.$endpoint );
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $PostData );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
				curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'X-API-KEY:'.$APIKey ) );
				curl_setopt( $ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1' );
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);  
				$RawData = curl_exec($ch);
				$curl_error = curl_error($ch); // ✅ VOR curl_close() speichern
        		curl_close( $ch );
				$this->SendDebug("UnifiPGW", "API Endpoint: " .$RawData, 0);
				if ($RawData === false) {
					// Handle error
					$this->SendDebug("UnifiPGW", "Curl error: " . $curl_error, 0);
					$this->SetStatus( 201 ); // Set status to error
					return [];
				}
				$JSONData = json_decode( $RawData, true );
				if ( isset( $JSONData[ 'statusCode' ] ) ) {
					if ($JSONData[ 'statusCode' ]<> 200) {
						// instance inactive
						$this->SendDebug("UnifiPGW", "Curl error: " . $JSONData[ 'statusCode' ], 0);
						$this->SetStatus( $JSONData[ 'statusCode' ] );
					}
				}
				$elapsed = microtime(true) - $starttime;
				if($elapsed < 0.05) {
					usleep((int)((0.05 - $elapsed) * 1000000));
				}
				return [];
			} finally {
				// Semaphore freigeben
				IPS_SemaphoreLeave("UnifiProtectAPI");
			}
		}

		public function getApiDataPost( string $endpoint = '', string $PostData = '' ):array {
			if (!IPS_SemaphoreEnter("UnifiProtectAPI", 500)) {
				$this->SendDebug("UnifiPGW", "Semaphore Timeout - Request abgebrochen", 0);
				return [];
			}

			try {
				$starttime=microtime(true);	
				$ServerAddress = $this->ReadPropertyString( 'ServerAddress' );
				$APIKey = $this->ReadPropertyString( 'APIKey' );
				$this->SendDebug("UnifiPGW", "PostData: " . $PostData, 0);
				$ch = curl_init();
				curl_setopt( $ch, CURLOPT_URL, 'https://'.$ServerAddress.'/proxy/protect/integration/v1'.$endpoint );				
				curl_setopt( $ch, CURLOPT_POST, true );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $PostData );			
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
				curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'X-API-KEY:'.$APIKey ) );
				curl_setopt( $ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1' );
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);  
				$RawData = curl_exec($ch);
				$curl_error = curl_error($ch); // ✅ VOR curl_close() speichern
        		curl_close( $ch );
				$this->SendDebug("UnifiPGW", "API Endpoint: " .$RawData, 0);
				if ($RawData === false) {
					// Handle error
					$this->SendDebug("UnifiPGW", "Curl error: " . $curl_error, 0);
					$this->SetStatus( 201 ); // Set status to error
					return [];
				}
				$JSONData = json_decode( $RawData, true );
				if ( isset( $JSONData[ 'statusCode' ] ) ) {
					if ($JSONData[ 'statusCode' ]<> 200) {
						// instance inactive
						$this->SendDebug("UnifiPGW", "Curl error: " . $JSONData[ 'statusCode' ], 0);
						$this->SetStatus( $JSONData[ 'statusCode' ] );
					}
				}
				$elapsed = microtime(true) - $starttime;
				if($elapsed < 0.05) {
					usleep((int)((0.05 - $elapsed) * 1000000));
				}
				if ( $JSONData === null ) {
					$this->SendDebug("UnifiPGW", "JSON Decode error: " . json_last_error_msg(), 0);
					return [];
				}
				return $JSONData;
			} finally {
				// Semaphore freigeben
				IPS_SemaphoreLeave("UnifiProtectAPI");
			}
		}

		public function getProtectVersion ():string {
			$version=$this->getApiData( '/meta/info' );
			$this->SendDebug("UnifiPGW", "Protect Version: " . ($version['applicationVersion'] ?? ''), 0);
			return $version['applicationVersion'] ?? '';
		}



		public function getDevices(string $deviceType):array{
			if (empty($deviceType)) {
				$this->SendDebug("UnifiPGW", "Device type is empty, returning empty array.", 0);
				return [];
			}
			if ($deviceType === 'Camera') {
				$JSONData = $this->getCameras();
			} elseif ($deviceType === 'UP-Sense') {
				$JSONData = $this->getSensors();
			} elseif ($deviceType === 'Chime') {
				$JSONData = $this->getChimes();
			} elseif ($deviceType === 'Lights') {
				$JSONData = $this->getLights();
			} else {
				$this->SendDebug("UnifiPGW", "Unknown device type: " . $deviceType, 0);
				return [];
			}
			$this->SendDebug("UnifiPGW", "Devices: " . json_encode($JSONData), 0);
			return $JSONData;
		}


		public function getCameras():array {			
			$JSONData = $this->getApiData( '/cameras' );
			$value = [];
			if ( is_array( $JSONData ) && isset( $JSONData ) ) {
				if (isset($JSONData)) {
					$devices = $JSONData;
					usort( $devices, function ( $a, $b ) {
						return strcmp($a['name'], $b['name']);
					});

					foreach ( $devices as $device ) {
						$value[] = [
							'caption'=>$device[ 'name' ],
							'value'=> $device[ 'id' ]
						];
					}
				} else {
					$value[] = [
						'caption'=>$this->Translate('default'),
						'value'=> 'default'
					];
				}
				return $value;
			}
		}

		public function getSensors():array {
			$JSONData = $this->getApiData( '/sensors' );
			$value = [];
			if ( is_array( $JSONData ) && isset( $JSONData ) ) {
				if (isset($JSONData)) {
					$devices = $JSONData;
					usort( $devices, function ( $a, $b ) {
						return strcmp($a['name'], $b['name']);
					});

					foreach ( $devices as $device ) {
						$value[] = [
							'caption'=>$device[ 'name' ],
							'value'=> $device[ 'id' ]
						];
					}
				} else {
					$value[] = [
						'caption'=>'default',
						'value'=> 'default'
					];
				}
				return $value;
			}
		}

		public function getChimes():array {
			$JSONData = $this->getApiData( '/chimes' );
			$value = [];
			if ( is_array( $JSONData ) && isset( $JSONData ) ) {
				if (isset($JSONData)) {
					$devices = $JSONData;
					usort( $devices, function ( $a, $b ) {
						return strcmp($a['name'], $b['name']);
					});

					foreach ( $devices as $device ) {
						$value[] = [
							'caption'=>$device[ 'name' ],
							'value'=> $device[ 'id' ]
						];
					}
				} else {
					$value[] = [
						'caption'=>'default',
						'value'=> 'default'
					];
				}
				return $value;
			}
		}

		public function getLights():array {
			$JSONData = $this->getApiData( '/lights' );
			$value = [];
			if ( is_array( $JSONData ) && isset( $JSONData ) ) {
				if (isset($JSONData)) {
					$devices = $JSONData;
					usort( $devices, function ( $a, $b ) {
						return strcmp($a['name'], $b['name']);
					});

					foreach ( $devices as $device ) {
						$value[] = [
							'caption'=>$device[ 'name' ],
							'value'=> $device[ 'id' ]
						];
					}
				} else {
					$value[] = [
						'caption'=>'default',
						'value'=> 'default'
					];
				}
				return $value;
			}
		}

		public function createStream(string $cameraID, string $streamType):array {
			// Falls $streamType ein JSON-String ist, dekodieren
			if (is_string($streamType) && $this->isJson($streamType)) {
				$decoded = json_decode($streamType, true);
				if (isset($decoded['qualities']) && is_array($decoded['qualities'])) {
					$gewünschteStreams = $decoded['qualities'];
				} else {
					$gewünschteStreams = [];
				}
			} else {
				$gewünschteStreams = is_array($streamType) ? $streamType : [$streamType];
			}
			$gewünschteStreams = array_map('strval', array_map('trim', $gewünschteStreams));

			// Aktive Streams holen
			$aktiveStreams = $this->getStreams($cameraID);

			// Aktive Qualitäten extrahieren
			$aktiveQualities = [];
			foreach ($aktiveStreams as $quality => $url) {
				if (!is_null($url)) {
					$aktiveQualities[] = (string)$quality;
				}
			}

			$this->SendDebug("UnifiPGW", "Aktive Streams: " . json_encode($aktiveQualities), 0);
			$this->SendDebug("UnifiPGW", "Gewünschte Streams: " . json_encode($gewünschteStreams), 0);

			// Streams löschen, die aktiv sind und NICHT mehr gewünscht werden
			$zuLoeschen = [];
			foreach ($aktiveQualities as $quality) {
				if (!in_array($quality, $gewünschteStreams, true)) {
					$zuLoeschen[] = $quality;
				}
			}
			if (!empty($zuLoeschen)) {
				$query = http_build_query(['qualities' => $zuLoeschen]);
				$this->SendDebug("UnifiPGW", "Delete: " . $query, 0);
				$this->deleteApiData('/cameras/' . $cameraID . '/rtsps-stream?' . $query);
			}
			// Streams anlegen, die gewünscht sind und noch nicht aktiv sind
			$neueQualities = [];
			foreach ($gewünschteStreams as $quality) {
				if (!in_array($quality, $aktiveQualities, true)) {
					$neueQualities[] = $quality;
				}
			}
			if (!empty($neueQualities)) {
			$this->SendDebug("UnifiPGW", "Create: " . json_encode($neueQualities), 0);
			$this->getApiDataPost(
				'/cameras/' . $cameraID . '/rtsps-stream',
				json_encode(['qualities' => $neueQualities])
			);
		}

			// Rückgabe: neue Liste der aktiven Streams
			return $this->getStreams($cameraID);
		}

		// Hilfsfunktion zum Erkennen von JSON
		private function isJson($string) {
			json_decode($string);
			return (json_last_error() == JSON_ERROR_NONE);
		}
		public function setAlarmManager(string $webhookID):array {
			$JSONData = $this->getApiDataPost( '/alarm-manager/webhook/' . $webhookID );
			$this->SendDebug("UnifiPGW", "setAlarmManager: " . json_encode($JSONData), 0);
			return $JSONData;
		}

		public function getStreams(string $cameraID):array {
			$JSONData = $this->getApiData( '/cameras/' . $cameraID . '/rtsps-stream' );
			$this->SendDebug("UnifiPGW", "getStreams: " . json_encode($JSONData), 0);
			return $JSONData;
		}

		public function patchSettingCamera(string $cameraID, string $setting):array {
			$JSONData = $this->patchApiData( '/cameras/' . $cameraID, $setting);
			return $JSONData;
		}

		public function patchSettingSensor(string $sensorID, string $setting):array {
			$JSONData = $this->patchApiData( '/sensors/' . $sensorID, $setting);
			return $JSONData;
		}
		public function getDeviceData(string $deviceID,string $deviceType):array {
			if (empty($deviceID)) {
				$this->SendDebug("UnifiPGW", "Device ID is empty, returning empty array.", 0);
				return [];
			}
			if ($deviceType === 'Camera') {
				$JSONData = $this->getApiData( '/cameras/' . $deviceID );
			} elseif ($deviceType === 'UP-Sense') {
				$JSONData = $this->getApiData( '/sensors/' . $deviceID );
			} elseif ($deviceType === 'Light') {
				$JSONData = $this->getApiData( '/lights/' . $deviceID );
			} elseif ($deviceType === 'Chime') {
				$JSONData = $this->getApiData( '/chimes/' . $deviceID );
			} else {
				$this->SendDebug("UnifiPGW", "Unknown device type: " . $deviceType, 0);
				return [];
			}			
			return $JSONData;
		}

		public function GetConfigurationForm() {
			$ServerAddress = $this->ReadPropertyString( 'ServerAddress' );
			$APIKey = $this->ReadPropertyString( 'APIKey' );

			$arrayStatus = array();
			$arrayStatus[] = array( 'code' => 102, 'icon' => 'active', 'caption' => $this->Translate('Instance is active') );
			$arrayStatus[] = array( 'code' => 201, 'icon' => 'inactive', 'caption' => $this->Translate('Instance is faulty: Data query error') );
			$arrayStatus[] = array( 'code' => 400, 'icon' => 'inactive', 'caption' => $this->Translate('Instance is faulty: Bad Request' ));
			$arrayStatus[] = array( 'code' => 401, 'icon' => 'inactive', 'caption' => $this->Translate('Instance is faulty: Unauthorized' ));
			$arrayStatus[] = array( 'code' => 403, 'icon' => 'inactive', 'caption' => $this->Translate('Instance is faulty: Forbidden') );
			$arrayStatus[] = array( 'code' => 404, 'icon' => 'inactive', 'caption' => $this->Translate('Instance is faulty: Not Found') );
			$arrayStatus[] = array( 'code' => 429, 'icon' => 'inactive', 'caption' => $this->Translate('Instance is faulty: Rate Limit') );
			$arrayStatus[] = array( 'code' => 500, 'icon' => 'inactive', 'caption' => $this->Translate('Instance is faulty: Server Error') );
			$arrayStatus[] = array( 'code' => 502, 'icon' => 'inactive', 'caption' => $this->Translate('Instance is faulty: Bad Gateway') );

			$arraySort = array();
			#$arraySort = array( 'column' => 'DeviceName', 'direction' => 'ascending' );

			$arrayElements = array();
			$arrayElements[] = array( 'type' => 'Label','bold' => true, 'label' => $this->Translate('UniFi Protect Gateway'));
			$arrayElements[] = array( 'type' => 'Label', 'label' => $this->Translate('Please create API Key under "UniFi Network > Settings > Control Plane > Integrations"'));
			$arrayElements[] = array( 'type' => 'ValidationTextBox', 'name' => 'ServerAddress', 'caption' => $this->Translate('Unifi Device IP'), 'validate' => "^(([a-zA-Z0-9\\.\\-\\_]+(\\.[a-zA-Z]{2,3})+)|(\\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\b))$" );
			$arrayElements[] = array( 'type' => 'ValidationTextBox', 'name' => 'APIKey', 'caption' => $this->Translate('APIKey') );
			$arrayElements[] = array( 'type' => 'CheckBox', 'name' => 'applicationVersion', 'caption' => $this->Translate('Show Application Version') );
			if ( !empty( $APIKey && $this->GetStatus() === 102)) {
				if ($this->ReadPropertyBoolean("applicationVersion")) {
					$this->SetValue('applicationVersion', $this->getProtectVersion());
				}
				$arrayElements[] = array( 'type' => 'Label', 'label' => $this->Translate('Protect Application Version: ').$this->getProtectVersion() );           
			} else {
				$arrayElements[] = array( 'type' => 'Label', 'label' => $this->Translate('Protect Application Version: not found') );
			}
		
			#$arrayElements[] = array( 'type' => 'Select', 'name' => 'Site', 'caption' => 'Site', 'options' => $arrayOptions );

			$arrayActions = array();

			return JSON_encode( array( 'status' => $arrayStatus, 'elements' => $arrayElements, 'actions' => $arrayActions ) );

    	}

		public function getDevicesConfig():array {
			$value = [];
        	$cameras = $this->getApiData( '/cameras/' );
			$this->SendDebug("UnifiPGW", "getDevicesConfig: " . json_encode($cameras), 0);
			if ( is_array( $cameras ) && isset( $cameras ) )
			{
				$this->SendDebug("UnifiPGW", json_encode($cameras), 0);
				usort( $cameras, function ( $a, $b ) {
					return $a[ 'name' ]>$b[ 'name' ];
					});
				foreach ( $cameras as $camera ) {                         
					$addValue = array(
						'Name'	=>$camera[ 'name' ],
						'Type'	=>$camera[ 'modelKey' ],
						'State' =>$camera['state'],
						'ID'		=>isset( $camera[ 'id' ] ) ? $camera[ 'id' ] : $this->Translate('missing') ,                    
						'instanceID'	=>$this->getInstanceIDForGuid( isset( $camera[ 'id' ] ) ? $camera[ 'id' ] : '', '{F78D1159-D735-D23A-0A97-69F07962BB89}' )
					);
					if (isset($camera['id']) && !empty($camera['id'])) {
						$streams=$this->getStreams($camera['id']);
						$this->SendDebug("UnifiPGW", "Streams: " . json_encode($streams), 0);
						$addValue['create'] = array(
							'moduleID'      => '{F78D1159-D735-D23A-0A97-69F07962BB89}',
							'configuration' => [
								'ID'	=> isset( $camera[ 'id' ] ) ? $camera[ 'id' ] : '',
								'DeviceType' => 'Camera'								
							],
							'name' => $camera[ 'name' ]
						);
							if (isset($streams['high'])) {
								$urlStream = $streams['high'];
								if (isset($urlStream)) {
									$addValue['create']['configuration']['StreamHigh'] = True;
								}
							}
							if (isset($streams['medium'])) {
								$urlStream = $streams['medium'];
								if (isset($urlStream)) {
									$addValue['create']['configuration']['StreamMedium'] = True;
								}
							}
							if (isset($streams['low'])) {
								$urlStream = $streams['low'];
								if (isset($urlStream)) {
									$addValue['create']['configuration']['StreamLow'] = True;
								}
							}
					}
					$value[] = $addValue;
				}
			}
            $sensors = $this->getApiData( '/sensors/' );
            if ( is_array( $sensors ) && isset( $sensors ) ) {
                usort( $sensors, function ( $a, $b ) {
                return $a[ 'name' ]>$b[ 'name' ];
                });
                foreach ( $sensors as $sensor )
                {
                   $addValue = array(
                        'Name'	=>$sensor[ 'name' ],
                        'Type'	=>$sensor[ 'modelKey' ],
						'State' =>$sensor['state'],
                        'ID'		=>isset( $sensor[ 'id' ] ) ? $sensor[ 'id' ] : 'missing' ,                        
                        'instanceID'	=>$this->getInstanceIDForGuid( $sensor[ 'id' ], '{F78D1159-D735-D23A-0A97-69F07962BB89}' )
                        );
                        if (isset($sensor['id']) && !empty($sensor['id'])) {
                            $addValue['create'] = array(
                            'moduleID'      => '{F78D1159-D735-D23A-0A97-69F07962BB89}',
                            'configuration' => [
                                'ID'	=> isset( $sensor[ 'id' ] ) ? $sensor[ 'id' ] : '',
								'DeviceType' => 'UP-Sense'
                            ],
                            'name' => $sensor[ 'name' ]
                            );
                        }
                    $value[] = $addValue;
                    }
                }
				#https://192.168.178.1/proxy/protect/v1/chimes
				$chimes = $this->getApiData( '/chimes/' );
				if ( is_array( $chimes ) && isset( $chimes ) ) {
					usort( $chimes, function ( $a, $b ) {
					return $a[ 'name' ]>$b[ 'name' ];
					});
					foreach ( $chimes as $chime )
					{
					$addValue = array(
							'Name'	=>$chime[ 'name' ],
							'Type'	=>$chime[ 'modelKey' ],
							'State' =>$chime['state'],
							'ID'		=>isset( $chime[ 'id' ] ) ? $chime[ 'id' ] : 'missing' ,                        
							'instanceID'	=>$this->getInstanceIDForGuid( $chime[ 'id' ], '{F78D1159-D735-D23A-0A97-69F07962BB89}' )
							);
							if (isset($chime['id']) && !empty($chime['id'])) {
								$addValue['create'] = array(
								'moduleID'      => '{F78D1159-D735-D23A-0A97-69F07962BB89}',
								'configuration' => [
									'ID'	=> isset( $chime[ 'id' ] ) ? $chime[ 'id' ] : '',
									'DeviceType' => 'Chime'
								],
								'name' => $chime[ 'name' ]
								);
							}
						$value[] = $addValue;
						}
					}
				#https://192.168.178.1/proxy/protect/v1/lights
				$lights = $this->getApiData( '/lights/' );
				if ( is_array( $lights ) && isset( $lights ) ) {
					usort( $lights, function ( $a, $b ) {
					return $a[ 'name' ]>$b[ 'name' ];
					});
					foreach ( $lights as $light )
					{
					$addValue = array(
							'Name'	=>$light[ 'name' ],
							'Type'	=>$light[ 'modelKey' ],
							'State' =>$light['state'],
							'ID'		=>isset( $light[ 'id' ] ) ? $light[ 'id' ] : 'missing' ,                        
							'instanceID'	=>$this->getInstanceIDForGuid( $light[ 'id' ], '{F78D1159-D735-D23A-0A97-69F07962BB89}' )
							);
							if (isset($light['id']) && !empty($light['id'])) {
								$addValue['create'] = array(
								'moduleID'      => '{F78D1159-D735-D23A-0A97-69F07962BB89}',
								'configuration' => [
									'ID'	=> isset( $light[ 'id' ] ) ? $light[ 'id' ] : '',
									'DeviceType' => 'Light'
								],
								'name' => $light[ 'name' ]
								);
							}
						$value[] = $addValue;
						}
					}
			$this->SendDebug("UnifiPGW", "getDevicesConfig: " . json_encode($value), 0);
            return $value;
    	}
	   private function getInstanceIDForGuid( $id, $guid )
		{
			$instanceIDs = IPS_GetInstanceListByModuleID( $guid );
			foreach ( $instanceIDs as $instanceID ) {
				if ( IPS_GetProperty( $instanceID, 'ID' ) == $id ) {
					$instance=IPS_GetInstance($instanceID);
					if ($instance['ConnectionID']==$this->InstanceID) {
						return $instanceID;
					}
				}
			}
			return 0;
		}

	}