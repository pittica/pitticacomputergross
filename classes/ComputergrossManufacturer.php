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
 * Manufacturer object model.
 *
 * @category ObjectModel
 * @package  Pittica/PrestaShop/Module/Computergross
 * @author   Lucio Benini <info@pittica.com>
 * @license  http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link     https://github.com/pittica/prestashop-computergross/blob/main/classes/ComputergrossManufacturer.php
 * @since    1.0.0
 */
class ComputergrossManufacturer extends ObjectModel
{
    const TABLE_NAME = 'pittica_computergross_manufacturer';

    /**
     * {@inheritDoc}
     *
     * @var   boolean
     * @since 1.0.0
     */
    public $force_id = true;

    /**
     * Computer Gross name.
     *
     * @var   string
     * @since 1.0.0
     */
    public $name;

    /**
     * {@inheritDoc}
     *
     * @var   array
     * @since 1.0.0
     */
    public static $definition = array(
        'table'   => self::TABLE_NAME,
        'primary' => 'id_manufacturer',
        'fields'  => array(
            'name' => array(
                'type' => self::TYPE_STRING, 'required' => false, 'size' => 255, 'lang' => false
            )
        )
    );

    /**
     * Finds the Manufacturer object using the name.
     *
     * @param string $name Name.
     *
     * @return Manufacturer
     * @since  1.0.0
     */
    public static function findByName($name)
    {
        $query = new DbQuery();
        $query
            ->select('cc.id_manufacturer')
            ->from(self::TABLE_NAME, 'cc')
            ->where('cc.name = "' . pSQL(trim($name)) . '"');

        $id = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);

        if ($id) {
            return new Manufacturer($id);
        } else {
            return null;
        }
    }

    /**
     * Finds the Manufacturer object using the name or creates it.
     *
     * @param string $name Name.
     *
     * @return Manufacturer
     * @since  1.0.0
     */
    public static function findOrCreateByName($name)
    {
        $manufacturer = self::findByName($name);

        if ($manufacturer === null) {
            $manufacturer = new Manufacturer();
            
            $manufacturer->active       = true;
            $manufacturer->name         = $name;
            $manufacturer->link_rewrite = array();

            foreach (Language::getIDs() as $language) {
                $manufacturer->link_rewrite[$language] = Tools::link_rewrite($name);
            }

            $manufacturer->add();

            $cgc = new self($manufacturer->id);
            $cgc->id   = $manufacturer->id;
            $cgc->name = $name;

            $cgc->add();
        }

        return $manufacturer;
    }
}
