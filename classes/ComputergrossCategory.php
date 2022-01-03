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
 * Category object model.
 *
 * @category ObjectModel
 * @package  Pittica/PrestaShop/Module/Computergross
 * @author   Lucio Benini <info@pittica.com>
 * @license  http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link     https://github.com/pittica/prestashop-computergross/blob/main/classes/ComputergrossCategory.php
 * @since    1.0.0
 */
class ComputergrossCategory extends ComputergrossObjectModel
{
    const TABLE_NAME = 'pittica_computergross_category';

    /**
     * {@inheritDoc}
     *
     * @var   array
     * @since 1.0.0
     */
    public static $definition = array(
        'table'   => self::TABLE_NAME,
        'primary' => 'id_category',
        'fields'  => array(
            'name' => array(
                'type' => self::TYPE_STRING, 'required' => false
            )
        )
    );

    /**
     * Finds the Category object using the name.
     *
     * @param string $name Name.
     *
     * @return Category
     * @since  1.0.0
     */
    public static function findByName($name)
    {
        $id = self::findIdByName($name);

        if ($id) {
            return new Category($id);
        } else {
            return null;
        }
    }

    /**
     * Finds the Category object using the name or creates it.
     *
     * @param string $name Name.
     *
     * @return Category
     * @since  1.0.0
     */
    public static function findOrCreateByName($name)
    {
        $category = self::findByName($name);

        if ($category === null) {
            $category = new Category();
            
            $category->active       = true;
            $category->name         = array();
            $category->link_rewrite = array();
            $category->parent       = (int) Configuration::get('PS_HOME_CATEGORY');

            foreach (Language::getIDs() as $language) {
                $category->name[$language]         = $name;
                $category->link_rewrite[$language] = Tools::link_rewrite($name);
            }

            $category->add();

            $cgc = new self($category->id);
            $cgc->id   = $category->id;

            $cgc->setNames($name);

            $cgc->add();
        }

        return $category;
    }
}
