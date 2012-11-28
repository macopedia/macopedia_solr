<?php

/**
 * Copyright (c) 2012, Magentix
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
 *  - Neither the name of the Magentix nor the names of its contributors
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
 * @copyright Copyright 2012, Magentix (http://www.magentix.fr)
 * @license http://www.opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 * 
 * @category Solr
 * @package Magentix_Solr
 * @author Matthieu Vion <contact@magentix.fr>
 */

class Magentix_Solr_Model_Search extends Apache_Solr_Service {
    
    /**
     * Represents a Solr response.
     *
     * @var Apache_Solr_Response
     */
    protected $_response;
    
    /**
     * Constructor, retrieve config for connection to solr server.
     */
    public function __construct() {
        $_host = Mage::getStoreConfig('solr/server/host');
        $_port = Mage::getStoreConfig('solr/server/port');
        $_path = Mage::getStoreConfig('solr/server/path');
        
        parent::__construct($_host,$_port,$_path);
    }
    
    /**
     * Load query and set response
     * 
     * @param string $query
     * @param int $limit
     * @return Magentix_Solr_Model_Search
     */
    public function loadQuery($query,$storeId=0,$limit=10) {
        if(!$this->_response && $query) {
            $params = array(
                'fl' => 'id,score',
                'fq' => 'store_id:'.$storeId,
            );
            $response = $this->search($query,0,$limit,$params,'POST');
            $this->setResponse($response->response);
        }
        
        return $this;
    }
    
    /**
     * Delete All documents
     * 
     * @return Magentix_Solr_Model_Search
     */
    public function deleteAllDocuments() {
        $this->deleteByQuery('*:*');
        
        return $this;
    }
    
    /**
     * Set Solr response
     * 
     * @param Apache_Solr_Response $response
     */
    public function setResponse(Apache_Solr_Response $response) {
        $this->_response = $response;
    }
    
    /**
     * Extract product ids and score in Solr response
     * 
     * @return array $ids
     */
    public function getProducts() {
        $products = array();
        foreach($this->_response->docs as $doc) {
            $products[] = array('id'=>$doc->id,'relevance'=>$doc->score);
        }
        return $products;
    }
    
    /**
     * Retreive documents count
     * 
     * @return int
     */
    public function count() {
        return count($this->_response->docs);
    }
    
    /**
     * Test if server configuration is complete
     * 
     * @return bool
     */
    public function isConfigured() {
        return Mage::getStoreConfig('solr/server/host') && Mage::getStoreConfig('solr/server/port');
    }

    /**
     * Retrieve Store Id
     * 
     * @return int
     */
    public function getStoreId() {
        return Mage::app()->getStore()->getId();
    }
    
}