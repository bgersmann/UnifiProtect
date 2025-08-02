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
			IPS_LogMessage('UnifiPGW', $JSONString);
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
					case "getCameras":
						$array = $this->getCameras();
						$this->send($data->InstanceID,$data->Api,json_encode($array));
						break;
					case "getStreams":
						$array = $this->getStreams(IPS_GetProperty( $data->InstanceID, 'ID' ));
						$this->send($data->InstanceID,$data->Api,json_encode($array));
						break;
					case "getSnapshot":
						$snapshot = $this->getSnapshot(IPS_GetProperty( $data->InstanceID, 'ID' ), $data->InstanceID);
						$this->send($data->InstanceID,$data->Api,$snapshot);
						break;
					case "createStream":
						$stream = $this->createStream(IPS_GetProperty( $data->InstanceID, 'ID' ), $data->Param1);
						$this->send($data->InstanceID,$data->Api,json_encode($stream));
						break;
					case "getCameraData":
						$cameraData = $this->getCameraData(IPS_GetProperty( $data->InstanceID, 'ID' ));
						$this->send($data->InstanceID,$data->Api,json_encode($cameraData));
						break;
					case "patchSetting":
						$setting = $this->patchSetting(IPS_GetProperty( $data->InstanceID, 'ID' ), $data->Param1);
						$this->send($data->InstanceID,$data->Api,json_encode($setting));
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

		public function getApiData( string $endpoint = '' ):array {
			$ServerAddress = $this->ReadPropertyString( 'ServerAddress' );
			$APIKey = $this->ReadPropertyString( 'APIKey' );

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, 'https://'.$ServerAddress.'/proxy/protect/integration/v1'.$endpoint );
			curl_setopt( $ch, CURLOPT_HTTPGET, true );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'X-API-KEY:'.$APIKey ) );
			curl_setopt( $ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1' );
			$RawData = curl_exec($ch);
			curl_close( $ch );
			$this->SendDebug("UnifiPGW", "API Endpoint: " .$RawData, 0);
			if ($RawData === false) {
				// Handle error
				$this->SendDebug("UnifiPGW", "Curl error: " . curl_error($ch), 0);
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
			return $JSONData;
		}

		public function deleteApiData( string $endpoint = '' ):array {
			$ServerAddress = $this->ReadPropertyString( 'ServerAddress' );
			$APIKey = $this->ReadPropertyString( 'APIKey' );

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, 'https://'.$ServerAddress.'/proxy/protect/integration/v1'.$endpoint );
			#curl_setopt( $ch, CURLOPT_HTTPGET, true );
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'X-API-KEY:'.$APIKey ) );
			curl_setopt( $ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1' );
			$RawData = curl_exec($ch);
			curl_close( $ch );
			$this->SendDebug("UnifiPGW", "API Endpoint: " .$RawData, 0);
			if ($RawData === false) {
				// Handle error
				$this->SendDebug("UnifiPGW", "Curl error: " . curl_error($ch), 0);
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
			return [];
		}

		public function patchApiData( string $endpoint = '', string $PostData = '' ):array {
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
			$RawData = curl_exec($ch);
			curl_close( $ch );
			$this->SendDebug("UnifiPGW", "API Endpoint: " .$RawData, 0);
			if ($RawData === false) {
				// Handle error
				$this->SendDebug("UnifiPGW", "Curl error: " . curl_error($ch), 0);
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
			return [];
		}

		public function getApiDataPost( string $endpoint = '', string $PostData = '' ):array {
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
			$RawData = curl_exec($ch);
			curl_close( $ch );
			$this->SendDebug("UnifiPGW", "API Endpoint: " .$RawData, 0);
			if ($RawData === false) {
				// Handle error
				$this->SendDebug("UnifiPGW", "Curl error: " . curl_error($ch), 0);
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
			return $JSONData;
		}

		public function getProtectVersion ():string {
			$version=$this->getApiData( '/meta/info' );
			$this->SendDebug("UnifiPGW", "Protect Version: " . ($version['applicationVersion'] ?? ''), 0);
			return $version['applicationVersion'] ?? '';
		}


		public function getCameras():array {			
			$JSONData = $this->getApiData( '/cameras' );
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

		public function getStreams(string $cameraID):array {
			$JSONData = $this->getApiData( '/cameras/' . $cameraID . '/rtsps-stream' );
			$this->SendDebug("UnifiPGW", "getStreams: " . json_encode($JSONData), 0);
			return $JSONData;
		}

		public function patchSetting(string $cameraID, string $setting):array {
			$JSONData = $this->patchApiData( '/cameras/' . $cameraID, $setting);
			return $JSONData;
		}

		public function getCameraData(string $cameraID):array {
			$JSONData = $this->getApiData( '/cameras/' . $cameraID );
			return $JSONData;
		}

		public function getSnapshot(string $cameraID, int $idParent):string {
			$ServerAddress = $this->ReadPropertyString( 'ServerAddress' );
			$APIKey = $this->ReadPropertyString( 'APIKey' );
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, 'https://'.$ServerAddress.'/proxy/protect/integration/v1/cameras/' . $cameraID . '/snapshot' );
			curl_setopt( $ch, CURLOPT_HTTPGET, true );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'X-API-KEY:'.$APIKey ) );
			curl_setopt( $ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1' );
			$RawData = curl_exec($ch);
			curl_close( $ch );
			if ($RawData === false) {
				// Handle error
				$this->SendDebug("UnifiPGW", "Curl error: " . curl_error($ch), 0);
				$this->SetStatus( 201 ); // Set status to error
				return '';
			}
			return  base64_encode($RawData);
		}

		public function GetConfigurationForm() {
			$ServerAddress = $this->ReadPropertyString( 'ServerAddress' );
			$APIKey = $this->ReadPropertyString( 'APIKey' );

			$arrayStatus = array();
			$arrayStatus[] = array( 'code' => 102, 'icon' => 'active', 'caption' => 'Instanz ist aktiv' );
			$arrayStatus[] = array( 'code' => 201, 'icon' => 'inactive', 'caption' => 'Instanz ist fehlerhaft: Fehler Datenabfrage' );
			$arrayStatus[] = array( 'code' => 400, 'icon' => 'inactive', 'caption' => 'Instanz ist fehlerhaft: Bad Request' );
			$arrayStatus[] = array( 'code' => 401, 'icon' => 'inactive', 'caption' => 'Instanz ist fehlerhaft: Unauthorized' );
			$arrayStatus[] = array( 'code' => 403, 'icon' => 'inactive', 'caption' => 'Instanz ist fehlerhaft: Forbidden' );
			$arrayStatus[] = array( 'code' => 404, 'icon' => 'inactive', 'caption' => 'Instanz ist fehlerhaft: Not Found' );
			$arrayStatus[] = array( 'code' => 429, 'icon' => 'inactive', 'caption' => 'Instanz ist fehlerhaft: Rate Limit' );
			$arrayStatus[] = array( 'code' => 500, 'icon' => 'inactive', 'caption' => 'Instanz ist fehlerhaft: Server Error' );
			$arrayStatus[] = array( 'code' => 502, 'icon' => 'inactive', 'caption' => 'Instanz ist fehlerhaft: Bad Gateway' );

			$arraySort = array();
			#$arraySort = array( 'column' => 'DeviceName', 'direction' => 'ascending' );

			$arrayElements = array();
			$arrayElements[] = array( 'type' => 'Label', 'label' => $this->Translate('UniFi Protect Gateway'));
			$arrayElements[] = array( 'type' => 'Label', 'label' => 'Bitte API Key unter "UniFi Network > Settings > Control Plane > Integrations" erzeugen');
			$arrayElements[] = array( 'type' => 'ValidationTextBox', 'name' => 'ServerAddress', 'caption' => 'Unifi Device IP', 'validate' => "^(([a-zA-Z0-9\\.\\-\\_]+(\\.[a-zA-Z]{2,3})+)|(\\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\b))$" );
			$arrayElements[] = array( 'type' => 'ValidationTextBox', 'name' => 'APIKey', 'caption' => 'APIKey' );
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

	}