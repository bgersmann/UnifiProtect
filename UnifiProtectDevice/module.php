<?php

declare(strict_types=1);
	class UnifiProtectDevice extends IPSModuleStrict
	{
		public function Create() : void
		{
			//Never delete this line!
			parent::Create();
			//$this->ConnectParent('{3F49B3E6-093C-40FA-661C-3D31BE37AEA3}');
			$this->RegisterPropertyString( 'DeviceType', 'Camera' );
			$this->RegisterPropertyString( 'ID', '' );
			$this->RegisterPropertyBoolean( 'StreamLow', false );
			$this->RegisterPropertyBoolean( 'StreamMedium', false );
			$this->RegisterPropertyBoolean( 'StreamHigh', false );
			$this->RegisterPropertyInteger( 'Timer', 0 );
			$this->RegisterPropertyBoolean( 'IDAnzeigen', false );
			$this->RegisterPropertyBoolean( 'Temperature', false );
			$this->RegisterPropertyBoolean( 'Humidity', false );
			$this->RegisterPropertyBoolean( 'Illuminance', false );
			$this->RegisterPropertyBoolean( 'Motion', false );			
			$this->RegisterTimer( 'Collect Data', 0, "UNIFIPDV_getData(\$_IPS['TARGET']);" );
			
		}

		// public function GetCompatibleParents(): string
        // {
        //     return json_encode([
        //         'type' => 'connect',
        //         'moduleIDs' => [
        //             '{3F49B3E6-093C-40FA-661C-3D31BE37AEA3}'
        //         ]
        //     ]);
        // }
		public function Destroy(): void
		{
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges(): void
		{
			//Never delete this line!
			parent::ApplyChanges();
			$vpos = 100;
			$this->MaintainVariable( 'Name', $this->Translate( 'Name' ), 3, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0 ,'ICON'=> 'circle-info'], $vpos++, 1 );
			$this->MaintainVariable( 'ID', $this->Translate( 'ID' ), 3,[ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0 ,'ICON'=> 'circle-info'], $vpos++, $this->ReadPropertyBoolean("IDAnzeigen") );
			$this->MaintainVariable( 'Model', $this->Translate( 'Model' ), 3, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0 ,'ICON'=> 'circle-info'], $vpos++, 1 );
			
			$this->MaintainVariable( 'micEnabled', $this->Translate( 'Is Microphone enabled' ), 0, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0 ,'ICON'=> 'microphone-lines', 'OPTIONS' => '[{"ColorDisplay":16077123,"Value":false,"Caption":"Aus","IconValue":"","IconActive":false,"ColorActive":true,"ColorValue":16077123,"Color":-1},{"ColorDisplay":1692672,"Value":true,"Caption":"An","IconValue":"","IconActive":false,"ColorActive":true,"ColorValue":1692672,"Color":-1}]'], $vpos++, $this->ReadPropertyString('DeviceType') == 'Camera');
			$this->MaintainVariable( 'micVolume', $this->Translate( 'Microphone Volume' ), 1, [ 'PRESENTATION' => VARIABLE_PRESENTATION_SLIDER, 'MAX'=>100,'MIN'=>1,'STEP_SIZE'=>1,'USAGE_TYPE'=> 2, 'SUFFIX'=> ' %' , 'ICON'=> 'volume-high'], $vpos++, $this->ReadPropertyString('DeviceType') == 'Camera');
			#$this->MaintainVariable( 'snapshot', $this->Translate( 'Snapshot' ), 1, [ 'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,'LAYOUT'=> 2, 'OPTIONS'=>'[{"Caption":"Erzeuge Snapshot...","Color":16711680,"IconActive":false,"IconValue":"","Value":0},{"Caption":"Snapshot","Color":65280,"IconActive":false,"IconValue":"","Value":1}]', 'ICON'=> 'camera-polaroid'], $vpos++, $this->ReadPropertyString('DeviceType') == 'Camera');
			$this->MaintainVariable( 'snapshot', $this->Translate( 'Snapshot' ), 1, [ 'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,'LAYOUT'=> 2, 'OPTIONS'=>'[{"Caption":"Snapshot","Color":65280,"IconActive":false,"IconValue":"","Value":1}]', 'ICON'=> 'camera-polaroid'], $vpos++, $this->ReadPropertyString('DeviceType') == 'Camera');
			
			if ($this->ReadPropertyString('DeviceType') == 'Camera') {
				$this->MaintainAction('micVolume', true);
				$this->SetValue('snapshot', 1);
				$this->MaintainAction('snapshot', true);
				
			}
			$MedienID = @$this->GetIDForIdent('Snapshot');			
			if (!$MedienID) {
				if ($this->ReadPropertyString('DeviceType') == 'Camera') {
					$MediaID = IPS_CreateMedia(1);
					IPS_SetParent($MediaID, $this->InstanceID);
					IPS_SetName($MediaID, $this->Translate('Snapshot'));
					IPS_SetIdent($MediaID, 'Snapshot');
				} 
			} else {
				if ($this->ReadPropertyString('DeviceType') !== 'Camera') {
					IPS_DeleteMedia($MedienID,true);
				}
			}

			$this->MaintainVariable( 'Temperature', $this->Translate( 'Temperature' ), 2, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'MAX'=>100,'MIN'=>-50,'USAGE_TYPE'=> 1,'DIGITS'=> 2, 'SUFFIX'=> ' °C' , 'ICON'=> 'temperature-list'], $vpos++, ($this->ReadPropertyBoolean("Temperature")&& $this->ReadPropertyString('DeviceType') == 'UP-Sense') );
			$this->MaintainVariable( 'Humidity', $this->Translate( 'Humidity' ), 2, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'MAX'=>100,'MIN'=>0,'USAGE_TYPE'=> 1,'DIGITS'=> 0, 'SUFFIX'=> ' % RH' , 'ICON'=> 'droplet-degree'], $vpos++, ($this->ReadPropertyBoolean("Humidity")&& $this->ReadPropertyString('DeviceType') == 'UP-Sense') );
			$this->MaintainVariable( 'Illuminance', $this->Translate( 'Illuminance' ), 2, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'MAX'=>100000,'MIN'=>0,'USAGE_TYPE'=> 1,'DIGITS'=> 0, 'SUFFIX'=> ' Lux' , 'ICON'=> 'sun'], $vpos++, ($this->ReadPropertyBoolean("Illuminance")&& $this->ReadPropertyString('DeviceType') == 'UP-Sense') );
			
			if ($this->ReadPropertyString('DeviceType') == 'Camera') {
				$arrayStream = array();
				$streamQuality = array();			
				if ($this->ReadPropertyBoolean('StreamLow')) {
					$streamQuality[] = 'low';
				}
				if ($this->ReadPropertyBoolean('StreamMedium')) {
					$streamQuality[] = 'medium';
				}
				if ($this->ReadPropertyBoolean('StreamHigh')) {
					$streamQuality[] = 'high';
				}
				$arrayStream=array('qualities' => $streamQuality);
				$this->SendDebug("UnifiPDevice", "Stream Qualities: " . json_encode($arrayStream), 0);
				$this->Send('createStream', json_encode($arrayStream));
				
			} elseif ($this->ReadPropertyString('DeviceType') == 'UP-Sense') {
				$arraySettings = array();
				$arraySettings['temperatureSettings'] = ['isEnabled'=> $this->ReadPropertyBoolean('Temperature')];
				$arraySettings['humiditySettings'] = ['isEnabled'=> $this->ReadPropertyBoolean('Humidity')];
				$arraySettings['lightSettings'] = ['isEnabled'=> $this->ReadPropertyBoolean('Illuminance')];
				$arraySettings['motionSettings'] = ['isEnabled'=> $this->ReadPropertyBoolean('Motion')];
				$this->SendDebug("UnifiPDevice", "Settings: " . json_encode($arraySettings), 0);
				$this->Send('patchSettingSensor', json_encode($arraySettings));
			}			
			$TimerMS = $this->ReadPropertyInteger( 'Timer' ) * 1000;
			$this->SetTimerInterval( 'Collect Data', $TimerMS );
			if ( 0 == $TimerMS )
			{
				// instance inactive
				$this->SetStatus( 104 );
			} else {
				// instance active
				$this->SetStatus( 102 );
				$this->Send('getDeviceData',$this->ReadPropertyString('DeviceType'));
			}


			$this->MaintainVariable( 'State', $this->Translate( 'State' ), 3, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE'=> 0,'DIGITS'=> 0, 
																				'OPTIONS'=> '[{"Value":"CONNECTED","Caption":"'.$this->Translate( 'Connected' ).'","IconActive":false,"IconValue":"","ColorActive":true,"ColorValue":65280,"Color":-1,"ColorDisplay":65280},{"Value":"CONNECTING","Caption":"'.$this->Translate( 'Connecting' ).'","IconActive":false,"IconValue":"","ColorActive":true,"ColorValue":16776960,"Color":-1,"ColorDisplay":16776960},{"Value":"DISCONNECTED","Caption":"'.$this->Translate( 'Disconnected' ).'","IconActive":false,"IconValue":"","ColorActive":true,"ColorValue":16711680,"Color":-1,"ColorDisplay":16711680}]'
																				,'ICON'=> 'link'], $vpos++, 1 );


		}


		public function Send(string $api, string $param1): void
		{
			if ($this->HasActiveParent()) {
				$data=$this->SendDataToParent(json_encode(['DataID' => '{BBE44630-5AEE-27A0-7D2E-E1D2D776B83B}',
					'Api' => $api,
					'InstanceID' => $this->InstanceID,
					'Param1' => $param1,
					'ID' => $this->ReadPropertyString('ID')
					]));
				if (!$data) {
					$this->SendDebug("UnifiPDevice", "Send Data error: " . $api, 0);
					return;
				};
				switch($api) {
					case "getSnapshot":
						$array=unserialize($data);
						$this->SendDebug("UnifiPDevice", "Snapshot: " .json_encode($array), 0);
						$this->getSnapshot($array);
						$this->SetValue('snapshot', 1);
						$this->MaintainVariable( 'snapshot', $this->Translate( 'Snapshot' ), 1, [ 'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,'LAYOUT'=> 2, 'OPTIONS'=>'[{"Caption":"Snapshot","Color":65280,"IconActive":false,"IconValue":"","Value":1}]', 'ICON'=> 'camera-polaroid'], 0, $this->ReadPropertyString('DeviceType') == 'Camera');
						break;
					case "getDevices":
						$deviceData=unserialize($data);
						$this->SendDebug("UnifiPDevice", "Data: " . json_encode($deviceData), 0);
						$this->UpdateFormField("ID", "options", json_encode($deviceData));
						$this->SetBuffer("devices", json_encode($deviceData));
						break;
					case "createStream":
					case "getStreams":
						$streams=unserialize($data);
						$this->SendDebug("UnifiPDevice", "Data: " . json_encode($streams), 0);
						if ( is_array( $streams ) && isset( $streams ) ) {
							if (isset($streams['high'])) {
								$urlStream = $streams['high'];
								if (isset($urlStream) && !empty($urlStream)) {
									$this->SendDebug("UnifiPDevice", "Stream High URL: " . $urlStream, 0);
									if (!$this->ReadPropertyBoolean('StreamHigh')) {
										$this->UpdateFormField("StreamHigh", "value", true);										
									}
									$MedienID = @$this->GetIDForIdent('Stream_High');
									$this->SendDebug("UnifiPDevice", "Stream High: " . $MedienID, 0);
									if ($MedienID && $MedienID > 0) {
										IPS_SetMediaFile($MedienID, $urlStream, true);										
									} else {
										$MedienID = IPS_CreateMedia(3);
										IPS_SetParent($MedienID, $this->InstanceID);
										IPS_SetName($MedienID, 'Stream_High');
										IPS_SetIdent($MedienID, 'Stream_High');
										IPS_SetMediaFile($MedienID, $urlStream, true);
									}
								} else {
									$MedienID = @$this->GetIDForIdent('Stream_High');
									if ($MedienID > 0) {
										IPS_SetMediaFile($MedienID, $urlStream, true);
										IPS_DeleteMedia ($MedienID,true);
									}							
								}
							}
							if (isset($streams['medium'])) {
								$urlStream = $streams['medium'];
								if (isset($urlStream) && !empty($urlStream)) {
									$this->SendDebug("UnifiPDevice", "Stream Medium URL: " . $urlStream, 0);
									if (!$this->ReadPropertyBoolean('StreamMedium')) {
										$this->UpdateFormField("StreamMedium", "value", true);
									} 
									$MedienID = @$this->GetIDForIdent('Stream_Medium');
									$this->SendDebug("UnifiPDevice", "Stream Medium: " . $MedienID, 0);
									if ($MedienID && $MedienID > 0) {
										IPS_SetMediaFile($MedienID, $urlStream, true);										
									} else {
										$MedienID = IPS_CreateMedia(3);
										IPS_SetParent($MedienID, $this->InstanceID);
										IPS_SetName($MedienID, 'Stream_Medium');
										IPS_SetIdent($MedienID, 'Stream_Medium');
										IPS_SetMediaFile($MedienID, $urlStream, true);
									}
								} else {
									$MedienID = @$this->GetIDForIdent('Stream_Medium');
									if ($MedienID > 0) {
											IPS_SetMediaFile($MedienID, $urlStream, true);
											IPS_DeleteMedia ($MedienID,true);
									}
								}
							} 
							if (isset($streams['low'])) {
								$urlStream = $streams['low'];
								if (isset($urlStream) && !empty($urlStream)) {
									if (!$this->ReadPropertyBoolean('StreamLow')) {
										$this->UpdateFormField("StreamLow", "value", true);
									} 
									$MedienID = @$this->GetIDForIdent('Stream_Low');
									$this->SendDebug("UnifiPDevice", "Stream Low: " . $MedienID, 0);
									if ($MedienID && $MedienID > 0) {
										IPS_SetMediaFile($MedienID, $urlStream, true);										
									}	else {
										$MedienID = IPS_CreateMedia(3);
										IPS_SetParent($MedienID, $this->InstanceID);
										IPS_SetName($MedienID, 'Stream_Low');
										IPS_SetIdent($MedienID, 'Stream_Low');
										IPS_SetMediaFile($MedienID, $urlStream, true);
									}
								} else {
									$MedienID = @$this->GetIDForIdent('Stream_Low');
									if ($MedienID > 0) {
											IPS_SetMediaFile($MedienID, $urlStream, true);
											IPS_DeleteMedia ($MedienID,true);
									}
								}
							}
						}
						break;					
					case "getDeviceData":
						$deviceData = unserialize($data);
						$this->SendDebug("UnifiPDevice", "Device Data: " . json_encode($deviceData), 0);
						if ($this->ReadPropertyString('DeviceType') == 'Camera') {
							$this->SetValue('micEnabled', $deviceData['isMicEnabled'] ?? false);
							$this->SetValue('micVolume', $deviceData['micVolume'] ?? 0);
							$lcdMessageText = '';
                            if (isset($deviceData['lcdMessage']) && is_array($deviceData['lcdMessage'])) {
                                $lcdMessageText = $deviceData['lcdMessage']['text'] ?? '';
                            }
                            if ($lcdMessageText !== '') {
                                $this->MaintainVariable('lcdMessage',$this->Translate('LCD Message'),3,['PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION,'USAGE_TYPE' => 0,'ICON' => 'text'], 201, true);
								$this->MaintainAction('lcdMessage', true);
                                $this->SetValue('lcdMessage', (string)$lcdMessageText);
                            }
						} elseif ($this->ReadPropertyString('DeviceType') == 'UP-Sense') {
							$this->SetValue('Temperature', $deviceData['stats']['temperature']['value'] ?? 0);
							$this->SetValue('Humidity', $deviceData['stats']['humidity']['value'] ?? 0);
							$this->SetValue('Illuminance', $deviceData['stats']['light']['value'] ?? 0);							
							if (!empty($deviceData['batteryStatus']['percentage'])) {
								$this->MaintainVariable('batteryStatus', $this->Translate('Battery Status'), 1, ['PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'MAX' => 100, 'MIN' => 0, 'USAGE_TYPE' => 1, 'DIGITS' => 0, 'SUFFIX' => ' %', 'ICON' => 'battery-bolt'], 200, 1);
								$this->SetValue('batteryStatus', $deviceData['batteryStatus']['percentage']);
							}
						}
						if (isset($deviceData['name'])) {
							$this->SetValue('Name',$deviceData['name']);
							$summary=$deviceData['name'];
						}
						if (isset($deviceData['modelKey'])) {
							$this->SetValue('Model',$deviceData['modelKey']);
							$summary=$summary.' - ' .$deviceData['modelKey'];
						}
						if (!empty($summary)) {
							$this->SetSummary($summary);
						}
						if (isset($deviceData['state'])) {
							$this->SetValue('State',$deviceData['state']);
							$summary=$summary.' - ' .$deviceData['modelKey'];
						}
						if (isset($deviceData['id']) && $this->ReadPropertyBoolean("IDAnzeigen")) {
							$this->SetValue('ID',$deviceData['id']);
						}
					break;
					default:
						$this->SendDebug("UnifiPDevice", "Unknown API: " . $api, 0);
						break;
				}
			}
		}

		private function getSnapshot(array $array):bool {
			if (!IPS_SemaphoreEnter("UnifiProtectAPI", 500)) {
				$this->SendDebug("UnifiPDevice", "Semaphore Timeout - Request abgebrochen", 0);
				return false;
			}

			try {
				$starttime=microtime(true);
				$ch = curl_init();
				curl_setopt( $ch, CURLOPT_URL, $array['url'] );
				curl_setopt( $ch, CURLOPT_HTTPGET, true );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
				curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'X-API-KEY:'.$array['apikey'] ) );
				curl_setopt( $ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1' );
				// Timeout-Einstellungen
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);  
				$RawData = curl_exec($ch);
				$curl_error = curl_error($ch);		
				if ($RawData === false) {
					// Handle error
					$this->SendDebug("UnifiPDevice", "Curl error: " . $curl_error, 0);
					//$this->SetStatus( 201 ); // Set status to error
					return false;
				}
				// Fehler-JSON abfangen
				$json = json_decode($RawData, true);
				if (is_array($json) && isset($json['error'])) {
					$this->SendDebug("UnifiPDevice", "Snapshot error: " . $json['error'], 0);
					//$this->SetStatus(201);
					return false;
				}
				$this->SendDebug("UnifiPDevice", "Got Snapshot: " . $RawData, 0);
				$MedienID = $this->GetIDForIdent('Snapshot');
				$this->SendDebug("UnifiPDevice", "Snapshot media file: " . $MedienID, 0);
				if ($MedienID && $MedienID > 0) {
					if (isset($RawData) && !empty($RawData)) {
						IPS_SetMediaFile($MedienID, 'Snapshot_'.$this->InstanceID.'.jpeg', FALSE);
						IPS_SetMediaContent($MedienID, base64_encode($RawData));
					} else {
						return false;
					}
				} else {
					return false;
				}
				if(microtime(true)-$starttime<=0.05){
					usleep((int)(50-(microtime(true)-$starttime*1000000)));
				}
				return true;
			} finally {
				// Semaphore freigeben
				IPS_SemaphoreLeave("UnifiProtectAPI");
			}
		}

		public function getData():string {
			$this->Send("getDeviceData",$this->ReadPropertyString('DeviceType'));
			return "";
		}

		public function GetConfigurationForm():string{			
			if ($this->HasActiveParent()) {
				$this->Send("getDevices",$this->ReadPropertyString('DeviceType'));
			}	
			$arrayStatus = array();
			$arrayStatus[] = array( 'code' => 102, 'icon' => 'active', 'caption' => $this->Translate('Instanz ist aktiv') );
			$arrayStatus[] = array( 'code' => 104, 'icon' => 'inactive', 'caption' => $this->Translate('Instanz ist inaktiv') );

			$arrayElements = array();
			$arrayElements[] = array( 'type' => 'Label','bold' => true, 'label' => $this->Translate('UniFi Protect Device')); 
			$arrayElements[] = array( 'type' => 'NumberSpinner', 'name' => 'Timer', 'caption' => $this->Translate('Timer (s) -> 0=Off'));
			$arrayOptions[] = array( 'caption' => 'Camera', 'value' => 'Camera' );
			$arrayOptions[] = array( 'caption' => 'All-In-One Sensor', 'value' => 'UP-Sense' );
			$arrayOptions[] = array( 'caption' => 'Chime', 'value' => 'Chime' );
			$arrayOptions[] = array( 'caption' => 'Light', 'value' => 'Light' );
			$arrayElements[] = array( 'type' => 'Select', 'name' => 'DeviceType', 'caption' => $this->Translate('Device Type'), 'options' => $arrayOptions );

			unset($arrayOptions);
			$Bufferdata = $this->GetBuffer("devices");
			if ($Bufferdata=="") {
				$arrayOptions[] = array( 'caption' => 'Test', 'value' => '' );
			} else {
				$arrayOptions=json_decode($Bufferdata);
			}		
			$arrayElements[] = array( 'type' => 'Select', 'name' => 'ID', 'caption' => $this->Translate('Device ID'), 'options' => $arrayOptions );
			unset($arrayOptions);
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'IDAnzeigen', 'width' => '220px','caption' => $this->Translate('Show ID') );			
			$arrayElements[] = array( 'type' => 'RowLayout',  'items' => $arrayOptions );
			unset($arrayOptions);
			if ($this->ReadPropertyString('DeviceType') == 'Camera') {
				$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'StreamLow','width' => '180px', 'caption' => $this->Translate('Stream Low') );
				$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'StreamMedium','width' => '180px', 'caption' => $this->Translate('Stream Medium') );
				$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'StreamHigh','width' => '180px', 'caption' => $this->Translate('Stream High') );
				$arrayElements[] = array( 'type' => 'RowLayout',  'items' => $arrayOptions );
			}
			if ($this->ReadPropertyString('DeviceType') == 'UP-Sense') {
				$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'Temperature','width' => '180px', 'caption' => $this->Translate('Temperature') );
				$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'Humidity','width' => '180px', 'caption' => $this->Translate('Humidity') );
				$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'Illuminance','width' => '180px', 'caption' => $this->Translate('Illuminance') );				
				$arrayElements[] = array( 'type' => 'RowLayout',  'items' => $arrayOptions );
				$arrayElements[] = array( 'type' => 'CheckBox', 'name' => 'Motion','width' => 'auto', 'caption' => $this->Translate('Motion (Motion shows up in Protect Events)') );
			}

			$arrayActions = array();
			unset($arrayOptions);
			$arrayOptions[] = array( 'type' => 'Button', 'label' => $this->Translate('Get Devices'), 'width' => '220px','onClick' => 'UNIFIPDV_Send($id,"getDevices","'.$this->ReadPropertyString('DeviceType').'");' );
			if ($this->ReadPropertyString('DeviceType') == 'Camera') {
				$arrayOptions[] = array( 'type' => 'Button', 'label' => $this->Translate('Get Streams'),'width' => '220px', 'onClick' => 'UNIFIPDV_Send($id,"getStreams","");' );
				$arrayOptions[] = array( 'type' => 'Button', 'label' => $this->Translate('Get Snapshot'), 'width' => '220px','onClick' => 'UNIFIPDV_Send($id,"getSnapshot","");' );
			}
			$arrayOptions[] = array( 'type' => 'Button', 'label' => $this->Translate('Get Device Data'), 'width' => '220px','onClick' => 'UNIFIPDV_getData($id);' );			
			$arrayActions[] = array( 'type' => 'RowLayout',  'items' => $arrayOptions	 );
			return JSON_encode( array( 'status' => $arrayStatus, 'elements' => $arrayElements, 'actions' => $arrayActions ) );
	    }
		
		public function RequestAction($Ident, $Value): void {
			switch($Ident) {
				case "micVolume":
					//Hier würde normalerweise eine Aktion z.B. das Schalten ausgeführt werden
					//Ausgaben über 'echo' werden an die Visualisierung zurückgeleitet

					$this->SendDebug("UnifiPDevice", "micVolume: New state is $Value", 0);
					$this->Send('patchSettingCamera', json_encode(array('micVolume' => $Value)));

					//Neuen Wert in die Statusvariable schreiben
					$this->SetValue($Ident, $Value);
					break;
				case 'snapshot':
					$idIdent=$this->GetIDForIdent($Ident);
					//Hier würde normalerweise eine Aktion z.B. das Schalten ausgeführt werden
					if ($Value == 1) {
						$this->MaintainVariable( 'snapshot', $this->Translate( 'Snapshot' ), 1, [ 'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,'LAYOUT'=> 2, 'OPTIONS'=>'[{"Caption":"Erzeuge Snapshot...","Color":16711680,"IconActive":false,"IconValue":"","Value":0}]', 'ICON'=> 'camera-polaroid'], 0, $this->ReadPropertyString('DeviceType') == 'Camera');
						$this->SetValue($Ident, 0);
						$this->Send('getSnapshot','');
						$this->SendDebug("UnifiPDevice", "Get Snapshot...", 0);						
					} else {
						$varTmp=IPS_GetVariable($idIdent);
						if ((time()-$varTmp['VariableChanged']) > 10) {
							$this->SetValue($Ident, 1);
							$this->SendDebug("UnifiPDevice", "Error waiting on snapshot-", 0);
							return;
						}
						$this->SetValue($Ident, 0);
						$this->SendDebug("UnifiPDevice", "Already waiting on Snapshot: ".$this->GetValue($Ident), 0);
					}
					break;
				case 'lcdMessage':
                    $this->SendDebug('UnifiPDevice', 'lcdMessage: New value is '.$Value, 0);
                    $payload = [
                        'lcdMessage' => [
                            'type' => 'CUSTOM_MESSAGE',
                            'text' => (string)$Value
                        ]
                    ];
                    $this->Send('patchSettingCamera', json_encode($payload));
                    $this->SetValue($Ident, (string)$Value);
                    break;
				default:
					throw new Exception("Invalid Ident");
			}
			
		}
	}