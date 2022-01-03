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

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ComputergrossObjectModel.php';

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
class ComputergrossManufacturer extends ComputergrossObjectModel
{
    const TABLE_NAME = 'pittica_computergross_manufacturer';

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
                'type' => self::TYPE_STRING, 'required' => false
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
        $id = self::findIdByName($name);

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
            
            $cgc->setNames($name);

            $cgc->add();
        }

        return $manufacturer;
    }
}
