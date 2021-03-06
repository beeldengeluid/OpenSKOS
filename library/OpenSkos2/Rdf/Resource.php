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

namespace OpenSkos2\Rdf;

use OpenSkos2\Rdf\Object as RdfObject;
use OpenSkos2\Namespaces as Namespaces;
use OpenSkos2\Namespaces\OpenSkos as OpenSkos;
use OpenSkos2\Exception\OpenSkosException;

class Resource extends Uri implements ResourceIdentifier
{
    /**
     * @TODO Separate in StatusAwareResource class or something like that
     * openskos:status value which marks a resource as deleted.
     */
    const STATUS_DELETED = 'deleted';

    protected $properties = [];

    /**
     * @return array of RdfObject[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param string $predicate
     * @return RdfObject[]
     */
    public function getProperty($predicate)
    {
        if (!isset($this->properties[$predicate])) {
            return [];
        } else {
            return $this->properties[$predicate];
        }
    }

    /**
     * @param string $predicate
     * @param RdfObject $value
     * @return $this
     */
    public function addProperty($predicate, RdfObject $value)
    {
        $this->handleSpecialProperties($predicate, $value);
        $this->properties[$predicate][] = $value;
        return $this;
    }

    /**
     * Add multiple values at once, keeps the existing values
     * @param string $predicate
     * @param RdfObject[] $values
     * @return $this
     */
    public function addProperties($predicate, array $values)
    {
        foreach ($values as $value) {
            $this->addProperty($predicate, $value);
        }
        return $this;
    }

    /**
     * Make sure the property is only added once
     *
     * @param string $predicate
     * @param RdfObject $value
     * @return \OpenSkos2\Rdf\Resource
     */
    public function addUniqueProperty($predicate, RdfObject $value)
    {
        if (!isset($this->properties[$predicate])) {
            $this->addProperty($predicate, $value);
            return $this;
        }
        foreach ($this->properties[$predicate] as $obj) {
            if ($obj instanceof Literal && $value instanceof Literal) {
                if ($obj->getValue() === $value->getValue() && $obj->getLanguage() === $value->getLanguage()) {
                    return $this;
                }
            } elseif ($obj instanceof Uri && $value instanceof Uri) {
                if ($obj->getUri() === $value->getUri()) {
                    return $this;
                }
            }
        }
        $this->addProperty($predicate, $value);
        return $this;
    }

    /**
     * Set property, overwrite existing values.
     * @param string $predicate
     * @param RdfObject $value
     * @return $this
     */
    public function setProperty($predicate, RdfObject $value)
    {
        $this->unsetProperty($predicate)
            ->addProperty($predicate, $value);
        return $this;
    }

    /**
     * Set multiple values at once, override existing values
     * @param string $predicate
     * @param RdfObject[] $values
     * @return $this
     */
    public function setProperties($predicate, array $values)
    {
        $this->unsetProperty($predicate)
            ->addProperties($predicate, $values);
        return $this;
    }

    /**
     * @param string $predicate
     * @return $this
     */
    public function unsetProperty($predicate)
    {
        unset($this->properties[$predicate]);
        return $this;
    }

    /**
     * @param string $predicate
     * @return bool
     */
    public function hasProperty($predicate)
    {
        return isset($this->properties[$predicate]);
    }

    /**
     * @param string $predicate
     * @return bool
     */
    public function isPropertyEmpty($predicate)
    {
        return !isset($this->properties[$predicate])
            || $this->properties[$predicate] === null
            || $this->properties[$predicate] === '';
    }

    /**
     * @param string $predicate
     * @return bool
     */
    public function isPropertyTrue($predicate)
    {
        if (!$this->isPropertyEmpty($predicate)) {
            $values = $this->getProperty($predicate);
            return (bool) $values[0]->getValue();
        }
        return false;
    }

    /**
     * @TODO Separate in StatusAwareResource class or something like that
     * @return string|null
     */
    public function getStatus()
    {
        if (!$this->hasProperty(OpenSkos::STATUS)) {
            return null;
        } else {
            return $this->getProperty(OpenSkos::STATUS)[0]->getValue();
        }
    }

    /**
     * Check if the concept is deleted
     * @TODO Separate in StatusAwareResource class or something like that
     * @return boolean
     */
    public function isDeleted()
    {
        if ($this->getStatus() === self::STATUS_DELETED) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return current($this->getProperty(\OpenSkos2\Namespaces\Rdf::TYPE));
    }

