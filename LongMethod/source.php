<?php
class admin_Model_Marketing {
    public function saveMarketingData($data, $markId = null)
	{
        $sendStatusChangeNotify = false;
        
        //filtrowanie danych liczbowych
        
        $data['mark_profit']    = str_replace(',', '.', $data['mark_profit']);
        $data['mark_cost']      = str_replace(',', '.', $data['mark_cost']);
        
        try {
            $conn = Doctrine_Manager::connection();
            $conn->beginTransaction();

            $oMarketing = null;
	        if (isset($markId)) {
	            $oMarketing = Doctrine::getTable('EisMarketing')->find($markId);
	        } else {
				if (!empty($data['mark_end_date'])) {
                    if(strpos($data['mark_end_date'], '00:00:00') !== false) {
                        $data['mark_end_date'] = str_replace('00:00:00', '23:59:59', $data['mark_end_date']);
                    } else {
                        $data['mark_end_date'] = $this->_fillDateTimeIfDateOnlyPassed($data['mark_end_date']);
                    }
                }
	        }

            $data = $this->_filterDateTimeValues($data);

            $clearCache = false;
            if($data['mark_begin_date'] < date('Y-m-d H:i:s') && date('Y-m-d H:i:s')< $data['mark_end_date']) {
                $clearCache = true;
            }

	        if(!is_object($oMarketing)) {
	            $oMarketing = new EisMarketing();
	        }

	        $data['mark_status_id_old'] = $oMarketing->mark_status_id;
	        if(!empty($data['mark_status_id_old']) && $data['mark_status_id'] != $data['mark_status_id_old']) {
	        	//$oEisMarketingStatus = new EisMarketingStatus();
	        	//$boolChangeStatus = $oEisMarketingStatus->checkIfStatusCanBeChanged($oMarketing->mark_status_id, $data['mark_status_id']);
		        $boolChangeStatus = true; //SK: zmiana na zyczenie kienta (issue_number)
                        if (!$boolChangeStatus) {
                            return self::CHANGE_STATUS_ERR;
                        } else {
                            $sendStatusChangeNotify = true;
                        }

	            $oMarketing->mark_status_changed = true;	
	            $oMarketing->mark_status_changed_date = date('Y-m-d H:i:s');
	        } else {
	        	$oMarketing->mark_status_changed = false;  
	        }

	        if ($data['mark_profit'] == '') {
	            $data['mark_profit'] = null;
	        }

	        if ($data['mark_cost'] == '') {
	            $data['mark_cost'] = null;
	        }

	        $data['mark_serialized_conditions'] = serialize($data['action_params']);

	        if ($data['mark_repeat_interval'] === 'null') {
	            $data['mark_repeat_interval'] = null;
	        }
	        
            /*if($data['mark_for_logged'] || $data['mark_for_not_logged']) {
                $data['fk_segment_person_id'] = null;
            }*/ // issue_number
            
            if ($data['fk_segment_person_id']=='null') {
                $data['fk_segment_person_id'] = null;
            } // issue_number

	        if(!isset($markId)) { //DEFAULTOWY PRIORYTET MUSI BYC USTAWIONY RECZNIE DLA EisMarketingVersion
		        $strQuery = "SELECT nextval( 'eis_marketing_mark_priority_seq' )";
			    $conn = Doctrine_Manager::getInstance()->getCurrentConnection();
			    $statement = $conn->execute($strQuery);
			    $aData = $statement->fetchAll();
			    if (isset($aData[0]['nextval'])) {
					$data['mark_priority'] = $aData[0]['nextval'];
			    } else {
					$data['mark_priority'] = 999;
			    }
	        }
	        
	        $oMarketing->fromArray($data);

            if ($data['mark_landing_page_id'] == '') {
                $oMarketing->mark_landing_page_id = null;
            }

	        $oMarketing->save();


            if (array_key_exists('fk_approve_prs_id', $data)
                && $data['fk_approve_prs_id'] !== '') {

                $oAuditPerson = Doctrine::getTable('EicPerson')->find($data['fk_approve_prs_id']);

                desktop_Model_Writer::getInstance()->notify(
                    'promotion_new',
                    array(
                        'name' => $data['mark_name'] . ' - ' . $data['mark_code'],
                        'author' => ModelUser::getCurrentUserEmail(),
                        'person' => $oAuditPerson->prs_fname . ' ' . $oAuditPerson->prs_lname
                    )
                );
            }

	        $oMarketing->refresh();

			// komunikat systemowy

	        //zapisujemy w ten sposób tylko akcje na listach
            if (is_numeric($oMarketing->mark_id) ) {//&& strpos($oMarketing->mark_place, 'www') !== false ) {
            	$oEisMarketingCondReact = new EisMarketingCondReact();
            	$oEisMarketingCondReact->saveCondReactForMarketing($data['action_params'], $oMarketing->mark_id);
            	unset($oEisMarketingCondReact);
            }

            if ($sendStatusChangeNotify) {
            	$arrOPersAdmin = admin_Model_Users::getAdminList();
            	$oConfig = new EisConfiguration();
            	foreach($arrOPersAdmin as $oPerson) {
            		$oMailer = new Company_Message_Adapter(null, null,'utf-8');
            		$oMailer->setTitle('change_marketing_status_title');
                    $oMailer->setEmail($oPerson->EifUser->usr_email);
                    $oMailer->setPersonId($oPerson->prs_id);
					$oMailer->setFrom($oConfig->smartGetConfOption(EisConfiguration::CONF_APP_APPLICATION_EMAIL, false));
					$oMailer->setNameFrom($oConfig->smartGetConfOption(EisConfiguration::CONF_APP_MAILER_NAME, false));
            		//$oMailer->setFrom(Zend_Registry::get('application_email'));
            		//$oMailer->setNameFrom(Zend_Registry::get('mailer_name'));
            		$oMailer->setTemplate('marketing_status_change'); //templatka w '_mail/changepass.txt.phtml
            		$oMailer->setContent(array(
						'marketingName' => $data['mark_name'],
						'oldStatus' => EisMarketingStatus::getStatusName($data['mark_status_id_old']),
						'newStatus' => EisMarketingStatus::getStatusName($data['mark_status_id']),
					));
            		$oMailer->setMsgType('email');
            		$oMailer->setMsgStatus(true);
            		$oMailer->send();
            	}
            }

            $conn->commit();

            //czyszczenie cacha
            if ($clearCache) {
                Company_Cache::clean(array('product_list', 'Cms'));
            }
        } catch(Exception $e) {
            $conn->rollback();
            throw $e;
        }

    	return true;
    }
    
}
    