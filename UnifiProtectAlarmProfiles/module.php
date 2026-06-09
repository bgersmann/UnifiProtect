<?php

declare(strict_types=1);
	class UnifiProtectAlarmProfiles extends IPSModuleStrict
	{
		private const PARENT_DATAID = '{BBE44630-5AEE-27A0-7D2E-E1D2D776B83B}';

		public function Create():void
		{
			//Never delete this line!
			parent::Create();
			$this->RegisterPropertyInteger( 'Timer', 0 );
			$this->RegisterAttributeString( 'Profiles', '[]' );
			$this->RegisterTimer( 'Update Data', 0, "UNIFIPAP_getData(\$_IPS['TARGET']);" );
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
			// Bedienelemente
			$this->MaintainVariable( 'CurrentProfile', $this->Translate( 'Alarm Profile' ), 3, $this->buildProfilePresentation( $profiles ), $vpos++, true );
			$this->MaintainAction( 'CurrentProfile', true );
			$this->MaintainVariable( 'Armed', $this->Translate( 'Alarm armed' ), 0, $this->buildArmedPresentation(), $vpos++, true );
			$this->MaintainAction( 'Armed', true );

			// Anzeige (ArmMode aus /nvrs)
			$this->MaintainVariable( 'ArmStatus', $this->Translate( 'Arm Status' ), 3, $this->buildArmStatusPresentation(), $vpos++, true );
			$this->MaintainVariable( 'ActiveProfile', $this->Translate( 'Active Profile' ), 3, [ 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, 'USAGE_TYPE' => 0, 'ICON' => 'shield-halved' ], $vpos++, true );
			$this->MaintainVariable( 'ArmedAt', $this->Translate( 'Armed At' ), 1, '~UnixTimestamp', $vpos++, true );
			$this->MaintainVariable( 'Breach', $this->Translate( 'Breach detected' ), 0, $this->buildBreachPresentation(), $vpos++, true );

			$TimerMS = $this->ReadPropertyInteger( 'Timer' ) * 1000;
			$this->SetTimerInterval( 'Update Data', $TimerMS );

			if ( $this->HasActiveParent() ) {
				// instance active
				$this->SetStatus( 102 );
				$this->Send( 'getAlarmProfiles', '' );
				$this->getData();
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
			$data = $this->SendDataToParent( json_encode( [ 'DataID' => self::PARENT_DATAID,
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

		public function getData(): void
		{
			if ( !$this->HasActiveParent() ) {
				return;
			}
			$nvr = $this->fetchNvr();
			if ( empty( $nvr ) ) {
				$this->SendDebug( 'UnifiPAlarmProfiles', 'No NVR data received.', 0 );
				return;
			}
			$this->updateNvrData( $nvr );
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
						// Scharfschalten ist laut API nur moeglich, wenn der Status "disabled" ist
						$nvr    = $this->fetchNvr();
						$status = strtolower( (string)( $nvr['armMode']['status'] ?? '' ) );
						if ( $status !== 'disabled' ) {
							$this->SendDebug( 'UnifiPAlarmProfiles', 'Cannot arm, current status: ' . $status, 0 );
							$this->LogMessage( $this->Translate( 'Alarm can only be armed when the current status is "disabled".' ) . ' (' . $status . ')', KL_WARNING );
							if ( !empty( $nvr ) ) {
								$this->updateNvrData( $nvr ); // Schalter auf echten Zustand zuruecksetzen
							}
							return;
						}
						// Gewaehltes Profil sicherstellen, dann scharf schalten
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
					// Echten Zustand aus /nvrs nachladen
					$this->getData();
					break;
				default:
					throw new Exception( 'Invalid Ident' );
			}
		}

		public function GetConfigurationForm():string
		{
			if ( $this->HasActiveParent() ) {
				$this->Send( 'getAlarmProfiles', '' );
				$this->getData();
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
			$arrayActions[] = array(
				'type'  => 'RowLayout',
				'items' => array(
					array( 'type' => 'Button', 'label' => $this->Translate( 'Read Profiles' ), 'onClick' => 'UNIFIPAP_getProfiles($id);' ),
					array( 'type' => 'Button', 'label' => $this->Translate( 'Update Status' ), 'onClick' => 'UNIFIPAP_getData($id);' )
				)
			);

			return JSON_encode( array( 'status' => $arrayStatus, 'elements' => $arrayElements, 'actions' => $arrayActions ) );
		}

		private function fetchNvr(): array
		{
			if ( !$this->HasActiveParent() ) {
				return array();
			}
			$data = $this->SendDataToParent( json_encode( [ 'DataID' => self::PARENT_DATAID,
				'Api'        => 'getNvrs',
				'InstanceID' => $this->InstanceID,
				'Param1'     => '',
				'ID'         => ''
				] ) );
			if ( $data === false ) {
				return array();
			}
			$nvr = unserialize( $data );
			return is_array( $nvr ) ? $nvr : array();
		}

		private function updateNvrData(array $nvr): void
		{
			$armMode = ( isset( $nvr['armMode'] ) && is_array( $nvr['armMode'] ) ) ? $nvr['armMode'] : array();
			$status  = (string)( $armMode['status'] ?? '' );

			$this->SetValue( 'ArmStatus', $status );

			$profileID = (string)( $armMode['armProfileId'] ?? '' );
			$this->SetValue( 'ActiveProfile', $this->resolveProfileName( $profileID ) );

			$armedAtMs = (int)( $armMode['armedAt'] ?? 0 );
			$this->SetValue( 'ArmedAt', $armedAtMs > 0 ? intdiv( $armedAtMs, 1000 ) : 0 );

			$breachAt = (int)( $armMode['breachDetectedAt'] ?? 0 );
			$this->SetValue( 'Breach', $breachAt > 0 || strtolower( $status ) === 'breach' );

			// Schalter "Alarm armed" auf echten Zustand spiegeln (alles ausser "disabled" gilt als scharf)
			$this->SetValue( 'Armed', $status !== '' && strtolower( $status ) !== 'disabled' );

			// Aktuell auf dem Controller gewaehltes Profil in der Auswahl anzeigen
			if ( $profileID !== '' ) {
				$this->SetValue( 'CurrentProfile', $profileID );
			}

			$summary = $status;
			if ( $profileID !== '' ) {
				$summary .= ' - ' . $this->resolveProfileName( $profileID );
			}
			if ( $summary !== '' ) {
				$this->SetSummary( $summary );
			}
		}

		private function resolveProfileName(string $profileID): string
		{
			if ( $profileID === '' ) {
				return '';
			}
			foreach ( $this->getCachedProfiles() as $profile ) {
				if ( is_array( $profile ) && (string)( $profile['id'] ?? '' ) === $profileID ) {
					return (string)( $profile['name'] ?? $profileID );
				}
			}
			return $profileID;
		}

		private function getCachedProfiles(): array
		{
			$profiles = json_decode( $this->ReadAttributeString( 'Profiles' ), true );
			if ( !is_array( $profiles ) ) {
				return array();
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

		private function buildArmStatusPresentation(): array
		{
			return array(
				'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
				'USAGE_TYPE'   => 0,
				'ICON'         => 'shield',
				'OPTIONS'      => json_encode( array(
					array( 'ColorDisplay' => 8421504,  'Value' => 'disabled',  'Caption' => $this->Translate( 'Disabled' ),  'IconValue' => 'shield',       'IconActive' => true, 'ColorActive' => true, 'ColorValue' => 8421504,  'Color' => -1 ),
					array( 'ColorDisplay' => 16766720, 'Value' => 'arming',    'Caption' => $this->Translate( 'Arming' ),    'IconValue' => 'shield-halved','IconActive' => true, 'ColorActive' => true, 'ColorValue' => 16766720, 'Color' => -1 ),
					array( 'ColorDisplay' => 1692672,  'Value' => 'armed',     'Caption' => $this->Translate( 'Armed' ),     'IconValue' => 'shield-check', 'IconActive' => true, 'ColorActive' => true, 'ColorValue' => 1692672,  'Color' => -1 ),
					array( 'ColorDisplay' => 16766720, 'Value' => 'disarming', 'Caption' => $this->Translate( 'Disarming' ), 'IconValue' => 'shield-halved','IconActive' => true, 'ColorActive' => true, 'ColorValue' => 16766720, 'Color' => -1 ),
					array( 'ColorDisplay' => 16711680, 'Value' => 'breach',    'Caption' => $this->Translate( 'Breach' ),    'IconValue' => 'shield-xmark', 'IconActive' => true, 'ColorActive' => true, 'ColorValue' => 16711680, 'Color' => -1 )
				) )
			);
		}

		private function buildBreachPresentation(): array
		{
			return array(
				'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
				'USAGE_TYPE'   => 0,
				'ICON'         => 'triangle-exclamation',
				'OPTIONS'      => json_encode( array(
					array( 'ColorDisplay' => 16077123, 'Value' => false, 'Caption' => $this->Translate( 'No breach' ), 'IconValue' => 'shield-check', 'IconActive' => true, 'ColorActive' => true, 'ColorValue' => 1692672,  'Color' => -1 ),
					array( 'ColorDisplay' => 16711680, 'Value' => true,  'Caption' => $this->Translate( 'Breach' ),    'IconValue' => 'triangle-exclamation', 'IconActive' => true, 'ColorActive' => true, 'ColorValue' => 16711680, 'Color' => -1 )
				) )
			);
		}
	}
