<?php
class Empathy_Import_Manager
{
    protected $section;

    public function runImport()
    {
        Empathy_Log::debug("AFTER LOCK: " . __METHOD__,self::LOG_FILENAME);

        /**
         * ONE SECTION processing
         */
        try {
            $this->section->process();
        }catch(Empathy_Import_Part_Exception $e){
            Empathy_Log::exception($e);
        }catch(Exception $e){
            Empathy_Log::exception($e);
            $message = 'Saving section failed, msg: ' . $e->getMessage() . ', file: ' . $e->getFile(). ', line: ' . $e->getLine() . ', trace: ' . $e->getTraceAsString();
            Empathy_Log::debug($message,self::LOG_FILENAME);
        }
    }
}

class ImportSection {

    protected $paramParser;

    public function process()
    {
        $params = $this->paramParser->parseParams();
        $reader = ImportReaderFactory::createReader($params);
        $reader->readChunk();

        if ($reader->isComplete()) {
            $importer = ImportEngineFactory::createEngine($params);
            $importer->import($reader->getRecordIterator());
        } else {
            throw new Empathy_Import_Part_Exception('Nie wczytano jeszcze wszystkiego!');
        }
    }
}