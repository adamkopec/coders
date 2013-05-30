<?php
class communication_Model_Communication
{
    protected $discountCodeSender;

    public function __construct() {
        $this->discountCodeSender = new DiscountCodeSender();
    }

    public function adduserAction() {
        $hash = $this->getParam('hash');

        if (!empty($hash)) {
            $oEicPerson = Doctrine::getTable('EicPerson')->findOneByPrs_hash($hash);

            if (is_object($oEicPerson)) {

                try {
                    $oEicPerson->prs_newsletter = true;

                    $oEicPerson->save();

                    $this->discountCodeSender->sendCodeForPerson($oEicPerson);

                    $this->getHelper('Messenger')->add(Empathy_Helper_Messenger::OK, 'newsletter_user_accept_from_email_rebate');
                }catch (Exception $e) {
                    echo $e->getTraceAsString();
                    echo $e->getMessage(); die;
                    $this->getHelper('Messenger')->add(Empathy_Helper_Messenger::ERR, 'newsletter_user_accept_from_email_error');
                }
            }
            else {
                $this->getHelper('Messenger')->add(Empathy_Helper_Messenger::ERR, 'newsletter_user_accept_from_email_error');
            }
        }
        else {
            $this->getHelper('Messenger')->add(Empathy_Helper_Messenger::ERR, 'newsletter_user_accept_from_email_error_data');
        }

        $this->_redirect('/');
    }
}

class DiscountCodeSender {

    public function sendCodeForPerson(EicPerson $oEicPerson)
    {
        $proc = $this->_getPercentValue();

        $userId = $oEicPerson->prs_usr_id;

        $oDiscountCode = Empathy_Discount_Model_Default::genCodeForNewUser($userId, $proc, true, EicDiscountCode::TYPE_NONEWSLETTER);

        $personId = $oEicPerson->prs_id;

        //wysyłka maila z przyznanym kodem
        Empathy_Discount_Model_Default::sendMailWithCode($personId, ModelUser::getCurrentUserEmail(), $oDiscountCode->edc_token, $proc, 'nonewsletter');
    }

    protected function _getPercentValue()
    {
        $oEicSettings = Doctrine::getTable('EicDiscountCodeSettings')->find(EicDiscountCodeSettings::SETTINGS_ID);

        if (is_object($oEicSettings)) {
            $proc = (int)$oEicSettings->dcs_nonewsletter;
            return $proc;
        } else {
            $proc = EicDiscountCodeSettings::DEFAULT_AFTER_NONEWSLETTER;
            return $proc;
        }
    }
}