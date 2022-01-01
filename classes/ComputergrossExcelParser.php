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

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ComputergrossCategory.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ComputergrossManufacturer.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Excel parser.
 *
 * @category ObjectModel
 * @package  Pittica/PrestaShop/Module/Computergross
 * @author   Lucio Benini <info@pittica.com>
 * @license  http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link     https://github.com/pittica/prestashop-computergross/blob/main/classes/ComputergrossCategory.php
 * @since    1.0.0
 */
class ComputergrossExcelParser
{
    /**
     * XLS file path.
     *
     * @var   string
     * @since 1.0.0
     */
    public $file;

    /**
     * Raw data lines.
     *
     * @var   array
     * @since 1.0.0
     */
    protected $lines;

    /**
     * Creates an instance of the ComputergrossExcelParser object.
     *
     * @param string $file XLS file path.
     *
     * @since 1.0.0
     */
    public function __construct($file)
    {
        $this->file  = $file;
        $this->lines = array();
    }

    /**
     * Reads the file.
     *
     * @return ComputergrossExcelParser
     * @since  1.0.0
     */
    public function parse()
    {
        $reader = IOFactory::createReaderForFile($this->file);
        $reader->setReadDataOnly(true);
        $excel = $reader->load($this->file);

        $worksheet = $excel->getActiveSheet();
        $content = $worksheet->toArray();

        if (!empty($content)) {
            unset($content[0]);
        }

        $this->lines = array_merge($this->lines, $content);

        return $this;
    }

    /**
     * Update the products.
     *
     * @return ComputergrossExcelParser
     * @since  1.0.0
     */
    public function updateProducts()
    {
        if (!empty($this->lines)) {
            $languages = Language::getIDs();
            $markup    = (float) Configuration::get('PITTICA_COMPUTERGROSS_MARKUP');
            $tax       = (int) Configuration::get('PITTICA_COMPUTERGROSS_TAX');
            $supplier  = (int) Configuration::get('PITTICA_COMPUTERGROSS_SUPPLIER');
            $config    = Configuration::get('PITTICA_COMPUTERGROSS_CARRIERS');
            $carriers  = array();
    
            if ($config) {
                foreach (unserialize($config) as $v) {
                    $carriers[$v] = (int) $v;
                }
            }
            
            $carriers = array_flip($carriers);

            foreach ($this->lines as $line) {
                $reference   = str_replace(array('='), '', $line[6]);
                $product     = $this->getProduct($reference);
                $combination = $this->getCombination($reference);
                $price       = (float) $line[12];
                $price       = $price + (($price / 100.0) * $markup);

                if ($product || $combination) {
                    if ($product) {
                        $product = $this->fillData($line, $product, $price);

                        $product->addSupplierReference(
                            $supplier,
                            0,
                            $product->supplier_reference,
                            $product->wholesale_price
                        );
                        $product->setCarriers(array_flip($carriers));

                        $product->save();
                    
                        StockAvailable::setQuantity($product->id, 0, $product->quantity, null, false);
                    }

                    if ($combination) {
                        $combination = $this->fillData($line, $combination, $price);

                        $product = new Product($combination->id_product);

                        $product->addSupplierReference(
                            $supplier,
                            $combination->id,
                            $combination->supplier_reference,
                            $combination->wholesale_price
                        );
                        $product->setCarriers(array_flip($carriers));

                        $combination->save();

                        StockAvailable::setQuantity($product->id, $combination->id, $combination->quantity, null, false);
                    }
                } else {
                    $category     = ComputergrossCategory::findOrCreateByName($line[0]);
                    $manufacturer = ComputergrossManufacturer::findOrCreateByName($line[5]);
                    $product      = new Product();

                    $product = $this->fillData($line, $product, $price);
                        
                    $product->name                      = array();
                    $product->description               = array();
                    $product->description_short         = array();
                    $product->link_rewrite              = array();
                    $product->id_category_default       = (int) $category->id;
                    $product->id_manufacturer           = (int) $manufacturer->id;
                    $product->id_supplier               = $supplier;
                    $product->id_tax_rules_group        = $tax;
                    $product->reference                 = $reference;
                    $product->manufacturer_name         = $manufacturer->name;
                    $product->advanced_stock_management = true;
                    $product->active                    = false;
                    $product->condition                = strpos($line[0], 'REFURBISHED') === false ? 'new' : 'refurbished';
                    $product->show_condition            = true;
                        
                    foreach ($languages as $language) {
                        $product->name[$language]              = $line[4];
                        $product->description[$language]       = '';
                        $product->description_short[$language] = '';
                        $product->link_rewrite[$language]      = Tools::link_rewrite($line[4]);
                    }
                        
                    $product->add();
                        
                    $product->addToCategories((int) $category->id);

                    $product->addSupplierReference(
                        $supplier,
                        0,
                        $product->supplier_reference,
                        $product->wholesale_price
                    );
                    $product->setCarriers(array_flip($carriers));
                        
                    $product->save();

                    StockAvailable::setQuantity($product->id, 0, $product->quantity, null, false);
                }
            }
        }

        return $this;
    }

    /**
     * Fills the given object.
     *
     * @param array               $line   Document data.
     * @param Product|Combination $object The Product or Combination.
     * @param float               $price  Product price.
     *
     * @return Product|Combination
     * @since  1.0.0
     */
    protected function fillData($line, $object, $price)
    {
        $object->quantity           = (int) $line[8];
        $object->supplier_reference = $line[3];
        $object->ean13              = $line[7];
        $object->wholesale_price    = (float) $line[13];
        $object->price              = $price;

        return $object;
    }

    /**
     * Gets a Product object from the given product reference.
     *
     * @param string $reference Product reference.
     *
     * @return Product|null
     * @since  1.0.0
     */
    protected function getProduct($reference)
    {
        if (!empty($reference)) {
            $query = new DbQuery();
            $query
                ->select('p.id_product')
                ->from('product', 'p')
                ->where('p.reference LIKE "' . pSQL(trim($reference)) . '"');

            $id = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);

            if ($id) {
                return new Product($id);
            }
        }
        
        return null;
    }

    /**
     * Gets a Combination object from the given product reference.
     *
     * @param string $reference Product reference.
     *
     * @return Combination|null
     * @since  1.0.0
     */
    protected function getCombination($reference)
    {
        $query = new DbQuery();
        $query
            ->select('pa.id_product_attribute')
            ->from('product_attribute', 'pa')
            ->where('pa.reference LIKE "' . pSQL(trim($reference)) . '"');

        $id = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);

        if ($id) {
            return new Combination($id);
        } else {
            return null;
        }
    }
}
