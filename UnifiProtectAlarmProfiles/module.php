<?php

declare(strict_types=1);
	class UnifiProtectAlarmProfiles extends IPSModuleStrict
	{
		public function Create():void
		{
			//Never delete this line!
			parent::Create();
			$this->RegisterPropertyInteger( 'Timer', 0 );
			$this->RegisterAttributeString( 'Profiles', '[]' );
			$this->RegisterTimer( 'Update Profiles', 0, "UNIFIPAP_getProfiles(\$_IPS['TARGET']);" );
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

			$profiles = $this->getCachedProfiles();
			$this->MaintainVariable( 'CurrentProfile', $this->Translate( 'Alarm Profile' ), 3, $this->buildProfilePresentation( $profiles ), $vpos++, true );
			$this->MaintainAction( 'CurrentProfile', true );

			$this->MaintainVariable( 'Armed', $this->Translate( 'Alarm armed' ), 0, $this->buildArmedPresentation(), $vpos++, true );
			$this->MaintainAction( 'Armed', true );

			$TimerMS = $this->ReadPropertyInteger( 'Timer' ) * 1000;
			$this->SetTimerInterval( 'Update Profiles', $TimerMS );

			if ( $this->HasActiveParent() ) {
				// instance active
				$this->SetStatus( 102 );
				$this->Send( 'getAlarmProfiles', '' );
			} else {
				// instance inactive
				$this->SetStatus( 104 );
			}
		}

		public function Send(string $api, string $param1): void
		{
			if ( !$this->HasActiveParent() ) {
				$this->SendDebug( 'UnifiPAlarmProfiles', 'No active parent (Gateway) configured.', 0 );
				return;
			}
			$data = $this->SendDataToParent( json_encode( [ 'DataID' => '{BBE44630-5AEE-27A0-7D2E-E1D2D776B83B}',
				'Api'        => $api,
				'InstanceID' => $this->InstanceID,
				'Param1'     => $param1,
				'ID'         => ''
				] ) );
			if ( $data === false ) {
				$this->SendDebug( 'UnifiPAlarmProfiles', 'Send Data error: ' . $api, 0 );
				return;
			}
			switch ( $api ) {
				case 'getAlarmProfiles':
					$profiles = unserialize( $data );
					if ( !is_array( $profiles ) ) {
						$profiles = [];
					}
					$this->SendDebug( 'UnifiPAlarmProfiles', 'Profiles: ' . json_encode( $profiles ), 0 );
					$this->WriteAttributeString( 'Profiles', json_encode( $profiles ) );
					// Auswahl-Variable an die aktuelle Profil-Liste anpassen
					$this->MaintainVariable( 'CurrentProfile', $this->Translate( 'Alarm Profile' ), 3, $this->buildProfilePresentation( $profiles ), 100, true );
					$this->MaintainAction( 'CurrentProfile', true );
					// Konfigurations-Formular aktualisieren
					$this->UpdateFormField( 'ProfileList', 'values', json_encode( $this->buildProfileListValues( $profiles ) ) );
					break;
				default:
					break;
			}
		}

		public function getProfiles(): void
		{
			$this->Send( 'getAlarmProfiles', '' );
		}

		public function RequestAction($Ident, $Value): void
		{
			switch ( $Ident ) {
				case 'CurrentProfile':
					$profileID = (string)$Value;
					$this->SendDebug( 'UnifiPAlarmProfiles', 'Set current arm profile: ' . $profileID, 0 );
					if ( $profileID !== '' ) {
						$this->Send( 'setCurrentArmProfile', $profileID );
					}
					$this->SetValue( $Ident, $profileID );
					break;
				case 'Armed':
					if ( $Value ) {
						// Sicherstellen, dass das gewählte Profil aktiv ist, dann scharf schalten
						$profileID = (string)$this->GetValue( 'CurrentProfile' );
						if ( $profileID !== '' ) {
							$this->Send( 'setCurrentArmProfile', $profileID );
						}
						$this->SendDebug( 'UnifiPAlarmProfiles', 'Enable arm alarm', 0 );
						$this->Send( 'enableArmAlarm', '' );
					} else {
						$this->SendDebug( 'UnifiPAlarmProfiles', 'Disable arm alarm', 0 );
						$this->Send( 'disableArmAlarm', '' );
					}
					$this->SetValue( $Ident, (bool)$Value );
					break;
				default:
					throw new Exception( 'Invalid Ident' );
			}
		}

		public function GetConfigurationForm():string
		{
			if ( $this->HasActiveParent() ) {
				$this->Send( 'getAlarmProfiles', '' );
			}

			$arrayStatus = array();
			$arrayStatus[] = array( 'code' => 102, 'icon' => 'active', 'caption' => $this->Translate( 'Instance is active' ) );
			$arrayStatus[] = array( 'code' => 104, 'icon' => 'inactive', 'caption' => $this->Translate( 'Instance is inactive' ) );

			$arrayElements = array();
			$arrayElements[] = array( 'type' => 'Label', 'bold' => true, 'label' => $this->Translate( 'UniFi Protect Alarm Profiles' ) );
			$arrayElements[] = array( 'type' => 'Label', 'label' => $this->Translate( 'Arm profiles are only available when using the local alarm manager.' ) );
			$arrayElements[] = array( 'type' => 'NumberSpinner', 'name' => 'Timer', 'caption' => $this->Translate( 'Update interval (s) -> 0=Off' ) );

			$arrayElements[] = array( 'type' => 'Label', 'bold' => true, 'label' => $this->Translate( 'Available Alarm Profiles' ) );
			$arrayElements[] = array(
				'type'                         => 'List',
				'name'                         => 'ProfileList',
				'rowCount'                     => 6,
				'add'                          => false,
				'delete'                       => false,
				'loadValuesFromConfiguration'  => false,
				'columns'                      => array(
					array( 'caption' => $this->Translate( 'Name' ), 'name' => 'Name', 'width' => 'auto' ),
					array( 'caption' => $this->Translate( 'ID' ), 'name' => 'ID', 'width' => '250px' )
				),
				'values'                       => $this->buildProfileListValues( $this->getCachedProfiles() )
			);

			$arrayActions = array();
			$arrayActions[] = array( 'type' => 'Button', 'label' => $this->Translate( 'Read Profiles' ), 'onClick' => 'UNIFIPAP_getProfiles($id);' );

			return JSON_encode( array( 'status' => $arrayStatus, 'elements' => $arrayElements, 'actions' => $arrayActions ) );
		}

		private function getCachedProfiles(): array
		{
			$profiles = json_decode( $this->ReadAttributeString( 'Profiles' ), true );
			if ( !is_array( $profiles ) ) {
				return [];
			}
			return $profiles;
		}

		private function buildProfileListValues(array $profiles): array
		{
			$values = array();
			foreach ( $profiles as $profile ) {
				if ( !is_array( $profile ) ) {
					continue;
				}
				$values[] = array(
					'Name' => (string)( $profile['name'] ?? '' ),
					'ID'   => (string)( $profile['id'] ?? '' )
				);
			}
			return $values;
		}

		private function buildProfilePresentation(array $profiles): array
		{
			$options = array();
			foreach ( $profiles as $profile ) {
				if ( !is_array( $profile ) || !isset( $profile['id'] ) ) {
					continue;
				}
				$options[] = array(
					'Caption'     => (string)( $profile['name'] ?? $profile['id'] ),
					'Value'       => (string)$profile['id'],
					'IconValue'   => '',
					'IconActive'  => false,
					'Color'       => -1
				);
			}
			if ( empty( $options ) ) {
				$options[] = array(
					'Caption'     => $this->Translate( 'No profiles found' ),
					'Value'       => '',
					'IconValue'   => '',
					'IconActive'  => false,
					'Color'       => -1
				);
			}
			return array(
				'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,
				'USAGE_TYPE'   => 2,
				'ICON'         => 'shield-halved',
				'OPTIONS'      => json_encode( $options )
			);
		}

		private function buildArmedPresentation(): array
		{
			return array(
				'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
				'USAGE_TYPE'   => 2,
				'ICON'         => 'shield',
				'OPTIONS'      => json_encode( array(
					array( 'ColorDisplay' => 16077123, 'Value' => false, 'Caption' => $this->Translate( 'Disarmed' ), 'IconValue' => 'shield', 'IconActive' => true, 'ColorActive' => true, 'ColorValue' => 16077123, 'Color' => -1 ),
					array( 'ColorDisplay' => 1692672, 'Value' => true, 'Caption' => $this->Translate( 'Armed' ), 'IconValue' => 'shield-check', 'IconActive' => true, 'ColorActive' => true, 'ColorValue' => 1692672, 'Color' => -1 )
				) )
			);
		}
	}
