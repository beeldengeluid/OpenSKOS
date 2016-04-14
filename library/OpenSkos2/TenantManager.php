<?php

/**
 * OpenSKOS
 *
 * LICENSE
 *
 * This source file is subject to the GPLv3 license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @category   OpenSKOS
 * @package    OpenSKOS
 * @copyright  Copyright (c) 2015 Picturae (http://www.picturae.com)
 * @author     Picturae
 * @license    http://www.gnu.org/licenses/gpl-3.0.txt GPLv3
 */
namespace OpenSkos2;

use OpenSkos2\Rdf\ResourceManager;
use OpenSkos2\Tenant;
use OpenSkos2\Namespaces\vCard;

class TenantManager extends ResourceManager
{
  
    protected $resourceType = Tenant::TYPE;
    
     //TODO: check conditions when it can be deleted
    public function CanBeDeleted($uri){
        return parent::CanBeDeleted($uri);
    }
    
    public function fetchUriName() {
        $query = 'SELECT ?uri ?name WHERE { ?uri  <' . vCard::ORG . '> ?org . ?org <' . vCard::ORGNAME . '> ?name . }';
        $response = $this->query($query);
        $result = $this->makeJsonUriNameMap($response);
        return $result;
    }
}
