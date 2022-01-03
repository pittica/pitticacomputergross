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
 * @link      https://github.com/pittica/prestashop-cComputergross
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use Symfony\Component\Form\Extension\Core\Type\TextType;

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes'. DIRECTORY_SEPARATOR . 'ComputergrossCategory.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes'. DIRECTORY_SEPARATOR . 'ComputergrossExcelParser.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes'. DIRECTORY_SEPARATOR . 'ComputergrossManufacturer.php';

/**
 * Connector module class.
 *
 * @category Module
 * @package  Pittica/PrestaShop/Module/Computergross
 * @author   Lucio Benini <info@pittica.com>
 * @license  http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link     https://github.com/pittica/prestashop-Computergross/blob/main/pitticaComputergross.php
 * @since    1.0.0
 */
class PitticaComputergross extends Module
{
    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->name          = 'pitticacomputergross';
        $this->tab           = 'market_place';
        $this->version       = '1.1.0';
        $this->author        = 'Pittica';
        $this->need_instance = 1;
        $this->bootstrap     = 1;

        parent::__construct();

        $this->displayName = 'Computer Gross';
        $this->description = $this->l('Computer Gross connector.');

        $this->ps_versions_compliancy = array(
            'min' => '1.7.7',
            'max' => _PS_VERSION_
        );
    }

    /**
     * {@inheritDoc}
     *
     * @return boolean
     * @since  1.0.0
     */
    public function install()
    {
        include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'sql'. DIRECTORY_SEPARATOR . 'install.php';

        return parent::install() &&
            $this->registerHook('actionCategoryFormBuilderModifier') &&
            $this->registerHook('actionManufacturerFormBuilderModifier') &&
            $this->registerHook('actionObjectManufacturerDeleteAfter') &&
            $this->registerHook('actionAfterCreateManufacturerFormHandler') &&
            $this->registerHook('actionAfterUpdateManufacturerFormHandler') &&
            $this->registerHook('actionObjectCaegoryDeleteAfter') &&
            $this->registerHook('actionAfterCreateCategoryFormHandler') &&
            $this->registerHook('actionAfterUpdateCategoryFormHandler');
    }

    /**
     * {@inheritDoc}
     *
     * @return boolean
     * @since  1.0.0
     */
    public function uninstall()
    {
        include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'sql'. DIRECTORY_SEPARATOR . 'uninstall.php';

        Configuration::deleteByName('PITTICA_COMPUTERGROSS_CARRIERS');
        Configuration::deleteByName('PITTICA_COMPUTERGROSS_SUPPLIER');
        Configuration::deleteByName('PITTICA_COMPUTERGROSS_MARKUP');
        Configuration::deleteByName('PITTICA_COMPUTERGROSS_TAX');

        return parent::uninstall();
    }

    /**
     * Hook "actionCategoryFormBuilderModifier".
     *
     * @param array $params Hook parameters.
     *
     * @return string
     * @since  1.0.0
     */
    public function hookActionCategoryFormBuilderModifier($params)
    {
        $this->_setFormFields($params['form_builder'], !empty($params['id']) ? new ComputergrossCategory((int) $params['id']) : null, $params['data']);
    }

    /**
     * Hook "actionManufacturerFormBuilderModifier".
     *
     * @param array $params Hook parameters.
     *
     * @return string
     * @since  1.0.0
     */
    public function hookActionManufacturerFormBuilderModifier($params)
    {
        $this->_setFormFields($params['form_builder'], !empty($params['id']) ? new ComputergrossManufacturer((int) $params['id']) : null, $params['data']);
    }

    /**
     * Hook "actionAfterCreateManufacturerFormHandler".
     *
     * @param array $params Hook parameters.
     *
     * @return string
     * @since  1.0.0
     */
    public function hookActionAfterCreateManufacturerFormHandler($params)
    {
        $id = (int) $params['id'];

        return $this->_handleObjectUpdate(new ComputergrossManufacturer($id), $id, Tools::getValue('manufacturer'));
    }

    /**
     * Hook "actionAfterUpdateManufacturerFormHandler".
     *
     * @param array $params Hook parameters.
     *
     * @return string
     * @since  1.0.0
     */
    public function hookActionAfterUpdateManufacturerFormHandler($params)
    {
        $id = (int) $params['id'];

        return $this->_handleObjectUpdate(new ComputergrossManufacturer($id), $id, Tools::getValue('manufacturer'));
    }

    /**
     * Hook "actionAfterCreateCategoryFormHandler".
     *
     * @param array $params Hook parameters.
     *
     * @return string
     * @since  1.0.0
     */
    public function hookActionAfterCreateCategoryFormHandler($params)
    {
        $id = (int) $params['id'];

        return $this->_handleObjectUpdate(new ComputergrossCategory($id), $id, Tools::getValue('category'));
    }

    /**
     * Hook "actionAfterUpdateCategoryFormHandler".
     *
     * @param array $params Hook parameters.
     *
     * @return string
     * @since  1.0.0
     */
    public function hookActionAfterUpdateCategoryFormHandler($params)
    {
        $id = (int) $params['id'];

        return $this->_handleObjectUpdate(new ComputergrossCategory($id), $id, Tools::getValue('category'));
    }

    /**
     * Hook "actionObjectCategoryDeleteAfter".
     *
     * @param array $params Hook parameters.
     *
     * @return string
     * @since  1.0.0
     */
    public function hookActionObjectCategoryDeleteAfter($params)
    {
        $category = new ComputergrossCategory((int) $params['object']->id);

        return $category->delete();
    }

    /**
     * Hook "actionObjectManufacturerDeleteAfter".
     *
     * @param array $params Hook parameters.
     *
     * @return string
     * @since  1.0.0
     */
    public function hookActionObjectManufacturerDeleteAfter($params)
    {
        $manufacturer = new ComputergrossManufacturer((int) $params['object']->id);

        return $manufacturer->delete();
    }
    
    /**
     * {@inheritDoc}
     *
     * @return string
     * @since  1.0.0
     */
    public function getContent()
    {
        $output = '';
        
        if (Tools::isSubmit('btnSubmit')) {
            $output .= $this->_postProcess();
        } else {
            $output .= '<br />';
        }
        
        return $output . $this->renderForm();
    }

    /**
     * Processes the POST action in module configuration.
     *
     * @return string
     * @since  1.0.0
     */
    protected function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('PITTICA_COMPUTERGROSS_SUPPLIER', (int) Tools::getValue('PITTICA_COMPUTERGROSS_SUPPLIER'));
            Configuration::updateValue('PITTICA_COMPUTERGROSS_MARKUP', (float) Tools::getValue('PITTICA_COMPUTERGROSS_MARKUP'));
            Configuration::updateValue('PITTICA_COMPUTERGROSS_TAX', (int) Tools::getValue('PITTICA_COMPUTERGROSS_TAX'));

            $file = Tools::fileAttachment('PITTICA_COMPUTERGROSS_FILE');

            if (!empty($file['content'])) {
                $parser = new ComputergrossExcelParser($file['tmp_name']);

                $parser
                    ->parse()
                    ->updateProducts();
            }

            $this->setCarrierFieldValues('PITTICA_COMPUTERGROSS_CARRIERS');
        }
        
        return $this->displayConfirmation($this->trans('Settings updated', array(), 'Admin.Global'));
    }
    
    /**
     * Renders settings form.
     *
     * @return void
     * @since  1.0.0
     */
    protected function renderForm()
    {
        $suppliers = array_merge(array('' => '-'), Supplier::getSuppliers());
        $carriers  = Carrier::getCarriers($this->context->language->id);
        $taxes     = TaxRulesGroup::getTaxRulesGroupsForOptions();
        $values    = array(
            'PITTICA_COMPUTERGROSS_SUPPLIER' => (int) Configuration::get('PITTICA_COMPUTERGROSS_SUPPLIER'),
            'PITTICA_COMPUTERGROSS_MARKUP'   => (float) Configuration::get('PITTICA_COMPUTERGROSS_MARKUP'),
            'PITTICA_COMPUTERGROSS_TAX'      => (int) Configuration::get('PITTICA_COMPUTERGROSS_TAX')
        );

        $this->getCarrierFieldValues($carriers, $values);

        $helper                           = new HelperForm();
        $helper->show_toolbar             = false;
        $helper->table                    = $this->table;
        $helper->module                   = $this;
        $helper->default_form_language    = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $this->fields_form                = array();
        $helper->id                       = (int) Tools::getValue('id_carrier');
        $helper->identifier               = $this->identifier;
        $helper->submit_action            = 'btnSubmit';
        $helper->currentIndex             = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module= ' . $this->tab . '&module_name=' . $this->name;
        $helper->token                    = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars                 = array(
            'fields_value' => $values,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        
        return $helper->generateForm(
            array(
                
                array(
                    'form' => array(
                        'legend' => array(
                            'title' => 'Computer Gross'
                        ),
                        'input' => array(
                            array(
                                'type'          => 'file',
                                'label'         => $this->l('XLS File'),
                                'name'          => 'PITTICA_COMPUTERGROSS_FILE',
                                'display_image' => true
                            )
                        ),
                        'submit' => array(
                            'title' => $this->trans('Save', array(), 'Admin.Actions')
                        )
                    )
                ),
                array(
                    'form' => array(
                        'legend' => array(
                            'title' => 'Computer Gross'
                        ),
                        'input' => array(
                            array(
                                'type'    => 'select',
                                'label'   => $this->trans('Supplier', array(), 'Admin.Global'),
                                'name'    => 'PITTICA_COMPUTERGROSS_SUPPLIER',
                                'options' => array(
                                    'query' => $suppliers,
                                    'id'    => 'id_supplier',
                                    'name'  => 'name'
                                )
                            ),
                            array(
                                'type'    => 'select',
                                'label'   => $this->trans('Taxes', array(), 'Admin.Global'),
                                'name'    => 'PITTICA_COMPUTERGROSS_TAX',
                                'options' => array(
                                    'query' => $taxes,
                                    'id'    => 'id_tax_rules_group',
                                    'name'  => 'name'
                                )
                            ),
                            array(
                                'type'   => 'number',
                                'label'  => $this->l('Markup'),
                                'name'   => 'PITTICA_COMPUTERGROSS_MARKUP',
                                'desc'   => $this->l('Default markup percentage.'),
                                'min'    => 0,
                                'step'   => 0.1,
                                'suffix' => '%'
                            ),
                            array(
                                'type'   => 'checkbox',
                                'label'  => $this->trans('Carriers', array(), 'Admin.Shipping.Feature'),
                                'name'   => 'PITTICA_COMPUTERGROSS_CARRIERS',
                                'values' => array(
                                    'query' => $carriers,
                                    'id'    => 'id_carrier',
                                    'name'  => 'name'
                                )
                            )
                        ),
                        'submit' => array(
                            'title' => $this->trans('Save', array(), 'Admin.Actions')
                        )
                    )
                )
            )
        );
    }

    /**
     * Generates the secuirty token.
     *
     * @return string
     * @since  1.0.0
     */
    public function getToken()
    {
        return Tools::hash(Configuration::get('PS_SHOP_DOMAIN'));
    }

    /**
     * Sets the carriers form field values in configuration.
     *
     * @param string $key Configuration key.
     *
     * @return void
     * @since  1.0.0
     */
    protected function setCarrierFieldValues($key)
    {
        $values   = array();
        $carriers = Carrier::getCarriers($this->context->language->id);

        foreach ($carriers as $carrier) {
            if (Tools::getValue($key . '_' . $carrier['id_carrier']) === 'on') {
                $values[] = (int) $carrier['id_carrier'];
            }
        }

        Configuration::updateValue($key, serialize($values));
    }

    /**
     * Gets the carriers form field values from configuration.
     *
     * @param array $carriers Carriers.
     * @param array $values   Form values.
     *
     * @return void
     * @since  1.0.0
     */
    protected function getCarrierFieldValues($carriers, &$values)
    {
        $config = unserialize(Configuration::get('PITTICA_COMPUTERGROSS_CARRIERS'));

        if ($config) {
            $config = array_flip($config);

            foreach ($carriers as $carrier) {
                if (array_key_exists((int) $carrier['id_carrier'], $config)) {
                    $values['PITTICA_COMPUTERGROSS_CARRIERS_' . $carrier['id_carrier']] = true;
                }
            }
        }
    }
    
    /**
     * Handles the POST contents and updates the object data.
     *
     * @param ComputergrossManufacturer|ComputergrossCategory $object Main object.
     * @param int                                             $id     Main object ID.
     * @param array                                           $data   Form data.
     *
     * @return bool
     * @since  1.0.0
     */
    private function _handleObjectUpdate($object, $id, $data)
    {
        if (!empty($data['computergross_name'])) {
            $object->setNames($data['computergross_name']);
        }
        
        if (!$object->id) {
            $object->id = (int) $id;
            
            return $object->add();
        } else {
            return $object->update();
        }
    }

    /**
     * Sets the form fields.
     *
     * @param FormBuilder                                     $builder Form builder.
     * @param ComputergrossManufacturer|ComputergrossCategory $object  Object which contains data.
     * @param array                                           $data    Form data.
     *
     * @return FormBuilder
     * @since  1.0.0
     */
    private function _setFormFields($builder, $object, &$data)
    {
        if ($object) {
            $data['computergross_name'] = implode(', ', $object->getNames());
        }

        $builder
            ->add(
                'computergross_name',
                TextType::class,
                array(
                    'label'    => $this->l('Computer Gross Name'),
                    'required' => false,
                    'attr' => array(
                        'class' => 'js-taggable-field',
                        'placeholder' => $this->trans('Add tag', array(), 'Admin.Actions')
                    )
                )
            )
            ->setData($data);

        return $builder;
    }
}
