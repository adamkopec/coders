<?php
class Empathy_Import_Manager
{
    public function runImport()
    {
        Empathy_Log::debug("AFTER LOCK: " . __METHOD__,self::LOG_FILENAME);

        /**
         * ONE SECTION processing 
         */        
        try {

            // FIXME źle pobiera numer sekcji
            for ($i=1 ; $i < 11 ; $i++ )
            {
                $sectionName = $this->_getSectionName($i);
                $sectionOptions = $this->_processConfig[$sectionName];

                // FIXME: bypass dla wywolania z konsoli!!
                $sectionOptions['dataSource']['filePath'] = dirname($this->_importPath) . DIRECTORY_SEPARATOR . $sectionOptions['dataSource']['filePath'];

                /**************************************/
                Empathy_Log::debug("SECTION " . $sectionName . ': searching files ' . __METHOD__,self::LOG_FILENAME);

                $importObj = new Empathy_Import($sectionOptions);

                // MATCH IMPORT FILES
                $dataSource         = $importObj->getDataSource();
                $dataSourceFiles    = $dataSource->getMatchedFiles(false);

                $arrFilesToImport   = $dataSourceFiles;

                if (!empty($arrFilesToImport))
                    break;
            }

            $importModel = $importObj->getImportModel();

            // SECTION: START OR CONTINUE
            $importId = $importModel->startImportProcess($sectionName, $arrFilesToImport, $this->_lockFilename);

            $importModel->saveFileList($arrFilesToImport, $sectionOptions['dataSource']['filePath'], $importObj->getDestModel(), $dataSource, $this->_lockFilename);
            $importModel->readFiles($dataSource);


            // SECTION: READING FINISHED?
            $isReadingFinished = $importModel->isReadingFinished();
            if($isReadingFinished){

                /**************************************/
                Empathy_Log::debug("READING FINISHED: " . $sectionName . ' processing ' . __METHOD__,self::LOG_FILENAME);

                /**
                 * FIXME: csv context - walidacja jest zrobiona w kontekscie pliku z kolumnami i zapisu do tabeli z kolumnami
                 * przy tworzeniu adaptera XML trzeba będzie to rop
                 */

                $isSectionValidated = false;
                $recordContent = $importModel->getTemporaryRecordContent();

                $isSectionValidated = true;

                if(false == $recordContent){

                    // przypadek gdy mamy zero rekordow do zimportowania
                    $isSectionValidated = true;

                    /**************************************/
                    Empathy_Log::debug('Empty record content',self::LOG_FILENAME);

                } else {

                    /**************************************/
                    Empathy_Log::debug("VALIDATE: " . __METHOD__,self::LOG_FILENAME);

                    // VALIDATE
//                    $sourceColumns = array_keys($recordContent);
                    $sourceColumns = array(0 => true); // FIXME bypas z braku czasu!!!
//                    $importColumns = $importObj->getColumns();
                    $importColumns = array(0 => true); // FIXME bypas z braku czasu!!!

                    $validator = $dataSource->getValidator();
                    $isSectionValidated = $importModel->runValidation($validator, $sourceColumns, $importColumns); // FIXME: csv only
                }

                if(true == $isSectionValidated){

                    /**************************************/
                    Empathy_Log::debug("VALID SECTION, SAVE RECORDS: " . __METHOD__,self::LOG_FILENAME);

                    // SAVE WITH DEST MODEL
                    $isSectionSaved = $importModel->saveRecords($importObj->getDestModel(), true);

                    if(true == $isSectionSaved){

                        if(method_exists($importObj->getDestModel(), 'onEndingImport')){
                            $importObj->getDestModel()->onEndingImport();
                        }

                        /**************************************/
                        Empathy_Log::debug("END IMPORT " . __METHOD__,self::LOG_FILENAME);

                        // FINISH HIM (&^%#$@%
                        $importModel->endImportProcess($importObj->getDestModel(), $dataSource, $sectionName);
                    }
                }
            }

        }catch(Empathy_Import_Part_Exception $e){
            Empathy_Log::exception($e);
        }catch(Exception $e){
            Empathy_Log::exception($e);
            $message = 'Saving section failed, msg: ' . $e->getMessage() . ', file: ' . $e->getFile(). ', line: ' . $e->getLine() . ', trace: ' . $e->getTraceAsString();
            Empathy_Log::debug($message,self::LOG_FILENAME);                
        }
    }
}