<?php
class ProjectManager {

    protected $excuse;

    function shouldGiveRaiseTo(Employee $e) {
        try {
            $this->excuse->execute();
            return false;
        } catch (Exception $e) {
            inform_ceo_about((string)$e);
            return true;
        }
    }

}

class WrongExcuse {

    function execute() {
        if (!file_exists('/foo/bar')) {
            throw new FileNotFoundException("/foo/bar not found, got no idea what that means, though");
        }

        $this->_readEmployeeFileAndIgnoreContents();
    }
}

class BetterExcuse {

    function execute() {
        try {
            $this->_readEmployeeFileAndIgnoreContents();
        } catch(FileNotFoundException $e) {
            throw new CannotExcuseException("This man saved the world, there are no excuses for not giving him a raise!");
        } catch(Exception $e) {
            throw new CannotExcuseException("Yes, we can! Give him a raise, man.");
        }
    }
}