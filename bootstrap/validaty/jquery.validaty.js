/*!
 * jQuery Validaty - A Validation Plugin
 * ----------------------------------------------------------------------
 *
 * jQuery Validaty is a form validation plugin.
 *
 * Licensed under The MIT License
 *
 * @version        0.3.0
 * @since          2013-02-10
 * @author         Washington Botelho
 * @documentation  wbotelhos.com/validaty
 *
 * ----------------------------------------------------------------------
 *
 *  <form>
 *    <input type="text" data-validy="required" />
 *  </form>
 *
 *  $('form').validaty();
 *
 */

;(function($) {

  var methods = {
    init: function(settings) {
      return this.each(function() {
        methods.destroy.call(this);

        this.opt = $.extend({}, $.fn.validaty.defaults, settings);

        var that = $(this).addClass('validaty');

        methods._bind.call(this);

        that.data({ 'settings': this.opt, 'validaty': true });
      });
    }, _bind: function() {
      var self = this,
          that = $(this);

      self.inputs   = methods._fields.call(self);
      self.distinct = methods._distinct.call(self);

      that.on('submit.validaty', function(evt) {
        methods._process.call(self, self.distinct, evt);
      });

      for (var i = 0; i < self.inputs.length; i++) {
        var field   = self.inputs[i],
            actions = helper.getParams(field).actions;

        methods._setHash.call(self, field);

        field = $(field);

        for (index in actions) {
          var binds = actions[index].args.join('.validaty ') + '.validaty';

          field.on(binds, function(evt, forced) {
            if (!forced) {
              methods._process.call(self, [this], evt);
            }
          });
        }
      }
    }, _balloon: function(result) {
      var offset     = result.el.offset(),
          html       = '<ul></ul>',
          attributes = { id: result.el[0].hash, 'class': 'validaty-message' };

      if (this.opt.balloon) {
        html               += '<div></div>';
        attributes['class'] = 'validaty-balloon';
        attributes['css']   = { top: offset.top, left: offset.left + result.el.outerWidth() - 15 };
      }

      attributes['html'] = html;

      return $('<div />', attributes).insertAfter(result.el[0]);
    }, _clear: function(field) {
      $('#' + field.hash).remove();
      $(field).removeClass('invalid valid');
    }, _display: function(result) {
      var balloon = methods._balloon.call(this, result);

      methods._writeBalloon.call(this, balloon, result);
      methods._showBalloon.call(this, balloon);
    }, _distinct: function() {
      var names    = [],
          distinct = [],
          inputs   = this.opt.balloon ? this.inputs : $($(this.inputs).get().reverse());

      inputs.each(function() {
        if (helper.isCheckable(this)) {
          if ($.inArray(this.name, names) === -1) {
            names.push(this.name);
            distinct.push(this);
          }
        } else {
          distinct.push(this);
        }
      });

      return distinct;
    }, _fade: function() {
      var self     = this,
          balloons = $(self).children(self.opt.balloon ? '.validaty-balloon' : '.validaty-message');

      if (balloons.length > 1) {
        balloons.on('mouseenter.validaty', function() {
          var overed = this;
              other  = balloons.filter(function() {
                return this !== overed;
              });

          other.animate({ opacity: .2 }, { duration: self.opt.fadeSpeed, queue: false });
        }).on('mouseleave.validaty', function() {
          balloons.animate({ opacity: 1 }, { duration: self.opt.fadeSpeed, queue: false });
        });
      }
    }, _fields: function() {
      var that   = $(this),
          fields = that.is('form') ? $(this).find(':input[data-validaty]') : that;

      return fields.not(this.opt.ignore);
    }, _format: function(message, args) {
      message = message || 'Message type missing!';

      var holders = message.match(/{[^}]*}/g);

      if (holders) {
        for (var i = 0; i < holders.length; i++) {
          if (i == args.length) {
            break;
          }

          message = message.replace(holders[i], args[i]);
        }
      }

      return message;
    }, _highlight: function(result) {
      var inputs = result.el;

      if (helper.isCheckable(result.el[0])) {
        inputs = $(this).find('[name="' + result.el.attr('name') + '"]');
      }

      inputs.addClass(result.fail.length ? 'invalid' : 'valid')
    }, _message: function(field, validator, args) {
      var message = validator.message;

      if (typeof validator.message === 'object') {
        if (field.is('input')) {
          message = validator.message[field.attr('type')];
        } else if (field.is('select')) {
          message = validator.message.select;
        }
      }

      return methods._format.call(this, message, args);
    }, _process: function(fields, evt) {
      var self   = this,
          submit = evt && evt.type === 'submit';

      $.each(fields, function() {
        var result = methods._validate.call(self, this);

        methods._clear.call(self, this);

        if (result.fail.length > 0) {
          if (submit) {
            evt.preventDefault();
          }

          methods._display.call(self, result);
        }

        methods._highlight.call(self, result);
      });

      if (self.opt.focus && submit) {
        var forced = true;

        $(self).find('.invalid:visible:' + self.opt.focus).trigger('focus', forced);
      }

      if (self.opt.fade) {
        methods._fade.call(self);
      }
    }, _setHash: function(field) {
      field.hash = 'validaty-' + Math.random().toString().substring(2)
    }, _showBalloon: function(balloon) {
      var position = balloon.offset().top - balloon.height();

      if (this.opt.balloon) {
        balloon.css({ top: position })
      }

      balloon.animate({ opacity: 1 });
    }, _writeBalloon: function(balloon, result) {
      var ul = balloon.children('ul');

      $.each(result.fail, function() {
        $('<li />', { text: this.message }).appendTo(ul);
      });
    }, _validate: function(input) {
      var self        = this,
          errors      = [],
          validations = helper.getParams(input).validations;

      input = $(input);

      var result = { el: input, pass: [], fail: [] };

      for (var i = 0; i < validations.length; i++) {
        var name      = validations[i].name,
            validator = self.opt.validators[name];

        if (!validator) {
          $.error('Validator "' + name + '" not registered!');
        }

        var args    = validations[i].args,
            valid   = validator.validate.apply(input, [helper, self].concat(args)),
            message = methods._message.call(self, input, validator, args);

        result[valid ? 'pass' : 'fail'].push({ type: name, message: message });
      }

      return result;
    }, destroy: function() {
      return $(this).each(function() {
        $(this).off('.validaty').removeClass('validaty');
      });
    }, helper: function(name) {
      return helper;
    }, validate: function() {
      return this.each(function() {
        methods._process.call(this, this.distinct);
      });
    }, validator: function(name) {
      return $.fn.validaty.defaults.validators[name] || $.error('Validator "' + name + '" not registered!');
    }
  }, helper = {
    contains: function(value, word) {
      return value.indexOf(word) !== -1;
    }, getParams: function(input) {
      var data   = $(input).attr('data-validaty'),
          params = { validations: [], actions: [] };

      if (data) {
        var items = data.split(/\s+/);

        for (var i = 0; i < items.length; i++) {
          var args     = $(items[i].split(':')).get().reverse(),
              name     = args.pop(),
              isAction = name.startsWith('on');

          args = $(args).get().reverse();

          if (!isAction) {
            for (var j = 0; j < args.length; j++) {
              if (isNaN(args[j])) {
                args[j] = args[j].replace('%20', ' ');
              } else if (name !== 'equal') {
                args[j] = parseInt(args[j]);
              }
            }
          }

          params[isAction ? 'actions' : 'validations'].push({ name: name, args: args });
        }
      } else {
        return undefined;
      }

      return params;
    }, isCheckable: function(input) {
      return /checkbox|radio/i.test(input.type);
    }, isDateISO: function(value) {
      return /^(\d{4})\-(0[1-9]|1[0-2])\-([12]\d|0[1-9]|3[01])$/.test(value);
    }, isDigits : function(value) {
      return /^\d+$/.test(value);
    }, isEmail : function(value) {
      return /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/i.test(value);
    }, isNumber: function(value) {
      return /^-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test(value);
    }, isUrl: function(value) {
      return /^(https?|s?ftp|git):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(value);
    }, isUsername: function(value) {
      return /^[a-zA-Z0-9]+(_?[a-zA-Z0-9]+)*$/i.test(value);
    }
  };

  $.fn.validaty = function(method) {
    if (methods[method]) {
      return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
    } else if (typeof method === 'object' || !method) {
      return methods.init.apply(this, arguments);
    } else {
      $.error('Method ' + method + ' does not exist!');
    }
  };

  $.fn.validaty.defaults = {
    balloon    : true,
    fade       : true,
    fadeSpeed  : 200,
    focus      : 'first',
    ignore     : ':submit, :reset, :image, :disabled',
    validators : {}
  };

  $.validaty = {
    register: function() {
      $.fn.validaty.defaults.validators[arguments[0]] = { message: arguments[1], validate: arguments[2] };
      return this;
    }
  };

})(jQuery);