    /**
     * @return string
     */
    public function getCaption($language = null)
    {
        return $this->uri;
    }

    /**
     * Is the current resource a blank node.
     * It is if no uri given or generated uri starting with _:
     * @return boolean
     */
    public function isBlankNode()
    {
        return empty($this->uri) || preg_match('/^_:/', $this->uri);
    }

    /**
     * Go through the propery values and check if there is one in the specified language.
     * @param string $predicate
     * @param string $language
     * @return bool
     */
    public function hasPropertyInLanguage($predicate, $language)
    {
        foreach ($this->getProperty($predicate) as $value) {
            if ($value instanceof Literal && $value->getLanguage() == $language) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gets the specified property values but filter only those in the specified language.
     * @TODO Rename to getPropertyInLanguage
     * @param string $predicate
     * @param string $language
     * @return RdfObject[]
     */
    public function retrievePropertyInLanguage($predicate, $language)
    {
        $values = [];
        foreach ($this->getProperty($predicate) as $value) {
            if ($value instanceof Literal && $value->getLanguage() == $language) {
                $values[] = $value;
            }
        }
        return $values;
    }

    /**
     * Gets list of all languages that currently exist in the properties of the resource.
     * @TODO Rename to getLanguages
     * @return string[]
     */
    public function retrieveLanguages()
    {
        $languages = [];
        foreach ($this->getProperties() as $property) {
            foreach ($property as $value) {
                if ($value instanceof Literal
                        && $value->getLanguage() !== null
                        && !isset($languages[$value->getLanguage()])) {
                    $languages[$value->getLanguage()] = true;
                }
            }
        }

        return array_keys($languages);
    }

    /**
     * Gets property value and checks if it is only one.
     * @param string $property
     * @return null|string
     * @throws OpenSkosException
     */
    public function getPropertySingleValue($property)
    {
        $values = $this->getProperty($property);

        if (count($values) > 1) {
            throw new OpenSkosException(
                'Multiple values found for property "' . $property . '" while a single one was requested.'
                . ' Values ' . implode(', ', $values)
            );
        }

        if (!empty($values)) {
            return $values[0];
        } else {
            return null;
        }
    }

    /**
     * Gets property value and implodes it if multiple values are found.
     * @param string $property
     * @param string $language
     * @return string
     */
    public function getPropertyFlatValue($property, $language = null)
    {
        if (!empty($language)) {
            $values = $this->retrievePropertyInLanguage($property, $language);
        } else {
            $values = $this->getProperty($property);
        }

        return implode(', ', $values);
    }

    /**
     * Gets the resource in simple flat array with all (or filtered) properties.
     * @param array $filter , optional
     * @param string $language , optional
     * @return array
     */
    public function toFlatArray($filter = [], $language = null)
    {
        $result = [];

        foreach (array_keys($this->getProperties()) as $property) {
            if (empty($filter) || in_array($property, $filter)) {
                $result[Namespaces::shortenProperty($property)] = $this->getPropertyFlatValue($property, $language);
            }
        }

        // @TODO uri and caption are out of scope here, but really handful.
        if (empty($filter) || in_array('uri', $filter)) {
            $result['uri'] = $this->getUri();
        }
        if (empty($filter) || in_array('caption', $filter)) {
            $result['caption'] = $this->getCaption($language);
        }

        return $result;
    }

    /**
     *
     * @return \DateTime|null
     */
    public function getLatestModifyDate()
    {
        $dates = $this->getProperty(Namespaces\DcTerms::MODIFIED);
        if (empty($dates)) {
            return;
        }

        $latestDate = null;
        foreach ($dates as $date) {
            /* @var $date \OpenSkos2\Rdf\Literal */
            /* @var $dateTime \DateTime */
            $dateTime = $date->getValue();
            if (!$latestDate || $dateTime->getTimestamp() > $latestDate->getTimestamp()) {
                $latestDate = $dateTime;
            }
        }

        return $latestDate;
    }

    /**
     * @TODO Separate in StatusAwareResource class or something like that
     * @param string &$predicate
     * @param RdfObject &$value
     */
    protected function handleSpecialProperties(&$predicate, RdfObject &$value)
    {
        // @TODO find better way and prevent hidden altering of the properties values in the Resource class.

        // Status is always transformed to lowercase.
        if ($predicate == OpenSkos::STATUS) {
            $value->setValue(strtolower($value->getValue()));
        }
    }
}
