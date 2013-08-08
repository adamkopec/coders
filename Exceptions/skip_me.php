<?php
//niezbyt bezpieczne pomijanie

class Sapper {

    function disarmTheBomb(Bomb $b) {
        try {
            $this->cableCutter->cutTheRedWire();
            return true;
        }
        catch(NoRedWireException $e) {
            log('oh, so what, no need to cut anything...');
            return true;
        }
        catch(ColorBlindnessException $e) {
            log('hopefully that one was red');
            return true;
        }
        catch(AlreadyDisarmedException $e) {
            log('//it is impossible to work here!');
            throw new CannotDisarmException("I didn't do it, sorry.");
        }
    }

}

class BombFinder {

    function lookForOneInThePlayground() {
        try {
            foreach($playground->bombs as $bomb) {
                $this->sapper->disarmTheBomb($bomb); //"safely" called...
            }
        } catch(CannotDisarmException $e) {
            $playground->evacuate();
        }
    }
}

//bezpieczne pomijanie

class PrimeMinister {

    function comment(PoliticalEvent $event) {
        try {
            $opinion = $this->consultant->getOpinion($event);

            if ($opinion->isPositive()) {
                return $this->promiseSomething();
            } else {
                return $this->lieAbout($event);
            }
        } catch(DontKnowWhatToSayException $e) {
            return $this->lieAbout($event);
        }
    }
}