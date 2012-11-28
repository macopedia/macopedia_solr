<?php

/**
 * Copyright (c) 2012, Macopedia
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *  - Neither the name of the Macopedia nor the names of its contributors
 *    may be used to endorse or promote products derived from this software
 *    without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 * 
 * @copyright Copyright 2012, Macopedia (http://www.Macopedia.fr)
 * @license http://www.opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 * 
 * @category Solr
 * @package Macopedia_Solr
 * @author Matthieu Vion <contact@Macopedia.fr>
 */

class Macopedia_Solr_Model_CatalogSearch_Resource_Fulltext extends Mage_CatalogSearch_Model_Resource_Fulltext {
    
    /**
     * Overloaded method prepareResult. Prepare results for query
     * Replaces the traditional fulltext search with a Solr Search (if active)
     *
     * @param Mage_CatalogSearch_Model_Fulltext $object
     * @param string $queryText
     * @param Mage_CatalogSearch_Model_Query $query
     * @return Macopedia_Solr_Model_CatalogSearch_Resource_Fulltext
     */
    public function prepareResult($object, $queryText, $query) {
        
        if(!Mage::getStoreConfigFlag('solr/active/frontend')) {
            return parent::prepareResult($object, $queryText, $query);
        }
        
        $adapter = $this->_getWriteAdapter();
        if (!$query->getIsProcessed()) {
            
            try {
                $search = Mage::getModel('solr/search')
                          ->loadQuery($queryText,(int)$query->getStoreId(),(int)Mage::getStoreConfig('solr/search/limit'));
                
                if($search->count()) {
                    $products = $search->getProducts();

                    $data = array();
                    foreach($products as $product) {
                        $data[] = array('query_id'   => $query->getId(),
                                        'product_id' => $product['id'],
                                        'relevance'  => $product['relevance']);
                    }

                    $adapter->insertMultiple($this->getTable('catalogsearch/result'),$data);
                }

                $query->setIsProcessed(1);
                
            } catch (Exception $e) {
                Mage::log($e->getMessage(),7,'solr.log');
                return parent::prepareResult($object, $queryText, $query);
            }
            
        }

        return $this;
    }
    
}