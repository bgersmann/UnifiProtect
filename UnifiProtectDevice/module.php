<?php

declare(strict_types=1);
	class UnifiProtectDevice extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			$this->ConnectParent('{3F49B3E6-093C-40FA-661C-3D31BE37AEA3}');
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
			$this->MaintainVariable( 'Name', $this->Translate( 'Name' ), 3, '', $vpos++, 1 );
			$this->MaintainVariable( 'ID', $this->Translate( 'ID' ), 3, '', $vpos++, $this->ReadPropertyBoolean("IDAnzeigen") );
			$this->MaintainVariable( 'Model', $this->Translate( 'Model' ), 3, '', $vpos++, 1 );
			
			$this->MaintainVariable( 'micEnabled', $this->Translate( 'Is Microphone enabled' ), 0, '', $vpos++, $this->ReadPropertyString('DeviceType') == 'Camera');
			$this->MaintainVariable( 'micVolume', $this->Translate( 'Microphone Volume' ), 1, [ 'PRESENTATION' => VARIABLE_PRESENTATION_SLIDER, 'MAX'=>100,'MIN'=>1,'STEP_SIZE'=>1,'USAGE_TYPE'=> 2, 'SUFFIX'=> ' %' , 'ICON'=> 'volume-high'], $vpos++, $this->ReadPropertyString('DeviceType') == 'Camera');
			$this->MaintainVariable( 'snapshot', $this->Translate( 'Snapshot' ), 1, [ 'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,'LAYOUT'=> 2, 'OPTIONS'=>'[{"Caption":"Snapshot","Color":65280,"IconActive":false,"IconValue":"","Value":1}]', 'ICON'=> 'camera-polaroid'], $vpos++, $this->ReadPropertyString('DeviceType') == 'Camera');
			if ($this->ReadPropertyString('DeviceType') == 'Camera') {
				$this->MaintainAction('micVolume', true);
				$this->MaintainAction('snapshot', true);
			}
			$MedienID = @IPS_GetObjectIDByIdent('Snapshot', $this->InstanceID);			
			if ($MedienID == 0) {
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

			$this->MaintainVariable( 'Temperature', $this->Translate( 'Temperature' ), 2, '', $vpos++, ($this->ReadPropertyBoolean("Temperature")&& $this->ReadPropertyString('DeviceType') == 'UP-Sense') );
			$this->MaintainVariable( 'Humidity', $this->Translate( 'Humidity' ), 2, '', $vpos++, ($this->ReadPropertyBoolean("Humidity")&& $this->ReadPropertyString('DeviceType') == 'UP-Sense') );
			$this->MaintainVariable( 'Illuminance', $this->Translate( 'Illuminance' ), 2, '', $vpos++, ($this->ReadPropertyBoolean("Illuminance")&& $this->ReadPropertyString('DeviceType') == 'UP-Sense') );
			
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


		}


		public function Send(string $api, string $param1)
		{
			if ($this->HasActiveParent()) {
				$this->SendDataToParent(json_encode(['DataID' => '{BBE44630-5AEE-27A0-7D2E-E1D2D776B83B}',
					'Api' => $api,
					'InstanceID' => $this->InstanceID,
					'Param1' => $param1
					]));
			}			
		}

		public function ReceiveData($JSONString)
		{
			$data = json_decode($JSONString,true);
			#$this->SendDebug("UnifiPDevice", "Data: " . $JSONString, 0);
			If ($data['id']== $this->InstanceID) {
				//IPS_LogMessage('UNIFIDV-'.$this->InstanceID,utf8_decode($data['data']));				
				switch($data['Api']) {
					case "getDevices":
						$this->UpdateFormField("ID", "options", $data['data']);
						$this->SetBuffer("devices", $data['data']);
						break;
					case "createStream":
					case "getStreams":
						$streams = json_decode($data['data'], true);
						$this->SendDebug("UnifiPDevice", "Data: " . json_encode($streams), 0);
						if ( is_array( $streams ) && isset( $streams ) ) {
							if (isset($streams['high'])) {
								$urlStream = $streams['high'];
								if (isset($urlStream)) {
									if (!$this->ReadPropertyBoolean('StreamHigh')) {
										$this->UpdateFormField("StreamHigh", "value", true);
									} 
									$MedienID = @IPS_GetObjectIDByIdent('Stream_High', $this->InstanceID);
									$this->SendDebug("UnifiPDevice", "Stream High: " . $MedienID, 0);
									if ($MedienID > 0) {
										IPS_SetMediaFile($MedienID, $urlStream, true);
									} else {
										$MedienID = IPS_CreateMedia(3);
										IPS_SetParent($MedienID, $this->InstanceID);
										IPS_SetName($MedienID, 'Stream_High');
										IPS_SetIdent($MedienID, 'Stream_High');
										IPS_SetMediaFile($MedienID, $urlStream, true);
									}
								}
							} else {
								$MedienID = @IPS_GetObjectIDByIdent('Stream_High', $this->InstanceID);
								if ($MedienID > 0) {
										IPS_SetMediaFile($MedienID, $urlStream, true);
										IPS_DeleteMedia ($MedienID,true);
								}
							}
							if (isset($streams['medium'])) {
								$urlStream = $streams['medium'];
								if (isset($urlStream) && !empty($urlStream)) {
									if (!$this->ReadPropertyBoolean('StreamMedium')) {
										$this->UpdateFormField("StreamMedium", "value", true);
									} 
									$MedienID = @IPS_GetObjectIDByIdent('Stream_Medium', $this->InstanceID);
									$this->SendDebug("UnifiPDevice", "Stream Medium: " . $MedienID, 0);
									if ($MedienID > 0) {
										IPS_SetMediaFile($MedienID, $urlStream, true);										
									} else {
										$MedienID = IPS_CreateMedia(3);
										IPS_SetParent($MedienID, $this->InstanceID);
										IPS_SetName($MedienID, 'Stream_Medium');
										IPS_SetIdent($MedienID, 'Stream_Medium');
										IPS_SetMediaFile($MedienID, $urlStream, true);
									}
								}
							} else {
								$MedienID = @IPS_GetObjectIDByIdent('Stream_Medium', $this->InstanceID);
								if ($MedienID > 0) {
										IPS_SetMediaFile($MedienID, $urlStream, true);
										IPS_DeleteMedia ($MedienID,true);
								}
							}
							if (isset($streams['low'])) {
								$urlStream = $streams['low'];
								if (isset($urlStream) && !empty($urlStream)) {
									if (!$this->ReadPropertyBoolean('StreamLow')) {
										$this->UpdateFormField("StreamLow", "value", true);
									} 
									$MedienID = @IPS_GetObjectIDByIdent('Stream_Low', $this->InstanceID);
									$this->SendDebug("UnifiPDevice", "Stream Low: " . $MedienID, 0);
									if ($MedienID > 0) {
										IPS_SetMediaFile($MedienID, $urlStream, true);										
									}	else {
										$MedienID = IPS_CreateMedia(3);
										IPS_SetParent($MedienID, $this->InstanceID);
										IPS_SetName($MedienID, 'Stream_Low');
										IPS_SetIdent($MedienID, 'Stream_Low');
										IPS_SetMediaFile($MedienID, $urlStream, true);
									}
								}
							}else {
								$MedienID = @IPS_GetObjectIDByIdent('Stream_Low', $this->InstanceID);
								if ($MedienID > 0) {
										IPS_SetMediaFile($MedienID, $urlStream, true);
										IPS_DeleteMedia ($MedienID,true);
								}
							}
						}
						break;
					case "createStream":
						$stream = json_decode($data['data'], true);
						$this->SendDebug("UnifiPDevice", "Stream: " . json_encode($stream), 0);						
						break;
					case "getSnapshot":
						$MedienID = IPS_GetObjectIDByIdent('Snapshot', $this->InstanceID);
						if ($MedienID > 0) {
							$snapshot = $data['data'];
							if (isset($snapshot)) {
								IPS_SetMediaFile($MedienID, 'Snapshot_'.$this->InstanceID.'.jpeg', FALSE);
								IPS_SetMediaContent($MedienID, $snapshot);
							}
						}
					break;
					case "getDeviceData":
						$deviceData = json_decode($data['data'], true);
						$this->SendDebug("UnifiPDevice", "Device Data: " . json_encode($deviceData), 0);
						if ($this->ReadPropertyString('DeviceType') == 'Camera') {
							$this->SetValue('micEnabled', $deviceData['isMicEnabled'] ?? false);
							$this->SetValue('micVolume', $deviceData['micVolume'] ?? 0);
						} elseif ($this->ReadPropertyString('DeviceType') == 'UP-Sense') {
							$this->SetValue('Temperature', $deviceData['stats']['temperature']['value'] ?? 0);
							$this->SetValue('Humidity', $deviceData['stats']['humidity']['value'] ?? 0);
							$this->SetValue('Illuminance', $deviceData['stats']['light']['value'] ?? 0);							
							if (!empty($deviceData['batteryStatus']['percentage'])) {
								$this->MaintainVariable( 'batteryStatus', $this->Translate( 'Battery Status' ), 1, '', 200, 1 );
								$this->SetValue('batteryStatus', $deviceData['batteryStatus']['percentage']);
							}
						}
						if (isset($deviceData['name'])) {
							$this->SetValue('Name',$deviceData['name']);
						}
						if (isset($deviceData['modelKey'])) {
							$this->SetValue('Model',$deviceData['modelKey']);
						}
						if (isset($deviceData['id']) && $this->ReadPropertyBoolean("IDAnzeigen")) {
							$this->SetValue('ID',$deviceData['id']);
						}
					break;
				}
			}			
		}

		public function getData():string {
			$this->Send("getDeviceData",$this->ReadPropertyString('DeviceType'));
			return "";
		}

		public function GetConfigurationForm(){			
			if ($this->HasActiveParent()) {
				$this->Send("getDevices",$this->ReadPropertyString('DeviceType'));
			}	
			$arrayStatus = array();
			$arrayStatus[] = array( 'code' => 102, 'icon' => 'active', 'caption' => 'Instanz ist aktiv' );
			$arrayStatus[] = array( 'code' => 104, 'icon' => 'inactive', 'caption' => 'Instanz ist inaktiv' );

			$arrayElements = array();
			$arrayElements[] = array( 'type' => 'Label', 'label' => $this->Translate('UniFi Protect Device')); 
			$arrayElements[] = array( 'type' => 'NumberSpinner', 'name' => 'Timer', 'caption' => 'Timer (s) -> 0=Off' );
			$arrayOptions[] = array( 'caption' => 'Camera', 'value' => 'Camera' );
			$arrayOptions[] = array( 'caption' => 'All-In-One Sensor', 'value' => 'UP-Sense' );
			$arrayElements[] = array( 'type' => 'Select', 'name' => 'DeviceType', 'caption' => 'Device Type', 'options' => $arrayOptions );

			unset($arrayOptions);
			$Bufferdata = $this->GetBuffer("devices");
			if ($Bufferdata=="") {
				$arrayOptions[] = array( 'caption' => 'Test', 'value' => '' );
			} else {
				$arrayOptions=json_decode($Bufferdata);
			}		
			$arrayElements[] = array( 'type' => 'Select', 'name' => 'ID', 'caption' => 'Device ID', 'options' => $arrayOptions );
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
			$arrayOptions[] = array( 'type' => 'Button', 'label' => 'Devices Holen', 'width' => '220px','onClick' => 'UNIFIPDV_Send($id,"getDevices","'.$this->ReadPropertyString('DeviceType').'");' );
			if ($this->ReadPropertyString('DeviceType') == 'Camera') {
				$arrayOptions[] = array( 'type' => 'Button', 'label' => 'Streams auslesen','width' => '220px', 'onClick' => 'UNIFIPDV_Send($id,"getStreams","");' );
				$arrayOptions[] = array( 'type' => 'Button', 'label' => 'Snapshot holen', 'width' => '220px','onClick' => 'UNIFIPDV_Send($id,"getSnapshot","");' );
			}
			$arrayOptions[] = array( 'type' => 'Button', 'label' => 'Daten holen', 'width' => '220px','onClick' => 'UNIFIPDV_getData($id);' );			
			$arrayActions[] = array( 'type' => 'RowLayout',  'items' => $arrayOptions	 );
			return JSON_encode( array( 'status' => $arrayStatus, 'elements' => $arrayElements, 'actions' => $arrayActions ) );
	    }
		public function RequestAction($Ident, $Value) {
			switch($Ident) {
				case "micVolume":
					//Hier würde normalerweise eine Aktion z.B. das Schalten ausgeführt werden
					//Ausgaben über 'echo' werden an die Visualisierung zurückgeleitet

					$this->SendDebug("UnifiPDevice", "micVolume: New state is $Value", 0);
					$this->Send('patchSettingCamera', json_encode(array('micVolume' => $Value)));

					//Neuen Wert in die Statusvariable schreiben
					SetValue($this->GetIDForIdent($Ident), $Value);
					break;
				case 'snapshot':
					//Hier würde normalerweise eine Aktion z.B. das Schalten ausgeführt werden
					$this->SendDebug("UnifiPDevice", "snapshot: New state is $Value", 0);
					$this->Send('getSnapshot','');
					SetValue($this->GetIDForIdent($Ident), $Value);
					break;
				default:
					throw new Exception("Invalid Ident");
			}
			
		}
	}