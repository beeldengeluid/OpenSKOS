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
namespace OpenSkos2\SkosXl;

use OpenSkos2\Rdf\Resource;
use OpenSkos2\Rdf\Uri;
use OpenSkos2\Namespaces\OpenSkos;
use OpenSkos2\Namespaces\SkosXl;
use OpenSkos2\Namespaces\Rdf;
use Rhumsaa\Uuid\Uuid;

class Label extends Resource
{
    const TYPE = SkosXl::LABEL;
    
    /**
     * Resource constructor.
     * @param string $uri , optional
     */
    public function __construct($uri = null)
    {
        parent::__construct($uri);
        $this->addProperty(Rdf::TYPE, new Uri(self::TYPE));
    }
    
    /**
     * Get tenant
     *
     * @return Literal
     */
    public function getTenant()
    {
        $values = $this->getProperty(OpenSkos::TENANT);
        if (isset($values[0])) {
            return $values[0];
        }
    }
        
    /**
     * Get institution row. Code adapted from OpenSkos2\Concept
     * @TODO Remove dependency on OpenSKOS v1 library
     * @return OpenSKOS_Db_Table_Row_Tenant
     */
    public function getInstitution()
    {
        // @TODO Remove dependency on OpenSKOS v1 library
        $model = new \OpenSKOS_Db_Table_Tenants();
        return $model->find($this->getTenant())->current();
    }
    
    /**
     * Generates label uri
     * @return string
     */
    public static function generateUri()
    {
        $separator = '/';
        
        $baseUri = rtrim(self::getBaseApiUri(), $separator);
        
        return $baseUri . $separator . 'labels' . $separator . Uuid::uuid4();
    }
    
    /**
     * @TODO temp function for base api uri
     */
    protected static function getBaseApiUri()
    {
        $apiOptions = \OpenSKOS_Application_BootstrapAccess::getOption('api');
        return $apiOptions['baseUri'];
    }
}
