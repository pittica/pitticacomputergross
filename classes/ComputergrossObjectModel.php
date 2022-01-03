<?php

/**
 * PrestaShop Module - pitticacomputergross
 *
 * Copyright 2022 Pittica S.r.l.
 *
 * @category  Module
 * @package   Pittica/PrestaShop/Module/Computergross
 * @author    Lucio Benini <info@pittica.com>
 * @copyright 2022 Pittica S.r.l.
 * @license   http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link      https://github.com/pittica/prestashop-computergross
 */

/**
 * Base object model.
 *
 * @category ObjectModel
 * @package  Pittica/PrestaShop/Module/Computergross
 * @author   Lucio Benini <info@pittica.com>
 * @license  http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link     https://github.com/pittica/prestashop-computergross/blob/main/classes/ComputergrossManufacturer.php
 * @since    1.1.0
 */
abstract class ComputergrossObjectModel extends ObjectModel
{
    /**
     * {@inheritDoc}
     *
     * @var   boolean
     * @since 1.1.0
     */
    public $force_id = true;

    /**
     * Computer Gross name.
     *
     * @var   string
     * @since 1.1.0
     */
    public $name;

    /**
     * Finds the names.
     *
     * @return array
     * @since  1.1.0
     */
    public function getNames()
    {
        try {
            $json = json_decode($this->name);

            return is_array($json) ? $json : array();
        } catch (Exception $ex) {
            return array();
        }
    }

    /**
     * Sets the names.
     *
     * @param string $names Names to set.
     *
     * @return ComputergrossObjectModel
     * @since  1.1.0
     */
    public function setNames($names)
    {
        try {
            $this->name = json_encode(array_unique(explode(', ', $names)));
        } catch (Exception $ex) {
            $this->name = null;
        }
        
        return $this;
    }

    /**
     * Finds the object ID using the name.
     *
     * @param string $name Name.
     *
     * @return int
     * @since  1.1.0
     */
    protected static function findIdByName($name)
    {
        $query = new DbQuery();
        $query
            ->select('cc.' . static::$definition['primary'])
            ->from(static::TABLE_NAME, 'cc')
            ->where('JSON_SEARCH(name, "all", "' . pSQL(trim($name)) . '") IS NOT NULL');
        
        return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }
}
