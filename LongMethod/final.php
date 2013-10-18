<?php
class admin_Model_Marketing_DataFixer
{
    protected $data;

    /**
     * @param mixed $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    public function fix($isCreating)
    {
        if (is_null($this->data)) {
            throw new Exception("Set data before trying to fix it!");
        }

        $this->_fixNumericValues();

        //$this->data = $this->_filterDateTimeValues($this->data);
        $this->_filterDateTimeValues();

        if ($isCreating && !empty($this->data['mark_end_date'])) {
            $this->_fixEndDate();
        }

        $this->data['mark_serialized_conditions'] = serialize($this->data['action_params']);

        $this->_fixEmptyValues();

        if ($isCreating) {
            $this->_fixPriorityForVersionTable();
        }
    }

    protected function _fixNumericValues()
    {
        $this->data['mark_profit'] = str_replace(',', '.', $this->data['mark_profit']);
        $this->data['mark_cost'] = str_replace(',', '.', $this->data['mark_cost']);
    }

    protected function _fixEndDate()
    {
        if (strpos($this->data['mark_end_date'], '00:00:00') !== false) {
            $this->data['mark_end_date'] = str_replace('00:00:00', '23:59:59', $this->data['mark_end_date']);
        } else {
            $this->data['mark_end_date'] = $this->_fillDateTimeIfDateOnlyPassed($this->data['mark_end_date']);
        }
    }

    protected function _fixPriorityForVersionTable()
    {
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

    protected function _fixEmptyValues()
    {
        $replacements = array(
            'mark_profit' => array('' => null),
            'mark_cost' => array('' => null),
            'mark_landing_page_id' => array('' => null),
            'mark_repeat_interval' => array('null' => null),
            'fk_segment_person_id' => array('null' => null),
        );

        foreach ($replacements as $column => $replacementTable) {
            foreach ($replacementTable as $badValue => $goodValue) {
                if ($this->data[$column] == $badValue) {
                    $this->data[$column] = $goodValue;
                }
            }
        }
    }
}


class admin_Model_Marketing_DataSaver
{
    protected $data = array();
    /** @var  admin_Model_Marketing_DataFixer */
    protected $fixer;
    protected $statusChangeNotificationEnabled = false;

    public function __construct(array $data = array())
    {
        $this->data = $data;
    }

    /**
     * @return boolean
     */
    public function getStatusChangeNotificationEnabled()
    {
        return $this->sendStatusChangeNotification;
    }

    /**
     * @param boolean $sendStatusChangeNotification
     */
    public function setStatusChangeNotificationEnabled($sendStatusChangeNotification)
    {
        $this->sendStatusChangeNotification = $sendStatusChangeNotification;
    }

    /**
     * @param \admin_Model_Marketing_DataFixer $fixer
     */
    public function setFixer($fixer)
    {
        $this->fixer = $fixer;
    }

    /**
     * @return \admin_Model_Marketing_DataFixer
     */
    public function getFixer()
    {
        return $this->fixer;
    }


    public function saveForId($markId = null)
    {
        $clearCache = $this->_shouldClearCache();
        $oMarketing = $this->_findOrCreateMarketing($markId);

        if ($this->_needsApproval()) {
            $this->_sendApprovalRequest();
        }

        $oMarketing->refresh(); //FIXME: purpose of refresh not known, leaving for backward compatibility

        $oEisMarketingCondReact = new EisMarketingCondReact();
        $oEisMarketingCondReact->saveCondReactForMarketing($this->data['action_params'], $oMarketing->mark_id);

        if ($this->statusChangeNotificationEnabled && $this->_statusHasChanged()) {
            $this->_sendNotification();
        }

        if ($clearCache) {
            Company_Cache::clean(array('product_list', 'Cms'));
        }

    }

    protected function _shouldClearCache()
    {
        $now = date('Y-m-d H:i:s');
        return ($this->data['mark_begin_date'] < $now && $now < $this->data['mark_end_date']);
    }

    protected function _statusHasChanged()
    {
        return !empty($this->data['mark_status_id_old']) && $this->data['mark_status_id'] != $this->data['mark_status_id_old'];
    }

    protected function _findOrCreateMarketing($markId = null)
    {
        $isCreating = !is_null($markId);

        if (!is_null($this->fixer)) {
            $this->fixer->setData($this->data)->fix($isCreating);
            $this->data = $this->fixer->getData();
        }

        //$this->_fixTypicalDataErrors($isCreating);

        if (!$isCreating) {
            $oMarketing = Doctrine::getTable('EisMarketing')->find($markId);
        }

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

        $oMarketing->fromArray($this->data);
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
            $oMailer = new Company_Message_Adapter(null, null, 'utf-8');
            $oMailer->setTitle('change_marketing_status_title');
            $oMailer->setEmail($oPerson->EifUser->usr_email);
            $oMailer->setPersonId($oPerson->prs_id);
            $oMailer->setFrom($oConfig->smartGetConfOption(EisConfiguration::CONF_APP_APPLICATION_EMAIL, false));
            $oMailer->setNameFrom($oConfig->smartGetConfOption(EisConfiguration::CONF_APP_MAILER_NAME, false));
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
    