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
cLass extension_countryselect extends Extension
{

    /**
     * @see toolkit.extension#install()
     */
	public function install()
	{
		Symphony::Database()->query(
			"CREATE TABLE IF NOT EXISTS `tbl_fields_countryselect` (
				`id` int(11) unsigned NOT NULL auto_increment,
				`field_id` int(11) unsigned NOT NULL,
				`default_location` varchar(255) default NULL,
				`exclude_location` varchar(8000) default NULL,
				PRIMARY KEY (`id`),
				KEY `field_id` (`field_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"
		);
		return true;
	}

    /**
     * @see toolkit.extension#uninstall()
     */
    public function uninstall()
    {
        try {
            Symphony::Database()->query("DROP TABLE `tbl_fields_countryselect`");
        } catch (DatabaseException $db_err) {

        }
        return true;
    }
}
