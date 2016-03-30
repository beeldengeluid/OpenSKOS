<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OpenSkos2\Api;

use OpenSkos2\Namespaces\DcTerms;
use OpenSkos2\Namespaces\Dcmi;
use OpenSkos2\Namespaces\Skos;
use OpenSkos2\Namespaces\OpenSkos;
use OpenSkos2\SkosCollectionManager;


class SkosCollection extends AbstractTripleStoreResource
{
     public function __construct(SkosCollectionManager $manager) {
        $this->manager = $manager;
    }
    
     // specific content validation
     protected function validate($resourceObject, $tenantcode) {
       parent::validate($resourceObject, $tenantcode);
       
       //must be new
       $this->validatePropertyForCreate($resourceObject, DcTerms::TITLE, Skos::SKOSCOLLECTION);
       
       // set referred by an uri must exist 
       $this->validateURI($resourceObject, OpenSkos::SET, Dcmi::DATASET);
    }
    
    
    // specific content validation
    protected function validateForUpdate($resourceObject, $tenantcode,  $existingResourceObject) {
        parent::validateForUpdate($resourceObject, $tenantcode, $existingResourceObject);
        
        // must not occur as another collection's name if different from the old one 
        $this->validatePropertyForUpdate($resourceObject, $existingResourceObject, DcTerms::TITLE, Skos::SKOSCOLLECTION);
    
        // set referred by an uri must exist 
       $this->validateURI($resourceObject, OpenSkos::SET, Dcmi::DATASET);
    }
}