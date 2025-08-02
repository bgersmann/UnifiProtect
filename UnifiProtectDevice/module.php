<?php

declare(strict_types=1);
	class UnifiProtectDevice extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			$this->ConnectParent('{3F49B3E6-093C-40FA-661C-3D31BE37AEA3}');
			$this->RegisterPropertyString( 'ID', '' );
			$this->RegisterPropertyBoolean( 'StreamLow', false );
			$this->RegisterPropertyBoolean( 'StreamMedium', false );
			$this->RegisterPropertyBoolean( 'StreamHigh', false );
			$this->RegisterPropertyInteger( 'Timer', 0 );
			$this->RegisterPropertyBoolean( 'IDAnzeigen', false );

			$MedienID = @IPS_GetObjectIDByIdent('Snapshot', $this->InstanceID);
            if ($MedienID == 0) {
                $MediaID = IPS_CreateMedia(1);
                IPS_SetParent($MediaID, $this->InstanceID);
                IPS_SetName($MediaID, $this->Translate('Snapshot'));
				IPS_SetIdent($MediaID, 'Snapshot');
            }

			
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
			#$this->MaintainVariable( 'applicationVersion', $this->Translate( 'Application Version' ), 3, '', $vpos++, $this->ReadPropertyBoolean("applicationVersion") );
			$this->MaintainVariable( 'Name', $this->Translate( 'Name' ), 3, '', $vpos++, 1 );
			$this->MaintainVariable( 'ID', $this->Translate( 'ID' ), 3, '', $vpos++, $this->ReadPropertyBoolean("IDAnzeigen") );
			$this->MaintainVariable( 'Model', $this->Translate( 'Model' ), 3, '', $vpos++, 1 );
			$this->MaintainVariable( 'micEnabled', $this->Translate( 'Is Microphone enabled' ), 0, '', $vpos++, 1 );
			$this->MaintainVariable( 'micVolume', $this->Translate( 'Microphone Volume' ), 1, [ 'PRESENTATION' => VARIABLE_PRESENTATION_SLIDER, 'MAX'=>100,'MIN'=>1,'STEP_SIZE'=>1,'USAGE_TYPE'=> 2, 'SUFFIX'=> ' %' , 'ICON'=> 'volume-high'], $vpos++, 1 );
			$this->MaintainAction('micVolume', true);
			$this->MaintainVariable( 'snapshot', $this->Translate( 'Snapshot' ), 1, [ 'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,'LAYOUT'=> 2, 'OPTIONS'=>'[{"Caption":"Snapshot","Color":65280,"IconActive":false,"IconValue":"","Value":1}]', 'ICON'=> 'camera-polaroid'], $vpos++, 1 );
			$this->MaintainAction('snapshot', true);

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
					case "getCameras":
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
					case "getCameraData":
						$cameraData = json_decode($data['data'], true);
						$this->SendDebug("UnifiPDevice", "Camera Data: " . json_encode($cameraData), 0);
						if (isset($cameraData['name'])) {
							$this->SetValue('Name',$cameraData['name']);
						}						
						if (isset($cameraData['modelKey'])) {
							$this->SetValue('Model',$cameraData['modelKey']);
						}
						if (isset($cameraData['id']) && $this->ReadPropertyBoolean("IDAnzeigen")) {
							$this->SetValue('ID',$cameraData['id']);
						}
						if (isset($cameraData['isMicEnabled'])) {
							$this->SetValue('micEnabled',$cameraData['isMicEnabled']);
						} else {
							$this->SetValue('micEnabled',false);
						}
						if (isset($cameraData['micVolume'])) {
							$this->SetValue('micVolume',$cameraData['micVolume']);
						} else {
							$this->SetValue('micVolume',0);
						}
					break;
				}
			}			
		}

		public function GetConfigurationForm(){			
			if ($this->HasActiveParent()) {
				$this->Send("getCameras",'');
			}	
			$arrayStatus = array();
			$arrayStatus[] = array( 'code' => 102, 'icon' => 'active', 'caption' => 'Instanz ist aktiv' );

			$arrayElements = array();
			$arrayElements[] = array( 'type' => 'Label', 'label' => $this->Translate('UniFi Protect Device')); 
			$arrayElements[] = array( 'type' => 'NumberSpinner', 'name' => 'Timer', 'caption' => 'Timer (s) -> 0=Off' );

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
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'StreamLow','width' => '180px', 'caption' => $this->Translate('Stream Low') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'StreamMedium','width' => '180px', 'caption' => $this->Translate('Stream Medium') );
			$arrayOptions[] = array( 'type' => 'CheckBox', 'name' => 'StreamHigh','width' => '180px', 'caption' => $this->Translate('Stream High') );
			$arrayElements[] = array( 'type' => 'RowLayout',  'items' => $arrayOptions );

			$arrayActions = array();
			unset($arrayOptions);
			$arrayOptions[] = array( 'type' => 'Button', 'label' => 'Devices Holen', 'width' => '220px','onClick' => 'UNIFIPDV_Send($id,"getCameras","");' );
			$arrayOptions[] = array( 'type' => 'Button', 'label' => 'Streams auslesen','width' => '220px', 'onClick' => 'UNIFIPDV_Send($id,"getStreams","");' );
			$arrayOptions[] = array( 'type' => 'Button', 'label' => 'Snapshot holen', 'width' => '220px','onClick' => 'UNIFIPDV_Send($id,"getSnapshot","");' );			
			$arrayOptions[] = array( 'type' => 'Button', 'label' => 'Daten holen', 'width' => '220px','onClick' => 'UNIFIPDV_Send($id,"getCameraData","");' );			
			$arrayActions[] = array( 'type' => 'RowLayout',  'items' => $arrayOptions	 );
			return JSON_encode( array( 'status' => $arrayStatus, 'elements' => $arrayElements, 'actions' => $arrayActions ) );
	    }
		public function RequestAction($Ident, $Value) {
			switch($Ident) {
				case "micVolume":
					//Hier würde normalerweise eine Aktion z.B. das Schalten ausgeführt werden
					//Ausgaben über 'echo' werden an die Visualisierung zurückgeleitet

					$this->SendDebug("UnifiPDevice", "micVolume: New state is $Value", 0);
					$this->Send('patchSetting', json_encode(array('micVolume' => $Value)));

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