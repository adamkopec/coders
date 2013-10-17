<?php
class admin_Model_Marketing_DataSaver {

    protected $data = array();

    protected $sendStatusChangeNotification = false;

    public function __construct(array $data = array()) {
        $this->data = $data;
    }

    /**
     * @param boolean $sendStatusChangeNotification
     */
    public function setSendStatusChangeNotification($sendStatusChangeNotification)
    {
        $this->sendStatusChangeNotification = $sendStatusChangeNotification;
    }

    /**
     * @return boolean
     */
    public function getSendStatusChangeNotification()
    {
        return $this->sendStatusChangeNotification;
    }


    public function saveForId($markId = null)
    {
        $clearCache = $this->_shouldClearCache();
        $oMarketing = $this->_findOrCreateMarketing($markId);

        if ($this->_needsApproval()) {
            $this->_sendApprovalRequest();
        }

        $oMarketing->refresh();

        $oEisMarketingCondReact = new EisMarketingCondReact();
        $oEisMarketingCondReact->saveCondReactForMarketing($this->data['action_params'], $oMarketing->mark_id);

        if ($this->sendStatusChangeNotification) {
            $this->_sendNotification();
        }

        if ($clearCache) {
            Empathy_Cache::clean(array('product_list', 'Cms'));
        }

    }

    protected function _shouldClearCache() {
        $now = date('Y-m-d H:i:s');
        return ($this->data['mark_begin_date'] < $now && $now < $this->data['mark_end_date']);
    }

    protected function _statusHasChanged()
    {
        return !empty($this->data['mark_status_id_old']) && $this->data['mark_status_id'] != $this->data['mark_status_id_old'];
    }

    protected function _findOrCreateMarketing($markId)
    {
        //filtrowanie danych liczbowych
        $this->data['mark_profit'] = str_replace(',', '.', $this->data['mark_profit']);
        $this->data['mark_cost'] = str_replace(',', '.', $this->data['mark_cost']);


        $oMarketing = null;
        if (isset($markId)) {
            $oMarketing = Doctrine::getTable('EisMarketing')->find($markId);
        } else {
            if (!empty($this->data['mark_end_date'])) {
                if (strpos($this->data['mark_end_date'], '00:00:00') !== false) {
                    $this->data['mark_end_date'] = str_replace('00:00:00', '23:59:59', $this->data['mark_end_date']);
                } else {
                    $this->data['mark_end_date'] = $this->_fillDateTimeIfDateOnlyPassed($this->data['mark_end_date']);
                }
            }
        }

        $this->data = $this->_filterDateTimeValues($this->data);



        if (!is_object($oMarketing)) {
            $oMarketing = new EisMarketing();
        }

        $this->data['mark_status_id_old'] = $oMarketing->mark_status_id;
        if ($this->_statusHasChanged()) {
            $oMarketing->mark_status_changed = true;
            $oMarketing->mark_status_changed_date = date('Y-m-d H:i:s');
        } else {
            $oMarketing->mark_status_changed = false;
        }

        if ($this->data['mark_profit'] == '') {
            $this->data['mark_profit'] = null;
        }

        if ($this->data['mark_cost'] == '') {
            $this->data['mark_cost'] = null;
        }

        $this->data['mark_serialized_conditions'] = serialize($this->data['action_params']);

        if ($this->data['mark_repeat_interval'] === 'null') {
            $this->data['mark_repeat_interval'] = null;
        }

        if ($this->data['fk_segment_person_id'] == 'null') {
            $this->data['fk_segment_person_id'] = null;
        }

        if (!isset($markId)) { //DEFAULTOWY PRIORYTET MUSI BYC USTAWIONY RECZNIE DLA EisMarketingVersion
            $strQuery = "SELECT nextval( 'eis_marketing_mark_priority_seq' )";
            $conn = Doctrine_Manager::getInstance()->getCurrentConnection();
            $statement = $conn->execute($strQuery);
            $aData = $statement->fetchAll();
            if (isset($aData[0]['nextval'])) {
                $this->data['mark_priority'] = $aData[0]['nextval'];
            } else {
                $this->data['mark_priority'] = 999;
            }
        }

        $oMarketing->fromArray($this->data);

        if ($this->data['mark_landing_page_id'] == '') {
            $oMarketing->mark_landing_page_id = null;
        }

        $oMarketing->save();
        return $oMarketing;
    }

    protected function _needsApproval()
    {
        return array_key_exists('fk_approve_prs_id', $this->data)
        && $this->data['fk_approve_prs_id'] !== '';
    }

    protected function _sendApprovalRequest()
    {
        $oAuditPerson = Doctrine::getTable('EicPerson')->find($this->data['fk_approve_prs_id']);

        desktop_Model_Writer::getInstance()->notify(
            'promotion_new',
            array(
                'name' => $this->data['mark_name'] . ' - ' . $this->data['mark_code'],
                'author' => ModelUser::getCurrentUserEmail(),
                'person' => $oAuditPerson->prs_fname . ' ' . $oAuditPerson->prs_lname
            )
        );
    }

    protected function _sendNotification()
    {
        $arrOPersAdmin = admin_Model_Users::getAdminList();
        $oConfig = new EisConfiguration();
        foreach ($arrOPersAdmin as $oPerson) {
            $oMailer = new Empathy_Message_Adapter(null, null, 'utf-8');
            $oMailer->setTitle('change_marketing_status_title');
            $oMailer->setEmail($oPerson->EifUser->usr_email);
            $oMailer->setPersonId($oPerson->prs_id);
            $oMailer->setFrom($oConfig->smartGetConfOption(EisConfiguration::CONF_APP_APPLICATION_EMAIL, false));
            $oMailer->setNameFrom($oConfig->smartGetConfOption(EisConfiguration::CONF_APP_MAILER_NAME, false));
            //$oMailer->setFrom(Zend_Registry::get('application_email'));
            //$oMailer->setNameFrom(Zend_Registry::get('mailer_name'));
            $oMailer->setTemplate('marketing_status_change'); //templatka w '_mail/changepass.txt.phtml
            $oMailer->setContent(array(
                'marketingName' => $this->data['mark_name'],
                'oldStatus' => EisMarketingStatus::getStatusName($this->data['mark_status_id_old']),
                'newStatus' => EisMarketingStatus::getStatusName($this->data['mark_status_id']),
            ));
            $oMailer->setMsgType('email');
            $oMailer->setMsgStatus(true);
            $oMailer->send();
        }
    }

}
    