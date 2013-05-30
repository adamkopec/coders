<?php

interface Observable {
    public function registerObserver(Observer $observer);
    public function notifyObservers(Event $e);
}

interface Event {

}

interface Observer {
    public function notify(Event $e);
}

abstract class StandardObservable {
    protected $observers = array();

    public function registerObserver(Observer $observer) {
        $this->observers[] = $observer;
    }

    public function notifyObservers(Event $event) {
        foreach($this->observers as $observer) {
            $observer->notify($event);
        }
    }
}

class BasketProductParsedEvent implements Event {
    protected $product;

    public function __construct(array $product) {
        $this->product = $product;
    }

    public function getProduct() {
        return $this->product;
    }
}

class ReadyForSaveEvent implements Event {
    protected $entity;

    public function __construct(EisOrder $entity) {
        $this->entity = $entity;
    }

    public function getEntity() {
        return $this->entity;
    }
}

class order_Model_Default extends StandardObservable {

    public function __construct() {
        $this->registerObserver(new AdhocObserver());
        $this->registerObserver(new CreditAgricoleObserver());
        $this->registerObserver(new ZagielObserver());
    }

    public function add($shippingCost, $filteredData, $basketId, $arrCurrentBasketInfo, $personData, $isElPayment, $aBasketService = null, $langId = null)
    {
        //280 linii tragedii

        $orderedServices = array();
        foreach($currentBasketProducts as $basketPrd) {

            //115 linii tragedii

            $this->notifyObservers(new BasketProductParsedEvent($aOrderProduct));
        }

        //93 linie tragedii

        $this->notifyObservers(new ReadyForSaveEvent($orderModel));

        //90 linii tragedii

        return $orderId;
    }
}

class AdhocObserver implements Observer {

    protected $stats;

    public function notify(Event $event)
    {
        if ($e instanceof BasketProductParsedEvent) {
            $this->stats->registerStatsProduct($e->getProduct());
        }
    }
}

class CreditAgricoleObserver implements Observer {
    public function notify(Event $event)
    {
        if ($event instanceof ReadyForSaveEvent) {
            {{ # Credit Agricole - Lukas Raty
                $entity = $event->getEntity();
                if($entity->fk_orp_id == EisOrderPayment::getIdForBehavior(EisOrderPayment::BEHAVE_AS_CREDIT_AGRICOLE)){
                    $oCA = new creditagricole_Model_CA();
                    $oCA->prepareXml($entity->EisOrderLine->toArray(),$entity);
                    $oCA->sendRequest();
                    $oCA->saveApplication();
                }
            }}
        }
    }
}

class ZagielObserver implements Observer {
    public function notify(Event $event)
    {
        if ($event instanceof ReadyForSaveEvent) {
            {{ # Zagiel eRaty
                $entity = $event->getEntity();
                if($entity->ord_eraty){
                    $oZagiel = new zagiel_Model_Zagiel();
                    $oZagiel->addForOrder($entity);
                    $oZagiel->addIdentifierToSession();
                }
            }}
        }
    }
}


