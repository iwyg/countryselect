/*
vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4
*/

/**
 * @package Assets
 * @author thomas appel <mail@thomas-appel.com>

 * Displays <a href="http://opensource.org/licenses/gpl-3.0.html">GNU Public License</a>
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 */
(function ($, Symphony) {
	jQuery(function () {
		Symphony.Language.add({'Countries': false});
		$('select.multiselect').multiselect({
			buttonText: Symphony.Language.get('Countries')
		});
		$('.frame').on('constructshow.duplicator', function (event) {
			$(event.target).find('select.multiselect').multiselect({
				buttonText: Symphony.Language.get('Countries')
			});
		});
	});
}(this.jQuery, this.Symphony));
