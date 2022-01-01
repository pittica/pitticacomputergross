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
 * Category object model.
 *
 * @category ObjectModel
 * @package  Pittica/PrestaShop/Module/Computergross
 * @author   Lucio Benini <info@pittica.com>
 * @license  http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link     https://github.com/pittica/prestashop-computergross/blob/main/classes/ComputergrossCategory.php
 * @since    1.0.0
 */
class ComputergrossCategory extends ObjectModel
{
    const TABLE_NAME = 'pittica_computergross_category';

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
        'primary' => 'id_category',
        'fields'  => array(
            'name' => array(
                'type' => self::TYPE_STRING, 'required' => false, 'size' => 255, 'lang' => false
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
        $query = new DbQuery();
        $query
            ->select('cc.id_category')
            ->from(self::TABLE_NAME, 'cc')
            ->where('cc.name = "' . pSQL(trim($name)) . '"');

        $id = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);

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
            $cgc->name = $name;

            $cgc->add();
        }

        return $category;
    }
}
