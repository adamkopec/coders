<?php

class ModelProductGroup
{
    public static function getDescendantsForMenu($rootId = null, $segmentId = null, $levelLimit = null) {
        if (!is_null($segmentId)) {
            $root = Doctrine::getTable('EisProductSegment')->find($segmentId);

            $aReturn = array();

            if ($root) {
                $oGroup = new product_Model_Group;
                $descendants = $oGroup->getSegmentCategories($segmentId, $levelLimit);

                $data = array();
                foreach ($descendants as $descendant) {
                    $data[] = array(
                        'pse_id' => $root->pse_id,
                        'pse_name' => $root->pse_name,
                        'pgr_id' => $descendant['pgr_id'],
                        'pgr_name' => $descendant['pgr_name'],
                        'pse_count' => $descendant['psc_product_count'],
                    );
                }

                return $data;
//                $aReturn[] = array(
//                    'pse_id' => $root->pse_id,
//                    'pse_name' => $root->pse_name,
//                    'pse_count' => $root->pse_count,
//                    'descendants' => $data
//                );
            }
            return $aReturn;
        }

        /** @var $rootRecord EisProductGroup */
        if (!is_null($rootId)) {
            $oQuery = self::getGroupByIdQuery($rootId);
            //$oQuery = self::_addProductCountQuery($oQuery, $rootId, 'pgr_product_count');
            $rootRecord = $oQuery->fetchOne(array(), Doctrine::HYDRATE_RECORD);
            if (!is_object($rootRecord)) {
                $oQuery = self::getGroupByIdQuery($rootId);
                $rootRecord = $oQuery->fetchOne(array(), Doctrine::HYDRATE_RECORD);
            }
        } else {
            $oQuery = self::getDefaultGroupItgQuery();
            //$oQuery = self::_addProductCountQuery($oQuery, $rootId, 'pgr_product_count');
            $rootRecord = $oQuery->fetchOne(array(), Doctrine::HYDRATE_RECORD);
        }

        /** @var $tree Doctrine_Tree_NestedSet */
        $tree = $rootRecord->getTable()->getTree();
        $q = $tree->getBaseQuery();
        $rootAlias = $q->getRootAlias();
        $q->select($rootAlias . '.*');
        //$q = self::_addProductCountQuery($q, $rootId, 'pgr_product_count');
        $q->addWhere('pgr_is_public = true');
        $q->addWhere('pgr_is_empty = false');
        //$q->addSelect('(SELECT COUNT(*) FROM EivProductList prl WHERE prl.pgr_id = pgr.pgr_id) as product_count');
        //$q->addWhere('pgr_product_count > 0');
        $q->orderBy('level ASC NULLS LAST');


        $tree->setBaseQuery($q);

        // zmieniając BaseQuery powodujemy niespójność pomiędzy hasParent a getParent
        // w tym przypadku, jeśli ktos wejdzie w pusta kategorię, to
        // hasParent może zwracać true, a getParent false

        /** @var $node Doctrine_Node_NestedSet */
        $node = $rootRecord->getNode();
        /** @var $descendants Doctrine_Collection */
        $aReturn = array();
        if (!is_null($rootId)) {
//            if ($rootRecord->level > 1) {
//                $parent = $rootRecord->getNode()->getParent();
//                $parentLink = array(
//                    'pgr_id' => $parent->pgr_id,
//                    'pgr_name' => $parent->pgr_name
//                );
//            }

            if ($rootRecord->level == 1) {
                // parentem jest root

                $rootRecord = $node->getParent();
                $node = $rootRecord->getNode();

                $descendants = $node->getDescendants(1);
            } elseif ($node->hasParent() && $node->getParent()) {
                $rootRecord = $node->getParent();
                $node = $rootRecord->getNode();

                if ($rootRecord->level > 1) {
                    $ancestors = $rootRecord->getNode()->getAncestors();

                    $aAncestors = array();
                    foreach ($ancestors as $ancestor) {
                        if ($ancestor->level == 0) {
                            continue;
                        }
                        $aAncestors[] = array(
                            'pgr_id' => $ancestor->pgr_id,
                            'pgr_name' => $ancestor->pgr_name,
                            'level' => $ancestor->level
                        );
                    }
                }

                $aReturn[] = array(
                    'pgr_id' => $rootRecord->pgr_id,
                    'pgr_name' => $rootRecord->pgr_name,
                    'level' => $rootRecord->level,
                    
                );
            } else {
                $aReturn[] = array(
                    'pgr_id' => $rootRecord->pgr_id,
                    'pgr_name' => $rootRecord->pgr_name,
                    'level' => $rootRecord->level,
                    
                );
            }

            $descendants = $node->getDescendants(1);
        } else {
            $descendants = $node->getDescendants(1);
        }

        if (is_object($descendants)) { // Doctrine_Collection
            foreach ($descendants as /** @var $descendant EisProductGroup */ $descendant) {
                if (is_null($rootId) || $rootRecord->pgr_id == self::getDefaultGroupId()) {
                    if ($descendant->level == 1) {
                        $data = array();
                        $collection = $descendant->getNode()->getDescendants(1);

                        // glowne menu - pobieramy nastepny poziom podkategorii
                        if ($collection) {
                            foreach ($collection as $innerDescendant) {
                                $data[] = array(
                                    'pgr_id' => $innerDescendant->pgr_id,
                                    'pgr_name' => $innerDescendant->pgr_name,
                                    'level' => $innerDescendant->level,
                                );
                            }
                        }
                        $aReturn[] = array(
                            'pgr_id' => $descendant->pgr_id,
                            'pgr_name' => $descendant->pgr_name,
                            'level' => $descendant->level,
                            //'image' => $descendant->getImagePath('/') . $descendant->pgr_main_image, // w założeniu każda kategoria ma zdjecie, zdjecia tylko dla głownych kategorii - hover na kategorie
                            'descendants' => $data
                        );
                    }
                } else {

                    if ($node->hasParent()) {

                        $tree = $descendant->getTable()->getTree();

                        $q = $tree->getBaseQuery();
                        $rootAlias = $q->getRootAlias();

                        if ('pgr' != $rootAlias) {
                            $q->from('EisProductGroup pgr'); // musimy znowu ustawic rootalias na pgr
                            $q->addSelect('pgr.*');
                        }
                        $q = self::_addProductCountQuery($q, $rootId, 'product_count');

                        $q->addWhere($rootAlias . '.pgr_is_empty = FALSE');
                        $q->addWhere($rootAlias . '.pgr_product_count > 0');
                        $q->setHydrationMode(Doctrine::HYDRATE_SCALAR);
                        $tree->setBaseQuery($q);

                        $aReturn[0]['descendants'][] = array(
                            'pgr_id' => $descendant->pgr_id,
                            'pgr_name' => $descendant->pgr_name,
                            'level' => $descendant->level,                            
                            'descendants' => ($descendant->pgr_id == $rootId) ? $descendant->getNode()->getDescendants(1) : null
                        );
                    } else {
                        $aReturn[0]['descendants'][] = array(
                            'pgr_id' => $descendant->pgr_id,
                            'pgr_name' => $descendant->pgr_name,
                            'level' => $descendant->level                            
                        );
                    }
                }
            }
        }

        if (!is_null($rootId) && $rootRecord->pgr_id != self::getDefaultGroupId()) {
            $newQuery = Doctrine_Query::create()->from('EisProductGroup pgr');
            $tree->setBaseQuery($newQuery);
        }

        if (isset($aAncestors)) {
            // fixme - aktualne zalozenie jest takie - ze mamy max 4 poziomy
            if (1 == count($aAncestors)) {
                $aAncestors[0]['descendants'] = $aReturn;

                $aReturn = $aAncestors;
            } elseif (isset($aAncestors[0])) {
                $root = $aAncestors[0];
                $child = $aAncestors[1];
                $child['descendants'] = $aReturn;
                $root['descendants'] = array(0 => $child);

                $aReturn = array(0 => $root);
            }
        }
        if (1 == count($aReturn)) {
            // musimy zbudowac drzewo kategorii
            $rootRecord = self::getDefaultGroupItg();
            $tree = $rootRecord->getTable()->getTree();
            $q = $tree->getBaseQuery();
            $rootAlias = $q->getRootAlias();
            $q->addSelect($rootAlias . '.*');

            $q = self::_addProductCountQuery($q, $rootId, 'product_count');

            $q->setHydrationMode(Doctrine::HYDRATE_SCALAR);
            $tree->setBaseQuery($q);

            $descendants = $rootRecord->getNode()->getDescendants(1);

            $aTmp = $aReturn;

            $aReturn = array();

            if (!empty($descendants)) {
                foreach ($descendants as $descendant) {
                    if ($descendant['pgr_pgr_id'] == $aTmp[0]['pgr_id']) {
                        $aReturn[] = $aTmp[0];
                    } else {
                        $aReturn[] = array(
                            'pgr_id' => $descendant['pgr_pgr_id'],
                            'pgr_name' => $descendant['pgr_pgr_name'],
                            'level' => $descendant['pgr_level'],
                            'pgr_product_count' => $descendant['pgr_product_count']
                        );
                    }
                }
            }
            $tree->resetBaseQuery();
        }
//        if (isset($parentLink)) {
//            $aReturn['parent'] = $parentLink;
//        }

        return $aReturn;
    }
}