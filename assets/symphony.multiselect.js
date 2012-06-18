/*
vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4
*/

/**
 * @package Assets
 * @author thomas appel <mail@thomas-appel.com>

 * Displays <a href="http://opensource.org/licenses/gpl-3.0.html">GNU Public License</a>
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 */
(function (definition) {
	if (typeof define === 'function' && define.amd) {
		define(['jquery'], definition);
	} else {
		definition(this.jQuery);
	}
}(function ($, undefined) {

	var Symphony = this.Symphony;

	Symphony.Language.add({
		'select all' : false,
		'unselect all' : false,
		'{$count} selected': false
	});

	var $win = $(window),
	callback = false,

	selectall_text = Symphony.Language.get('select all'),
	toggle_text = Symphony.Language.get('unselect all'),
	selected_text = '{$count} selected';

	function _delegateEvents() {
		var key, evt, sel, opt, fn, args;
		for (key in this.events) {
			opt = key.split(' ');
			evt = opt[0].split(',').join(' ');
			sel = opt[1];
			fn = $.isFunction(this.events[key]) ? $.proxy(this.events[key], this) : $.proxy(this[this.events[key]], this);
			args = sel ? [evt, sel, fn] : [evt, fn];
			this.element.on.apply(this.element, args);
		}
	}

	function _getButtonText(selected) {
		var postFix = ' (' + Symphony.Language.get(selected_text, {count: selected}) + ')',
		string = (typeof this.settings.buttonText === 'function') ? this.settings.buttonText(this._element) : this.settings.buttonText;
		return string + postFix;
	}
	function _getSelectCount() {
		var selected = this._element.find('option:selected');
		//console.log(selected.get(), selected.length);
		return selected.length;
	}
	function _selectVals(event) {
		event.preventDefault();
		event.stopPropagation();

		var that = this,
		chk = $(event.target),
		option = chk.data('option'),
		li = chk.parent();

		li.toggleClass('selected');
		//console.log(event.target);
		if (chk[0].checked) {
			option.prop('selected', 'selected');
			//option[0].selected = 'selected';
		} else {
			option.removeAttr('selected');
		}
		that._display.text(_getButtonText.call(that, _getSelectCount.call(that)));
		this._element.trigger('change');
	}

	function _selfClose(event) {
		if (event.target !== this.element[0] && event.target !== this._element[0]) {
			this.close();
		}
	}
	function _optionSelect(event) {
		event.stopPropagation();
		var option = $([event.target, event.target.parentNode]).filter(function () {
			if (this.nodeName.toLowerCase() === 'li') {
				return true;
			}
		}), chk = option.find('input[type=checkbox]');

		if (chk[0].checked) {
			chk[0].checked = false;
		} else {
			chk[0].checked = true;
		}
		chk.trigger('change.mscontrols');
	}

	function _makeElement() {
		var list = $('<ul class="dgt-multiselect dgt-multiselect-list"/>'),
		button = $('<button class="dgt-multiselect-list-toggle"><span class="label">' + _getButtonText.call(this, _getSelectCount.call(this)) + '</span><span class="toggle"></span></button>'),
		container = $('<div class="dgt-multiselect dgt-multiselect-container"/>'),
		body = $('<div class="hidden dgt-multiselect dgt-multiselect-listbody"/>'),
		optgroup,
		oli,
		scrollc = $('<div class="dgt-multiselect dgt-multiselect-scroll"/>'),
		li,
		toolbar = $('<div class="toolbar"><a class="select" href="#">' + selectall_text + '</a><a class="unselect" href="#">' + toggle_text + '</a></div>'),
		newopt = false;


		this._display = button.find('.label');

		this._element.find('option').each(function () {
			var opt = $(this);
			if (this.parentNode.tagName.toLowerCase() === 'optgroup') {
				if (!newopt) {
					optgroup = $('<li class="optgroup"><ul class="optgroup"><li class="label">' + this.parentNode.label + '</li></ul></li>');
					optgroup.appendTo(list);
					li = optgroup.find('ul');
				}
				newopt = true;
			} else {
				li = list;
				newopt = false;
			}
			if (!!this.value) {
				oli = $('<li class="option' + (this.selected ? ' selected' : '') + '"><input class="select-control" type="checkbox"' + (this.selected ? ' checked' : '') + '/><span class="label">' + opt.text() + '</span></li>');
				oli.find('input[type=checkbox]').data('option', opt);
			}
			li.append(oli);
		});
		container.append(button);
		container.append(body);
		body.append(toolbar);
		scrollc.append(list);
		body.append(scrollc);
		this._list = body;
		return container;
	}

	function _toggleList(event) {
		event.preventDefault();
		this.element.toggleClass('open');
		this.element.find('.dgt-multiselect-list').toggleClass('hidden');
		this.element.trigger('open.mscontrols');
	}

	function _prevent(event) {
		event.stopPropagation();
	}

	function _ensureEmptySelection() {
		var option = this._element.find('option[value=""]');
		if (_getSelectCount.call(this) === 0) {
			option.attr({'selected': 'selected', 'value': ''});
		} else {
			option.removeAttr('selected');
		}
	}

	function MultiSelect(element, options) {
		var that = this,
		callback = $.proxy(_selfClose, this);
		this.settings = options;
		this._element = element;
		this._element.addClass('dgt-multiselect dgt-select-ref');
		this.element = _makeElement.call(this);
		this.element.insertAfter(this._element);
		_delegateEvents.call(this);

		$win
			.on('selfclose.mscontrols', '.dgt-multiselect-container', callback)
			.on('click.mscontrols', ':not(.dgt-multiselect-list)', callback);

		_ensureEmptySelection.call(this);

		this._element.on('change', $.proxy(_ensureEmptySelection, this));
	}

	MultiSelect.prototype = {
		events: {
			'change.mscontrols .select-control': _selectVals,
			'click.mscontrols .dgt-multiselect-list-toggle': 'toggle',
			'click.mscontrols .dgt-multiselect-list': _prevent,
			'click.mscontrols .option': _optionSelect,
			'click.mscontrols a.select': 'selectAll',
			'click.mscontrols a.unselect': 'unselectAll',
		},
		toggle: function (event) {
			if (event) {
				event.preventDefault();
				event.stopPropagation();
				console.log(event.target);
				if ($('.dgt-multiselect-list').has(event.target).length) {
					return;
				}
			}


			var that = this,
			list = this._list, open = this.element.hasClass('open'),
			method = open ? 'fadeOut' : 'fadeIn';

			this.element.toggleClass('open');

			if (this.settings.fade) {
				this._list[method](this.settings.fade, function () {
					that._list.toggleClass('hidden');
				});
			} else {
				this._list.toggleClass('hidden');
			}
			if (!open) {
				this.element.trigger('selfclose.mscontrols', this.element);
			}
		},
		close: function () {
			if (this.element.hasClass('open')) {
				return this.toggle();
			}
			return false;
		},
		open: function () {
			if (!this.element.hasClass('open')) {
				return this.toggle();
			}
			return false;
		},
		selectAll: function (event) {
			event.stopPropagation();
			event.preventDefault();
			this.element.find('.select-control').each(function () {
				var chk = $(this),
				checked = chk.is(':checked');
				chk[0].checked = true;
				if (!checked) {
					chk.trigger('change.mscontrols');
				}
			});
		},
		unselectAll: function (event) {
			event.stopPropagation();
			event.preventDefault();
			this.element.find('.select-control').each(function () {
				var chk = $(this),
				checked = chk.is(':checked');
				chk[0].checked = false;
				if (checked) {
					chk.trigger('change.mscontrols');
				}
			});
		}

	};


	$.fn.multiselect = function (options) {
		options = $.extend({}, $.fn.multiselect.defaults, options || {});
		return this.each(function () {
			var element = $(this);
			element.data('multiselect', new MultiSelect(element, options));
		});
	};
	$.fn.multiselect.defaults = {
		fade: 250,
		buttonText: 'select option'
	};
}));
