<?php

declare(strict_types=1);
	class UnifiProtectGateway extends IPSModuleStrict
	{
		public function Create():void
		{
			//Never delete this line!
			parent::Create();
			$this->RegisterPropertyString( 'ServerAddress', '192.168.178.1' );
			$this->RegisterPropertyString( 'APIKey', '' );
			$this->RegisterPropertyBoolean('applicationVersion', 0);
			$this->RegisterPropertyInteger( 'VersionTimer', 0 );
			$this->RegisterPropertyString( 'localUser', '' );
			$this->RegisterPropertyString( 'localPassword', '' );

			// true = keine Migration noetig; bei 1.x-Bestandsinstanzen fehlt das Attribut -> Migrate() legt einmalig den WS Client an
			$this->RegisterAttributeBoolean( 'WsMigrationDone', true );
			$this->RegisterAttributeString( 'localToken', '' );

			$this->RegisterTimer( 'Update Version', 0, "UNIFIPGW_updateVersion(\$_IPS['TARGET']);" );
			$this->RegisterTimer( 'WS Watchdog', 0, "UNIFIPGW_watchdogWS(\$_IPS['TARGET']);" );
		}

		public function Migrate(string $JSONData): string
		{
			// Diese Zeile nicht entfernen
			parent::Migrate($JSONData);

			$data = json_decode($JSONData, true);
			$done = (bool)($data['attributes']['WsMigrationDone'] ?? false);
			// Nur konfigurierte Bestandsinstanzen aus einer Version ohne I/O migrieren -
			// bei Neuerstellungen laeuft der regulaere Erstellprozess (Benutzer-Hoheit)
			$isLegacyInstance = !empty($data['configuration']['APIKey'] ?? '');
			if ($done || !$isLegacyInstance) {
				// keine Aenderungen notwendig
				return '';
			}
			// Bestandsinstanz: WS Client einmalig anlegen und Migration als erledigt markieren
			$this->configureWsClient();
			$data['attributes']['WsMigrationDone'] = true;
			return json_encode($data);
		}

		// Nur der native WS Client ist als uebergeordnete Instanz zulaessig
		public function GetCompatibleParents(): string
		{
			return json_encode(['type' => 'require', 'moduleIDs' => ['{D68FD31F-0E90-7019-F16C-1949BD3079EF}']]);
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
			$vpos = 100;
			$this->MaintainVariable( 'applicationVersion', $this->Translate( 'Application Version' ), 3, '', $vpos++, $this->ReadPropertyBoolean("applicationVersion") );
			$APIKey = $this->ReadPropertyString( 'APIKey' );
			if (empty($APIKey))
			{
				// instance inactive
				$this->SetStatus( 104 );
				$this->SetTimerInterval( 'Update Version', 0 );
			} else {
				// instance active
				$this->SetStatus( 102 );
				$this->SetSummary($this->ReadPropertyString("ServerAddress"));
				// Timer: Protect-Version regelmaessig aktualisieren (nur wenn Anzeige aktiv)
				$VersionTimerMS = $this->ReadPropertyInteger( 'VersionTimer' ) * 1000;
				if ( $this->ReadPropertyBoolean("applicationVersion") ) {
					$this->SetTimerInterval( 'Update Version', $VersionTimerMS );
				} else {
					$this->SetTimerInterval( 'Update Version', 0 );
				}
			}
			$this->SetTimerInterval( 'WS Watchdog', empty($APIKey) ? 0 : 60000 );
		}

		// Stellt sicher, dass ein WS Client als Parent vorhanden ist (z.B. nach Update von aelterer Version).
		// Die Konfiguration selbst uebernimmt das Framework ueber GetConfigurationForParent().
		// Begruendete Abweichung von den Store-Richtlinien (Anlage/Konfiguration einer Instanz):
		// Hier wird ausschliesslich die eigene Datenflusskette vervollstaendigt (erlaubte Ausnahme),
		// und nur EINMALIG aus Migrate() heraus beim Update von Version <2.0, bei der das Gateway
		// selbst noch der I/O war - der Nutzer hat den Parent also nie selbst angelegt.
		// IPS_SetConfiguration/IPS_ApplyChanges betreffen nur diese frisch erzeugte Instanz.
		private function configureWsClient(): void
		{
			$parentID = IPS_GetInstance($this->InstanceID)['ConnectionID'];
			if ($parentID > 0) {
				return;
			}
			if (IPS_GetKernelRunlevel() !== KR_READY) {
				return;
			}
			$parentID = @IPS_CreateInstance('{D68FD31F-0E90-7019-F16C-1949BD3079EF}');
			if (!$parentID) {
				$this->SendDebug("UnifiPGW", "WS Client konnte nicht angelegt werden", 0);
				return;
			}
			IPS_SetName($parentID, 'UniFi Protect WebSocket');
			IPS_ConnectInstance($this->InstanceID, $parentID);
			// Einmalige Erstkonfiguration der frisch angelegten Instanz;
			// danach pflegt das Framework die Konfiguration ueber GetConfigurationForParent()
			IPS_SetConfiguration($parentID, $this->GetConfigurationForParent());
			IPS_ApplyChanges($parentID);
			$this->SendDebug("UnifiPGW", "WS Client angelegt: " . $parentID, 0);
		}

		// Liefert die Konfiguration fuer den vorgelagerten WS Client; wird vom Framework angewendet
		public function GetConfigurationForParent(): string
		{
			$ip = $this->ReadPropertyString('ServerAddress');
			$APIKey = $this->ReadPropertyString('APIKey');
			return json_encode([
				'URL'               => 'wss://' . $ip . '/proxy/protect/integration/v1/subscribe/devices',
				'VerifyCertificate' => false,
				'Type'              => 0,
				'Headers'           => json_encode([
					['Name' => 'X-API-KEY', 'Value' => $APIKey]
				]),
				'Active'            => !empty($ip) && !empty($APIKey)
			]);
		}

		// Ueberwacht die WebSocket-Verbindung: stoesst Reconnects an und nach
		// wiederhergestellter Verbindung einen Vollabgleich der Geraete
		public function watchdogWS(): void
		{
			$parentID = IPS_GetInstance($this->InstanceID)['ConnectionID'];
			if ($parentID <= 0) {
				return;
			}
			$status = IPS_GetInstance($parentID)['InstanceStatus'];
			$wasConnected = $this->GetBuffer('WsWasConnected');
			if ($status === 102) {
				if ($wasConnected === '0') {
					$this->SendDebug("UnifiPGW", "WebSocket wieder verbunden - starte Vollabgleich", 0);
					$this->SendDataToChildren(json_encode([
						'DataID' => '{C7147748-F01B-E4F9-D11E-72DFA08E7048}',
						'id' => '',
						'Api' => 'fullSync',
						'data' => ''
					]));
				}
				$this->SetBuffer('WsWasConnected', '1');
			} else {
				$this->SetBuffer('WsWasConnected', '0');
				if (!empty($this->ReadPropertyString('APIKey'))) {
					$this->SendDebug("UnifiPGW", "WebSocket nicht verbunden (Status " . $status . ") - Reconnect", 0);
					// Begruendete Abweichung von den Store-Richtlinien: IPS_ApplyChanges auf dem
					// eigenen Datenfluss-Parent stoesst nur einen Reconnect des WS Clients an -
					// es wird KEINE Konfiguration geaendert (der native WS Client bietet keine
					// eigene Reconnect-Funktion an).
					@IPS_ApplyChanges($parentID);
				}
			}
		}

		public function ReceiveData($JSONString): string
		{
			$data = json_decode($JSONString);
			if (!isset($data->Buffer)) {
				return '';
			}
			// Der I/O liefert den Payload hex-kodiert - zurueckwandeln in die Rohdaten
			$payload = hex2bin($data->Buffer);
			if ($payload === false) {
				$this->SendDebug("UnifiPGW", "WS-Payload nicht hex-dekodierbar", 0);
				return '';
			}
			$message = json_decode($payload, true);
			if (is_array($message)) {
				$this->SetBuffer('WsFragment', '');
				$this->processWsMessage($message);
				return '';
			}
			// Unvollstaendige Frames puffern, bis der JSON-Payload komplett ist
			$buffered = $this->GetBuffer('WsFragment') . $payload;
			$message = json_decode($buffered, true);
			if (is_array($message)) {
				$this->SetBuffer('WsFragment', '');
				$this->processWsMessage($message);
				return '';
			}
			if (strlen($buffered) > 2097152) {
				$this->SendDebug("UnifiPGW", "WS-Fragmentpuffer verworfen (>2MB)", 0);
				$buffered = '';
			}
			$this->SetBuffer('WsFragment', $buffered);
			return '';
		}

		private function processWsMessage(array $message): void
		{
			$deviceID = $message['item']['id'] ?? '';
			if ($deviceID === '') {
				$this->SendDebug("UnifiPGW", "WS-Update ohne Geraete-ID: " . json_encode($message), 0);
				return;
			}
			$this->SendDebug("UnifiPGW", "WS-Update (" . ($message['type'] ?? '?') . ") fuer " . $deviceID, 0);
			$this->SendDataToChildren(json_encode([
				'DataID' => '{C7147748-F01B-E4F9-D11E-72DFA08E7048}',
				'id' => $deviceID,
				'Api' => 'wsUpdate',
				'data' => json_encode($message)
			]));
		}

		public function updateVersion():void
		{
			if ( !$this->ReadPropertyBoolean("applicationVersion") ) {
				return;
			}
			$version = $this->getProtectVersion();
			$this->SendDebug("UnifiPGW", "Timer: Update Version: " . $version, 0);
			if ( @$this->GetIDForIdent('applicationVersion') ) {
				$this->SetValue('applicationVersion', $version);
			}
		}

		public function ForwardData($JSONString):string
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

			$this->SendDebug(__FUNCTION__, 'data=' . print_r($data, true), 0);

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
						$highQuality = false;
						if (!empty($data->Param1)) {
							$param1 = json_decode($data->Param1, true);
							if (is_array($param1) && array_key_exists('supportFullHDSnapshot', $param1)) {
								$highQuality = (bool)$param1['supportFullHDSnapshot'];
							}
						}
						$url = 'https://'.$ServerAddress.'/proxy/protect/integration/v1/cameras/' . $cameraID . '/snapshot';
						if ($highQuality) {
							$url .= '?highQuality=true';
						}
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
					case "patchSettingSpeaker":
						$setting = $this->patchSettingSpeaker($data->ID, $data->Param1);
						return serialize($setting);
					case "getDevicesConfig":
						$config = $this->getDevicesConfig();
						return serialize($config);
					case "getAlarmProfiles":
						$array = $this->getArmProfiles();
						return serialize($array);
					case "setCurrentArmProfile":
						$array = $this->setCurrentArmProfile($data->Param1);
						return serialize($array);
					case "enableArmAlarm":
						$array = $this->enableArmAlarm();
						return serialize($array);
					case "disableArmAlarm":
						$array = $this->disableArmAlarm();
						return serialize($array);
					case "getNvrs":
						$array = $this->getNvrs();
						return serialize($array);
					case "privApi_getCamera":
						$array = $this->privApi_getCamera($data->ID);
						return serialize($array);
					case 'privApi_setRecordingMode':
						$payload = json_decode($data->Param1, true);
						$ret = $this->privApi_setRecordingMode($data->ID, $payload['recordingMode']);
						return serialize($ret);
					case 'privApi_setPrivacy':
						$payload = json_decode($data->Param1, true);
						$ret = $this->privApi_setPrivacy($data->ID, $payload['enabled'], $payload['micVolume'], $payload['recordingMode']);
						return serialize($ret);
					default:
						$this->SendDebug("UnifiPGW", "Unknown API: " . $data->Api, 0);
						break;
				}
			}
			return serialize([]);
		}

		public function Send( int $id,string $Api, string $Text ):void
		{
			$this->SendDataToChildren( json_encode( [ 'DataID' => '{C7147748-F01B-E4F9-D11E-72DFA08E7048}',
					'id' =>  $id,
					'Api'=> $Api,
					'data'=> $Text ]));
		}



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
			} elseif ($deviceType === 'UP-Sense' || $deviceType === 'UP-AirQuality') {
				$JSONData = $this->getSensors();
			} elseif ($deviceType === 'Chime') {
				$JSONData = $this->getChimes();
			} elseif ($deviceType === 'Lights') {
				$JSONData = $this->getLights();
			} elseif ($deviceType === 'Speaker') {
				$JSONData = $this->getSpeakers();
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

		public function getSpeakers():array {
			$JSONData = $this->getApiData( '/speakers' );
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

		public function getArmProfiles():array {
			$JSONData = $this->getApiData( '/arm-profiles' );
			$this->SendDebug("UnifiPGW", "getArmProfiles: " . json_encode($JSONData), 0);
			if ( !is_array( $JSONData ) ) {
				return [];
			}
			// Endpoint liefert ein reines Array von Profilen zurück
			$value = [];
			foreach ( $JSONData as $profile ) {
				if ( !is_array( $profile ) || !isset( $profile['id'] ) ) {
					continue;
				}
				$value[] = [
					'id'   => $profile['id'],
					'name' => $profile['name'] ?? $profile['id']
				];
			}
			usort( $value, function ( $a, $b ) {
				return strcmp( $a['name'], $b['name'] );
			});
			return $value;
		}

		public function setCurrentArmProfile(string $profileID):array {
			if ( empty( $profileID ) ) {
				$this->SendDebug("UnifiPGW", "setCurrentArmProfile: empty profile ID", 0);
				return [];
			}
			$this->patchApiData( '/arm-profiles/settings', json_encode( [ 'armProfileId' => $profileID ] ) );
			return [];
		}

		public function getNvrs():array {
			$JSONData = $this->getApiData( '/nvrs' );
			$this->SendDebug("UnifiPGW", "getNvrs: " . json_encode($JSONData), 0);
			if ( !is_array( $JSONData ) ) {
				return [];
			}
			// Endpoint kann ein einzelnes Objekt oder ein Array von NVRs liefern
			if ( isset( $JSONData['id'] ) || isset( $JSONData['armMode'] ) ) {
				return $JSONData;
			}
			foreach ( $JSONData as $nvr ) {
				if ( is_array( $nvr ) ) {
					return $nvr;
				}
			}
			return [];
		}

		public function enableArmAlarm():array {
			$this->getApiDataPost( '/arm-profiles/enable' );
			return [];
		}

		public function disableArmAlarm():array {
			$this->getApiDataPost( '/arm-profiles/disable' );
			return [];
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

		public function patchSettingSpeaker(string $speakerID, string $setting):array {
			$JSONData = $this->patchApiData( '/speakers/' . $speakerID, $setting);
			return $JSONData;
		}
		public function getDeviceData(string $deviceID,string $deviceType):array {
			if (empty($deviceID)) {
				$this->SendDebug("UnifiPGW", "Device ID is empty, returning empty array.", 0);
				return [];
			}
			if ($deviceType === 'Camera') {
				$JSONData = $this->getApiData( '/cameras/' . $deviceID );
			} elseif ($deviceType === 'UP-Sense' || $deviceType === 'UP-AirQuality') {
				$JSONData = $this->getApiData( '/sensors/' . $deviceID );
			} elseif ($deviceType === 'Light') {
				$JSONData = $this->getApiData( '/lights/' . $deviceID );
			} elseif ($deviceType === 'Chime') {
				$JSONData = $this->getApiData( '/chimes/' . $deviceID );
			} elseif ($deviceType === 'Speaker') {
				$JSONData = $this->getApiData( '/speakers/' . $deviceID );
			} else {
				$this->SendDebug("UnifiPGW", "Unknown device type: " . $deviceType, 0);
				return [];
			}			
			return $JSONData;
		}

		public function GetConfigurationForm():string {
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

			$arrayElements[] = [
				'type'    => 'PopupAlert',
				'name'    => 'MessagePopup',
				'visible' => false,
				'popup'   => [
					'items'   => [
						[
							'type'    => 'Label',
							'name'    => 'MessagePopup_text',
							'caption' => '',
						],
					],
				],
			];

			$arrayElements[] = array( 'type' => 'Label','bold' => true, 'label' => $this->Translate('UniFi Protect Gateway'));
			$arrayElements[] = array( 'type' => 'Label', 'label' => $this->Translate('Please create API Key under "UniFi Network > Settings > Control Plane > Integrations"'));
			$arrayElements[] = array( 'type' => 'ValidationTextBox', 'name' => 'ServerAddress', 'caption' => $this->Translate('Unifi Device IP'), 'validate' => "^(([a-zA-Z0-9\\.\\-\\_]+(\\.[a-zA-Z]{2,3})+)|(\\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\b))$" );

			$arrayElements[] = [
				'type'      => 'ExpansionPanel',
				'caption'   => 'Public API (required)',
				'expanded'  => true,
				'items'     => [
					[
						'type'    => 'ValidationTextBox',
						'name'    => 'APIKey',
						'caption' => $this->Translate('API key'),
					],
				],
			];

			$arrayElements[] = [
				'type'      => 'ExpansionPanel',
				'caption'   => 'Unofficial API (optional)',
				'expanded'  => false,
				'items'     => [
					[
						'type'    => 'ValidationTextBox',
						'name'    => 'localUser',
						'caption' => $this->Translate('Local UniFi user')
					],
					[
						'type'    => 'PasswordTextBox',
						'name'    => 'localPassword',
						'caption' => $this->Translate('Password of local user')
					],
				],
			];

			$arrayElements[] = array( 'type' => 'CheckBox', 'name' => 'applicationVersion', 'caption' => $this->Translate('Show Application Version') );
			$arrayElements[] = array( 'type' => 'NumberSpinner', 'name' => 'VersionTimer', 'caption' => $this->Translate('Version update interval (s) -> 0=Off') );

			if ( !empty( $APIKey && $this->GetStatus() === 102)) {
				if ($this->ReadPropertyBoolean("applicationVersion")) {
					$this->SetValue('applicationVersion', $this->getProtectVersion());
				}

				$arrayElements[] = array( 'type' => 'Label', 'label' => $this->Translate('Protect Application Version: ').$this->getProtectVersion() );           
			} else {
				$arrayElements[] = array( 'type' => 'Label', 'label' => $this->Translate('Protect Application Version: not found') );
			}
		
			#$arrayElements[] = array( 'type' => 'Select', 'name' => 'Site', 'caption' => 'Site', 'options' => $arrayOptions );

			$arrayActions = [];

            $arrayActions[] = [
                'type'    => 'Button',
                'caption' => 'Test access',
                'onClick' => 'IPS_RequestAction(' . $this->InstanceID . ', "TestAccess", "");',
            ];

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
                   // Vape/Air-Quality-Sensor am Geraetetyp erkennen (z.B. "UP-AirQuality"), sonst UP-Sense
                   $sensorDeviceType = (stripos($sensor['type'] ?? '', 'air') !== false) ? 'UP-AirQuality' : 'UP-Sense';
                   $addValue = array(
                        'Name'	=>$sensor[ 'name' ],
                        'Type'	=>$sensor[ 'type' ] ?? $sensor[ 'modelKey' ],
						'State' =>$sensor['state'],
                        'ID'		=>isset( $sensor[ 'id' ] ) ? $sensor[ 'id' ] : 'missing' ,
                        'instanceID'	=>$this->getInstanceIDForGuid( $sensor[ 'id' ], '{F78D1159-D735-D23A-0A97-69F07962BB89}' )
                        );
                        if (isset($sensor['id']) && !empty($sensor['id'])) {
                            $addValue['create'] = array(
                            'moduleID'      => '{F78D1159-D735-D23A-0A97-69F07962BB89}',
                            'configuration' => [
                                'ID'	=> isset( $sensor[ 'id' ] ) ? $sensor[ 'id' ] : '',
								'DeviceType' => $sensorDeviceType
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
				#https://192.168.178.1/proxy/protect/integration/v1/speakers
				$speakers = $this->getApiData( '/speakers/' );
				if ( is_array( $speakers ) && isset( $speakers ) ) {
					usort( $speakers, function ( $a, $b ) {
					return $a[ 'name' ]>$b[ 'name' ];
					});
					foreach ( $speakers as $speaker )
					{
					$addValue = array(
							'Name'	=>$speaker[ 'name' ],
							'Type'	=>$speaker[ 'modelKey' ],
							'State' =>$speaker['state'],
							'ID'		=>isset( $speaker[ 'id' ] ) ? $speaker[ 'id' ] : 'missing' ,
							'instanceID'	=>$this->getInstanceIDForGuid( $speaker[ 'id' ], '{F78D1159-D735-D23A-0A97-69F07962BB89}' )
							);
							if (isset($speaker['id']) && !empty($speaker['id'])) {
								$addValue['create'] = array(
								'moduleID'      => '{F78D1159-D735-D23A-0A97-69F07962BB89}',
								'configuration' => [
									'ID'	=> isset( $speaker[ 'id' ] ) ? $speaker[ 'id' ] : '',
									'DeviceType' => 'Speaker'
								],
								'name' => $speaker[ 'name' ]
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

	/***** inoffizielle API (https://github.com/uilibs/uiprotect) */

    public function RequestAction(string $ident, mixed $value): void
    {
        $this->SendDebug(__FUNCTION__, 'ident=' . $ident . ', value=' . $value, 0);

        $r = false;
        switch ($ident) {
            case 'TestAccess':
                $this->TestAccess();
                break;
            default:
                $this->SendDebug(__FUNCTION__, 'invalid ident ' . $ident, 0);
                break;
        }

        if ($r) {
            $this->SetValue($ident, $value);
        }
    }

    private function TestAccess()
    {
        $txt = $this->Translate('Public API') . PHP_EOL;

        $APIKey = $this->ReadPropertyString('APIKey');
        if ($APIKey != '') {
            $version = $this->getApiData('/meta/info');
            if ($this->GetStatus() != IS_ACTIVE || is_array($version) == false) {
                $txt .= $this->Translate('unable to access public API with API key') . PHP_EOL;
            } else {
                $txt .= $this->Translate('Access to public API successful') . PHP_EOL;
            }
        } else {
            $txt .= $this->Translate('not configured');
        }

        $txt .= PHP_EOL;
        $txt .= $this->Translate('Unofficial API') . PHP_EOL;
        if ($this->privApi_enabled()) {
            $data = $this->privApi_getCameras();
            if ($this->GetStatus() != IS_ACTIVE || $data == false) {
                $txt .= $this->Translate('unable to access local account') . PHP_EOL;
            } else {
                $cams = @json_decode($data, true);
                $n_cams = count($cams);
                $txt .= $n_cams . ' ' . $this->Translate('registered cameras found') . PHP_EOL;
            }
        } else {
            $txt .= $this->Translate('not configured');
        }

        $this->UpdateFormField('MessagePopup_text', 'caption', $txt);
        $this->UpdateFormField('MessagePopup', 'visible', true);
    }

    private function build_header($headerfields)
    {
        $header = [];
        foreach ($headerfields as $key => $value) {
            $header[] = $key . ': ' . $value;
        }
        return $header;
    }

    private function privApi_enabled()
    {
        $localUser = $this->ReadPropertyString('localUser');
        $localPassword = $this->ReadPropertyString('localPassword');

        return $localUser != '' && $localPassword != '';
    }

    private function privApi_login()
    {
        $host = $this->ReadPropertyString('ServerAddress');
        $localUser = $this->ReadPropertyString('localUser');
        $localPassword = $this->ReadPropertyString('localPassword');

        $this->WriteAttributeString('localToken', '');

        if ($this->privApi_enabled() == false) {
            return false;
        }

        $url = 'https://' . $host . '/api/auth/login';

        $headerfields = [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ];
        $header = $this->build_header($headerfields);

        $this->SendDebug(__FUNCTION__, 'http-POST, url=' . $url, 0);
        $this->SendDebug(__FUNCTION__, '... header=' . print_r($header, true), 0);

        $postfields = [
            'username'   => $localUser,
            'password'   => $localPassword,
            'rememberMe' => true,
        ];
        $postdata = json_encode($postfields, JSON_UNESCAPED_UNICODE);

        $this->SendDebug(__FUNCTION__, '... postfields=' . print_r($postfields, true), 0);
        $this->SendDebug(__FUNCTION__, '... postdata=' . $postdata, 0);

        $responseHeaders = [];

        $curl_opts = [
            CURLOPT_HTTPHEADER     => $header,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postdata,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_HEADERFUNCTION => function ($ch, $line) use (&$responseHeaders)
            {
                $p = explode(':', $line, 2);
                if (count($p) === 2) {
                    $responseHeaders[strtolower(trim($p[0]))][] = trim($p[1]);
                }
                return strlen($line);
            },
        ];

        $time_start = microtime(true);

        $ch = curl_init($url);
        curl_setopt_array($ch, $curl_opts);
        $response = curl_exec($ch);
        $cerrno = curl_errno($ch);
        $cerror = $cerrno ? curl_error($ch) : '';
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_info = curl_getinfo($ch);
        if (IPS_GetKernelVersion() < 8.5) {
            curl_close($ch);
        }

        $duration = round(microtime(true) - $time_start, 2);
        $this->SendDebug(__FUNCTION__, ' => errno=' . $cerrno . ', httpcode=' . $httpcode . ', duration=' . $duration . 's', 0);
        $this->SendDebug(__FUNCTION__, ' => response=' . $response, 0);

        if ($cerrno) {
            $this->SendDebug(__FUNCTION__, 'got curl-errno ' . $cerrno . ' (' . $cerror . ')', 0);
            $this->SetStatus(201); // Set status to error
            return false;
        }

        $header_size = $curl_info['header_size'];
        $head = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $this->SendDebug(__FUNCTION__, ' => head=' . $head, 0);
        $this->SendDebug(__FUNCTION__, ' => body=' . $body, 0);

        $csrf = '';
        $cookie = '';
        $expiration = 0;

        // CSRF-Token und Token-Cookie aus der Antwort uebernehmen
        if (empty($responseHeaders['x-csrf-token']) == false) {
            $csrf = end($responseHeaders['x-csrf-token']);
        }
        if (empty($responseHeaders['set-cookie']) == false) {
            foreach ($responseHeaders['set-cookie'] as $sc) {
                $pair = explode(';', $sc, 2)[0];           // "TOKEN=eyJ..."
                $name = trim(explode('=', $pair, 2)[0]);
                if ($name === 'TOKEN' || $name === 'UOS_TOKEN') {
                    $cookie = trim($pair);
                }
            }
        }
        if (empty($responseHeaders['x-token-expire-time']) == false) {
            $expiration = (int) end($responseHeaders['x-token-expire-time']);
            $expiration = intdiv($expiration, 1000);

            // Sicherheitsmarge damit der Token rechtzeitig erneuれrt wird
            $expiration -= 60;
        }

        $this->SendDebug(__FUNCTION__, 'cookie=' . $cookie . ', csrf=' . $csrf . ', expiration=' . ($expiration ? date('d.m.Y H:i:s', $expiration) : '-'), 0);

        if ($cookie == '' || $csrf == '') {
            $this->SendDebug(__FUNCTION__, 'cookie and/or csrf not found', 0);
            $this->SetStatus(201);
            return false;
        }

        $jtoken = [
            'csrf'       => $csrf,
            'cookie'     => $cookie,
            'expiration' => $expiration,
        ];

        $this->WriteAttributeString('localToken', json_encode($jtoken));

        return $jtoken;
    }

    private function privApi_getToken()
    {
        $jtoken = @json_decode($this->ReadAttributeString('localToken'), true);

        if (empty($jtoken['csrf']) || empty($jtoken['cookie'])) {
            $this->SendDebug(__FUNCTION__, 'no saved token', 0);
            $jtoken = false;
        } else {
            $expiration = $jtoken['expiration'] ?? 0;
            if ($expiration < time()) {
                $this->SendDebug(__FUNCTION__, 'token expired', 0);
                $jtoken = false;
            }
        }

        if ($jtoken == false && $this->privApi_login() == false) {
            return false;
        }

        $jtoken = @json_decode($this->ReadAttributeString('localToken'), true);
        return $jtoken;
    }

    private function privApi_http(string $endpoint, string $method, array $postfields)
    {
        $this->SendDebug(__FUNCTION__, 'endpoint=' . $endpoint . ', method=' . $method . ', postfields=' . print_r($postfields, true), 0);
        $host = $this->ReadPropertyString('ServerAddress');

        if ($this->privApi_enabled() == false) {
            return false;
        }

        $maxRetries = 3;
        $retry = 0;

        if (!IPS_SemaphoreEnter('UnifiProtectAPI', 500)) {
            $this->SendDebug(__FUNCTION__, 'Semaphore Timeout - Request abgebrochen', 0);
            return [];
        }

        $url = 'https://' . $host . '/proxy/protect/api' . $endpoint;
        $method = strtoupper($method);

        do {
            try {
                $jtoken = $this->privApi_getToken();
                if ($jtoken == false) {
                    return false;
                }

                $headerfields = [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                    'Cookie'       => $jtoken['cookie'],
                    'X-CSRF-Token' => $jtoken['csrf'],
                ];
                $header = $this->build_header($headerfields);

                $this->SendDebug(__FUNCTION__, 'http-' . $method . ', url=' . $url . ', try ' . $retry . '/' . $maxRetries, 0);
                $this->SendDebug(__FUNCTION__, '... header=' . print_r($header, true), 0);

                if (empty($postfields) == false) {
                    $postdata = json_encode($postfields, JSON_UNESCAPED_UNICODE);

                    $this->SendDebug(__FUNCTION__, '... postfields=' . print_r($postfields, true), 0);
                    $this->SendDebug(__FUNCTION__, '... postdata=' . $postdata, 0);
                }

                $curl_opts = [
                    CURLOPT_CUSTOMREQUEST  => $method,
                    CURLOPT_HTTPHEADER     => $header,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_TIMEOUT        => 30,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER         => true,
                ];

                if (empty($postfields) == false) {
                    $curl_opts[CURLOPT_POSTFIELDS] = $postdata;
                }

                $this->SendDebug(__FUNCTION__, 'curl_opts=' . print_r($curl_opts, true), 0);

                $time_start = microtime(true);

                $ch = curl_init($url);
                curl_setopt_array($ch, $curl_opts);
                $response = curl_exec($ch);
                $cerrno = curl_errno($ch);
                $cerror = $cerrno ? curl_error($ch) : '';
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curl_info = curl_getinfo($ch);
                if (IPS_GetKernelVersion() < 8.5) {
                    curl_close($ch);
                }

                $duration = round(microtime(true) - $time_start, 2);
                $this->SendDebug(__FUNCTION__, ' => errno=' . $cerrno . ', httpcode=' . $httpcode . ', duration=' . $duration . 's', 0);
                $this->SendDebug(__FUNCTION__, ' => response=' . $response, 0);

                if ($cerrno) {
                    $this->SendDebug(__FUNCTION__, 'got curl-errno ' . $cerrno . ' (' . $cerror . ')', 0);
                    $this->SetStatus(201); // Set status to error
                    return false;
                }

                $header_size = $curl_info['header_size'];
                $head = substr($response, 0, $header_size);
                $body = substr($response, $header_size);
                $this->SendDebug(__FUNCTION__, ' => head=' . $head, 0);
                $this->SendDebug(__FUNCTION__, ' => body=' . $body, 0);

                if ($httpcode == 401) {
                    $this->SendDebug(__FUNCTION__, 'http request rejected, retry with new token', 0);
                    $this->WriteAttributeString('localToken', '');
                } elseif ($httpcode == 429) {
                    $this->SendDebug(__FUNCTION__, 'rate limit reached, wait 5s', 0);
                    usleep(5 * 1000);
                } elseif ($httpcode == 200) {
                    return $body;
                } else {
                    $this->SendDebug(__FUNCTION__, 'http request failed', 0);
                    $this->SetStatus($httpcode);
                    return false;
                }
            } finally {
                IPS_SemaphoreLeave('UnifiProtectAPI');
            }
        } while ($retry < $maxRetries);
        $this->SendDebug(__FUNCTION__, 'more than ' . $maxRetries . ' tries, abort', 0);
        return false;
    }

    private function privApi_getCameras()
    {
        if ($this->privApi_enabled() == false) {
            return false;
        }

        $body = $this->privApi_http('/cameras', 'GET', []);
        if ($body == false) {
            return false;
        }
        if (empty($body)) {
            $this->SendDebug(__FUNCTION__, 'no cameras found', 0);
            return false;
        }

        return $body;
    }

    private static $privApi_privacy_zone_name = 'symcon_privacy';

    private function privApi_getCamera(string $deviceId)
    {
        $body = $this->privApi_http('/cameras/' . $deviceId, 'GET', []);
        if ($body == false) {
            return false;
        }
        if (empty($body)) {
            $this->SendDebug(__FUNCTION__, 'no cameras found', 0);
            return false;
        }

        $cam = json_decode($body, true);

        $privacyEnabled = false;
        if (empty($cam['featureFlags']['hasPrivacyMask']) == false) {
            $zones = isset($cam['privacyZones']) ? array_values($cam['privacyZones']) : [];
            foreach (array_values($zones) as $i => $z) {
                if (($z['name'] ?? null) === self::$privApi_privacy_zone_name) {
                    $privacyEnabled = true;
                    break;
                }
            }
        }
        $cam['privacyEnabled'] = $privacyEnabled;

        return $cam;
    }

    private function privApi_setRecordingMode(string $deviceId, string $recordingMode)
    {
        $this->SendDebug(__FUNCTION__, 'deviceId=' . $deviceId . ', recordingMode=' . $recordingMode, 0);

        $cam = $this->privApi_getCamera($deviceId);
        if ($cam == false) {
            $ret = [
                'msg'    => 'not found',
                'status' => false,
            ];
            $this->SendDebug(__FUNCTION__, 'ret=' . print_r($ret, true), 0);
            return $ret;
        }

        $prev = [
            'recordingMode' => $cam['recordingSettings']['mode'],
        ];

        if (($cam['recordingSettings']['mode'] ?? null) === $recordingMode) {
            $ret = [
                'msg'    => 'unchanged',
                'status' => true,
            ];
            $this->SendDebug(__FUNCTION__, 'ret=' . print_r($ret, true), 0);
            return $ret;
        }

        $postfields = [
            'recordingSettings' => [
                'mode' => $recordingMode,
            ],
        ];
        $buf = $this->privApi_http('/cameras/' . $deviceId, 'PATCH', $postfields);
        if ($buf == false) {
            $ret = [
                'prev'   => $prev,
                'msg'    => 'patch failed',
                'status' => false,
            ];
            $this->SendDebug(__FUNCTION__, 'ret=' . print_r($ret, true), 0);
            return $ret;
        }

        $ret = [
            'prev'   => $prev,
            'msg'    => 'succeed',
            'status' => true,
        ];
        $this->SendDebug(__FUNCTION__, 'ret=' . print_r($ret, true), 0);
        return $ret;
    }

    private function privApi_setPrivacy(string $deviceId, bool $enabled, int $micVolume = null, string $recordingMode = null)
    {
        $this->SendDebug(__FUNCTION__, 'deviceId=' . $deviceId . ', enabled=' . ($enabled ? 'true' : 'false') . ', micVolume=' . $micVolume . ', recordingMode=' . $recordingMode, 0);

        $cam = $this->privApi_getCamera($deviceId);
        if ($cam == false) {
            $ret = [
                'msg'    => 'not found',
                'status' => false,
            ];
            $this->SendDebug(__FUNCTION__, 'ret=' . print_r($ret, true), 0);
            return $ret;
        }

        if (empty($cam['featureFlags']['hasPrivacyMask'])) {
            $ret = [
                'msg'    => 'privacy zones are not supported',
                'status' => false,
            ];
            $this->SendDebug(__FUNCTION__, 'ret=' . print_r($ret, true), 0);
            return $ret;
        }

        $zones = isset($cam['privacyZones']) ? array_values($cam['privacyZones']) : [];

        $idx = null;
        foreach (array_values($zones) as $i => $z) {
            if (($z['name'] ?? null) === self::$privApi_privacy_zone_name) {
                $idx = $i;
                break;
            }
        }

        $prev = [
            'enabled'         => ($idx !== null),
            'micVolume'       => (int) $cam['micVolume'],
            'recordingMode'   => $cam['recordingSettings']['mode'],
        ];

        $postfields = [];

        if ($enabled) {
            if ($idx === null) {
                $id = 0;
                if (count($zones) > 0) {
                    $last = end($zones);
                    $id = (int) $last['id'] + 1;
                }
                $zones[] = [
                    'id'     => $id,
                    'name'   => self::$privApi_privacy_zone_name,
                    'color'  => '#85BCEC',
                    'points' => [[0, 0], [1, 0], [1, 1], [0, 1]],
                ];
                $postfields['privacyZones'] = $zones;
            }
        } else {
            if ($idx !== null) {
                unset($zones[$idx]);
                $postfields['privacyZones'] = array_values($zones);
            }
        }

        if ($micVolume !== null) {
            $micVolume = max(0, min(100, (int) $micVolume));
            if (isset($cam['micVolume']) && (int) $cam['micVolume'] === $micVolume) {
                $postfields['micVolume'] = $micVolume;
            }
        }
        if ($recordingMode !== null) {
            if (isset($cam['recordingSettings']['mode']) && $cam['recordingSettings']['mode'] === $recordingMode) {
                $postfields['recordingSettings'] = ['mode' => $recordingMode];
            }
        }

        if (count($postfields) === 0) {
            $ret = [
                'msg'    => 'unchanged',
                'status' => true,
            ];
            $this->SendDebug(__FUNCTION__, 'ret=' . print_r($ret, true), 0);
            return $ret;
        }

        $buf = $this->privApi_http('/cameras/' . $deviceId, 'PATCH', $postfields);
        if ($buf == false) {
            $ret = [
                'prev'   => $prev,
                'msg'    => 'patch failed',
                'status' => false,
            ];
            $this->SendDebug(__FUNCTION__, 'ret=' . print_r($ret, true), 0);
            return $ret;
        }

        $ret = [
            'prev'   => $prev,
            'msg'    => 'succeed',
            'status' => true,
        ];
        $this->SendDebug(__FUNCTION__, 'ret=' . print_r($ret, true), 0);
        return $ret;
    }
}
