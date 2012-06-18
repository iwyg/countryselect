<?php
/*
vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4
*/

/**
 * @package Fields
 * @author thomas appel <mail@thomas-appel.com>

 * Displays <a href="http://opensource.org/licenses/gpl-3.0.html">GNU Public License</a>
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 */

(!defined('__IN_SYMPHONY__')) && die('You cannot directly access this file');
require_once CORE . '/class.cacheable.php';

/**
 * fieldCountrySelect
 * This field provides a selectbox containing countrynames.
 *
 * @uses Field
 * @release 1
 * @author  Thomas Appel <mail@thomas-appel.com>
 */
class fieldCountrySelect extends Field
{
    /**
     * Cache identifier
     */
    const COUNTRY_LIST = 'countrylist';

    /**
     * @see toolkit/field#__construct()
     */
    public function __construct()
    {
        parent::__construct();

        $this->_name = __('Country Select');
        $this->_required = true;

        $this->_cache = new Cacheable(Symphony::Database());
        $this->getCountries();
    }

    /**
     * @see toolkit/field#canFilter()
     */
    public function canFilter()
    {
        return true;
    }

    /**
     * @see toolkit/field#isSortable()
     */
    public function isSortable()
    {
        return true;
    }

    /**
     * @see toolkit/field#mustBeUnique()
     */
    public function mustBeUnique()
    {
        return false;
    }

    /**
     * @see toolkit.field#allowDatasourceOutputGrouping()
     */
    public function allowDatasourceOutputGrouping()
    {
        return true;
    }

    /**
     * @see toolkit/field#allowDatasourceParamOutput()
     */
    public function allowDatasourceParamOutput()
    {
        return true;
    }

    /**
     * buildList()
     *
     * Construct the countryselect box
     *
     * @param {String}  $name       the fields name
     * @param {Mixed}   countrycode string or array of countrycodes
     * @param {Array}   $countries  list of countries
     * @param {Boolean} $multiple   constructs a multiple selctbox
     * @param {Array}   $exclude    list of countrycodes to be excluded
     * @access public
     * @return {XMLElement} the selectbox
     */
    public function buildList($name, $index, $countries = null, $multiple = false, $exclude = null)
    {
        $data = is_array($countries) ? $countries : $this->getCountries();
        $options = array();
        $tcs = array();

        foreach ($data as $i => $country) {
            if (is_array($exclude) && in_array($i, $exclude)) {
                continue;
            }
            $tc =  __($country);
            $tcs[$i] = $tc;
        }

        asort($tcs, SORT_LOCALE_STRING);

        foreach ($tcs as $l => $c) {
            $is_selected = ($multiple && is_array($index)) ? in_array($l, $index) : $index == $l;
            $options[] = array($l, $is_selected ? true : false, General::sanitize($c));
        }
        $list = Widget::Select($name, $options, $multiple ? array('class' => 'multiselect', 'multiple' => 'multiple') : null);

        return $list;
    }

    /**
     * @see toolkit/field#displaySettingsPanel()
     */
    public function displaySettingsPanel(&$wrapper, $errors = null)
    {
        parent::displaySettingsPanel($wrapper, $errors);

        Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/countryselect/assets/symphony.multiselect.css', 'screen', 100, false);
        Administration::instance()->Page->addScriptToHead(URL . '/extensions/countryselect/assets/symphony.multiselect.js', 112, false);
        Administration::instance()->Page->addScriptToHead(URL . '/extensions/countryselect/assets/countryselect.settings.js', 113, false);

        $countries = $this->getCountries();

        $exclude = explode(',', $this->get('exclude_location'));

        $fieldset = new XMLElement('fieldset', null, array('class' => 'two columns'));

        $div = new XMLElement('div', null, array('class' => 'column'));
        $label = Widget::Label(__('default country'));
        $list = $this->buildList('fields[' . $this->get('sortorder') . '][default_location]', $this->get('default_location'), $countries, false, empty($exclude) ? false : $exclude);
        $label->appendChild($list);
        $div->appendChild($label);
        $fieldset->appendChild($div);

        $div = new XMLElement('div', null, array('class' => 'column'));
        $label = Widget::Label(__('exclude countries'));

        $div->appendChild($label);
        $fieldset->appendChild($div);
        $list = $this->buildList('fields[' . $this->get('sortorder') . '][exclude_location][]', $exclude, $countries, true);
        $label->appendChild($list);
        $wrapper->appendChild($fieldset);

        $fieldset = new XMLElement('div', null, array('class' => 'two columns'));
        $this->appendRequiredCheckBox($fieldset);

        $this->appendShowColumnCheckbox($fieldset);

        $wrapper->appendChild($fieldset);
    }

    /**
     * @see toolkit/field#commit()
     */
    public function commit()
    {
        if (!parent::commit()) return false;

        $id = $this->get('id');

        if (!$id) {
            return false;
        }
        $exclude = $this->get('exclude_location');
        $fields = array();
        $fields['field_id'] = $id;
        $fields['default_location'] = $this->get('default_location');
        $fields['exclude_location'] = empty($exclude) ? null : implode(',', $exclude);

        Symphony::Database()->query("DELETE FROM `tbl_fields_".$this->handle()."` WHERE `field_id` = '$id' LIMIT 1");
        return Symphony::Database()->insert($fields, 'tbl_fields_' . $this->handle());
    }

    /**
     * @see toolkit/field#displayPublishPanel()
     */
    public function displayPublishPanel(&$wrapper, $data=NULL, $flagWithError=NULL, $fieldnamePrefix=NULL, $fieldnamePostfix=NULL, $entry_id=NULL, $fieldnameSuffix=NULL )
    {
        $label = Widget::Label($this->get('label'));
        if ($this->get('required') != 'yes') $label->appendChild(new XMLElement('i', __('Optional')));

        $index;
        $countries = $this->getCountries();
        if (isset($data['value'])) {
            $index = $data['value'];
        } else {
            $index = $this->get('default_location');
        }
        $select = $this->buildList('fields[' . $this->get('element_name').']', $index, $countries, false, explode(',', $this->get('exclude_location')));
        $label->appendChild($select);
        $wrapper->appendChild($label);
    }

    /**
     * getCountries()
     * Checks if `countrylist` key exists in `tbl_cache`.
     * @access public
     * @return {Array} Country list
     */
    public function getCountries()
    {
        if (!$this->_cache->check(self::COUNTRY_LIST)) {
            include_once EXTENSIONS . '/countryselect/lib/class.countrylist.php';
            $countries = Countrylist::get();
            $this->_cache->write(self::COUNTRY_LIST, json_encode($countries));

            return $countries;
        }
        $cdata = $this->_cache->check(self::COUNTRY_LIST);
        return json_decode($cdata['data'], true);
    }

    /**
     * @see: toolkit.field#prepareTableValue()
     */
    public function prepareTableValue($data, XMLElement $link = null, $translate = true)
    {
        $string = null;
        $countries = $this->getCountries();

        if (isset($data['value'])) {
            $string = $translate ? __($countries[$data['value']]) : $countries[$data['value']];

            if (!is_null($link)) {
                $link->setValue($string);
                $string = $link;
            }
        }
        return $string;
    }

    /**
     * @see: toolkit.field#appendFormattedElement()
     */
    public function appendFormattedElement(&$wrapper, $data)
    {
        $dataNode = new XMLElement($this->get('element_name'), ($encode ? General::sanitize($this->prepareTableValue($data, null, $entry_id, false)) : $this->prepareTableValue($data, null, $entry_id, false)));
        $dataNode->setAttribute('alpha-2', $data['value']);
        $wrapper->appendChild($dataNode);
    }
}
