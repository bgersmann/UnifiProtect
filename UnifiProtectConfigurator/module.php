<?php

declare(strict_types=1);
	class UnifiProtectConfigurator extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			$this->ConnectParent('{3F49B3E6-093C-40FA-661C-3D31BE37AEA3}');
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

		public function Send(string $api, string $param1)
		{
			if ($this->HasActiveParent()) {
				$data=$this->SendDataToParent(json_encode(['DataID' => '{BBE44630-5AEE-27A0-7D2E-E1D2D776B83B}',
					'Api' => $api,
					'InstanceID' => $this->InstanceID,
					'Param1' => $param1
					]));
				if (!$data) {
					$this->SendDebug("UnifiPDevice", "Send Data error: " . $api, 0);
					return;
				};
				switch($api) {
					case "getDevicesConfig":
						$deviceData=json_encode(unserialize($data));
						$this->SendDebug("UnifiPCG", "getDevicesConfig: " .  $deviceData, 0);
						$this->UpdateFormField("UnifiDevices", "values", $deviceData);
						$this->SetBuffer("configurator", $deviceData);
						break;
					default:
						$this->SendDebug("UnifiPCG", "Unknown API: " . $api, 0);
						break;
				}
			}			
		}	


		public function GetConfigurationForm()
		{
			if ($this->HasActiveParent()) {
				$this->Send("getDevicesConfig","");
			}
			$arrayOptions[] = array( 'caption' => 'default', 'value' => 'default' );
			
			$arrayStatus = array();
			$arrayStatus[] = array( 'code' => 102, 'icon' => 'active', 'caption' => $this->Translate('Instanz ist aktiv') );
			$arrayStatus[] = array( 'code' => 104, 'icon' => 'inactive', 'caption' => $this->Translate('Instanz ist inaktiv') );
			$arraySort = array();
			#$arraySort = array( 'column' => 'Name', 'direction' => 'ascending' );

			$arrayColumns = array();
			$arrayColumns[] = array( 'caption' => 'Name', 'name' => 'Name', 'width' => 'auto', 'add' => '' );
			$arrayColumns[] = array( 'caption' => 'Typ', 'name' => 'Type', 'width' => '200px', 'add' => '' );
			$arrayColumns[] = array( 'caption' => 'State', 'name' => 'State', 'width' => '200px', 'add' => '' );	
			$arrayColumns[] = array( 'caption' => 'ID', 'name' => 'ID', 'width' => '300px', 'add' => '' );
			$arrayValues = array();

			$Bufferdata = $this->GetBuffer("configurator");
			if ($Bufferdata=="") {
				$arrayValues[] = array( 'caption' => 'Test', 'value' => '' );
			} else {
				$arrayValues=json_decode($Bufferdata);
			}
			$arrayElements = array();
			$arrayElements[] = array( 'type' => 'Label','bold' => true, 'label' => $this->Translate('UniFi Protect Device Configurator'));
			$arrayElements[] = array( 'type' => 'Configurator', 'name' => $this->Translate('UnifiDevices'), 'caption' => 'Unifi Protect Devices', 'rowCount' => 10, 'delete' => false, 'sort' => $arraySort, 'columns' => $arrayColumns, 'values' => $arrayValues );

			$arrayActions = array();
			$arrayActions[] = array( 'type' => 'Button', 'label' => $this->Translate('Get Devices'), 'onClick' => 'UNIFIPCG_Send($id,"getDevicesConfig","");');

			return JSON_encode( array( 'status' => $arrayStatus, 'elements' => $arrayElements, 'actions' => $arrayActions ) );

		}
	}