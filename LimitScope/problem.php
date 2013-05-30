<?php
class order_Model_Default {

    public function add($shippingCost, $filteredData, $basketId, $arrCurrentBasketInfo, $personData, $isElPayment, $aBasketService = null, $langId = null)
    {

        //280 linii tragedii

        $statsPopularProduct = new AdhocPopularForm();

        $orderedServices = array();
        foreach($currentBasketProducts as $basketPrd) {

            //115 linii tragedii

            $statsPopularProduct->registerStatsProduct($aOrderProduct);
        }

        //93 linie tragedii

        {{ # Credit Agricole - Lukas Raty
            if($orderModel->fk_orp_id == EisOrderPayment::getIdForBehavior(EisOrderPayment::BEHAVE_AS_CREDIT_AGRICOLE)){
                $oCA = new creditagricole_Model_CA();
                $oCA->prepareXml($aOrderedProducts,$orderModel);
                $oCA->sendRequest();
                $oCA->saveApplication();
            }
        }}

        {{ # Zagiel eRaty
            if($orderModel->ord_eraty){
                $oZagiel = new zagiel_Model_Zagiel();
                $oZagiel->addForOrder($orderModel);
                $oZagiel->addIdentifierToSession();
            }
        }}

        //90 linii tragedii

        return $orderId;
    }
}