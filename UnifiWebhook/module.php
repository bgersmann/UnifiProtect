<?php

declare(strict_types=1);

// CLASS UnifiWebhook
class UnifiWebhook extends IPSModuleStrict
{
    /**
     * In contrast to Construct, this function is called only once when creating the instance and starting IP-Symcon.
     * Therefore, status variables and module properties which the module requires permanently should be created here.
     */
    public function Create():void
    {
        //Never delete this line!
        parent::Create();
       // $this->ConnectParent('{3F49B3E6-093C-40FA-661C-3D31BE37AEA3}');
        $this->RegisterPropertyString( 'webhooks', '[]' );
        $this->RegisterAttributeString('webhooksOld', '[]'); 
        
    }

    // public function GetCompatibleParents(): string
    //     {
    //         return json_encode([
    //             'type' => 'connect',
    //             'moduleIDs' => [
    //                 '{3F49B3E6-093C-40FA-661C-3D31BE37AEA3}'
    //             ]
    //         ]);
    //     }

    /**
     * This function is called when deleting the instance during operation and when updating via "Module Control".
     * The function is not called when exiting IP-Symcon.
     */
    public function Destroy():void
    {
        parent::Destroy();
    }

    /**
     * The content can be overwritten in order to transfer a self-created configuration page.
     * This way, content can be generated dynamically.
     * In this case, the "form.json" on the file system is completely ignored.
     */
    public function GetConfigurationForm():string
    {
        // Get Form
        $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);

        // Debug output
        //$this->SendDebug(__FUNCTION__, $form, 0);
        return json_encode($form);
    }

    /**
     * Is executed when "Apply" is pressed on the configuration page and immediately after the instance has been created.
     */
    public function ApplyChanges():void
    {       
        parent::ApplyChanges();
        $webhooksOldArr = json_decode($this->ReadAttributeString('webhooksOld'), true);
        if (!is_array($webhooksOldArr)) {
            $webhooksOldArr = [];
        }
        $webhooksArr = json_decode($this->ReadPropertyString('webhooks'), true);
        if (!is_array($webhooksArr)) {
            $webhooksArr = [];
        }
        $vpos = 1;
        $currentWebhookNames = [];
        $optionsExecute = json_encode([
            [
                'Caption' => $this->Translate('Ausführen...'),
                'Color' => 65280,
                'IconActive' => false,
                'IconValue' => '',
                'Value' => 1
            ]
        ], JSON_UNESCAPED_UNICODE);
        foreach ($webhooksArr as $webhook) {
            if (!isset($webhook['Name'], $webhook['WebhookID'])) {
                continue;
            }
            $name = $webhook['Name'] . str_replace('-', '_', $webhook['WebhookID']);
            $currentWebhookNames[] = $name;
            $this->MaintainVariable($name, $webhook['Name'], 1, [ 'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,'LAYOUT'=> 2, 'OPTIONS'=> $optionsExecute, 'ICON'=> 'bell-ring'], $vpos++, 1);
            $this->MaintainAction($name, true);
            $this->SetValue($name, 1);
            $this->SendDebug(__FUNCTION__, 'Webhook: '.$name, 0);
        }
        foreach ($webhooksOldArr as $webhook) {
            if (!isset($webhook['Name'], $webhook['WebhookID'])) {
                continue;
            }
            $name = $webhook['Name'] . str_replace('-', '_', $webhook['WebhookID']);
            if (!in_array($name, $currentWebhookNames, true)) {
                $this->MaintainVariable($name, $webhook['Name'], 1, '', 0, 0);
            }
        }

        $this->WriteAttributeString('webhooksOld', $this->ReadPropertyString('webhooks'));
        $this->SendDebug(__FUNCTION__, $this->ReadPropertyString('webhooks'), 0);

        // Set status
        $this->SetStatus(102);
    }


    /**
     * This function sends the text message to all of his children.
     *
     * @param string $text Text message
     */
    public function Send(string $api, string $param1):void
    {
        if ($this->HasActiveParent()) {
			$test=$this->SendDataToParent(json_encode(['DataID' => '{BBE44630-5AEE-27A0-7D2E-E1D2D776B83B}',
					'Api' => $api,
					'InstanceID' => $this->InstanceID,
					'Param1' => $param1
					]));
            return;
        }
    }

    public function RequestAction($ident, $value):void
    {
        $webhooksArr= json_decode($this->ReadPropertyString('webhooks'), true);
        foreach ($webhooksArr as $webhook) {
            $name = $webhook['Name'].str_replace("-","_",$webhook['WebhookID']);
            if ($ident == $name) {
                if ($value==1) {
                    $optionsInProgress = json_encode([
                        [
                            'Caption' => $this->Translate('Wird ausgeführt...'),
                            'Color' => 16711680,
                            'IconActive' => false,
                            'IconValue' => '',
                            'Value' => 0
                        ]
                    ], JSON_UNESCAPED_UNICODE);
                    $optionsExecute = json_encode([
                        [
                            'Caption' => $this->Translate('Ausführen...'),
                            'Color' => 65280,
                            'IconActive' => false,
                            'IconValue' => '',
                            'Value' => 1
                        ]
                    ], JSON_UNESCAPED_UNICODE);
                    $this->MaintainVariable( $name, $webhook['Name'], 1, [ 'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,'LAYOUT'=> 2, 'OPTIONS'=> $optionsInProgress, 'ICON'=> 'bell-ring'], 0, 1);
					$this->SetValue( $ident, 0);
                    $this->SendDebug(__FUNCTION__, 'Webhook '.$webhook['Name'].' wird ausgeführt.', 0);
                    $this->Send('setAlarmManager', $webhook['WebhookID']);
                    $this->MaintainVariable( $name, $webhook['Name'], 1, [ 'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,'LAYOUT'=> 2, 'OPTIONS'=> $optionsExecute, 'ICON'=> 'bell-ring'], 0, 1);
                    $this->SetValue( $ident, 1);
                }
            }
        }        
        return;
    }

}