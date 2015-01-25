/*
 *         _________             __________________
 *         __  ____/__________  _______  /__(_)_  /_____  __
 *         _  /    __  ___/  / / /  __  /__  /_  __/_  / / /
 *         / /___  _  /   / /_/ // /_/ / _  / / /_ _  /_/ /
 *         \____/  /_/    \__,_/ \__,_/  /_/  \__/ _\__, /
 *                                                 /____/
 * *
 *        Copyright (c) 2015 Alban Pommeret
 *        Licensed under the MIT license.
 *
 *        Title generated using "Speed" @
 *        http://patorjk.com/software/taag/#p=display&f=Speed&t=Crudity
 */

;
(function ($, window, document, undefined) {

    "use strict";

    var pluginName = 'crudity',
        defaults = {
            action: 'create',
            errorHighlighted: true,
            errorGrouped: false
        };

    function Crudity(el, options) {

        this.name = pluginName;

        this.el = el;
        this.$el = $(el);

        var nativeOptions = this.$el.data('config');
        $.extend(defaults, nativeOptions);
        this.$el.removeAttr('data-config');

        this.options = $.extend({}, defaults, options);

        this.id = this.$el.attr("id");
        this.action = this.options.action;

        this.init();
        return this;
    }

    Crudity.prototype.init = function () {
        var self = this;

        // On form submission
        this.onSubmit = function (e) {
            self.handleSubmit(e);
        };
        this.$el.on("submit", this.onSubmit);

        this.onGuiltyChange = function () {
            self.handleGuiltyChange(this)
        };
        this.$el.on("change", ".cr-guilt--highlight", this.onGuiltyChange);
    };

    Crudity.prototype.addParams = function (addedParams) {
        $.extend(this.params, addedParams);
    };

    Crudity.prototype.handleGuiltyChange = function (guiltyEl) {
        $(guiltyEl).removeClass("cr-guilt--highlight");
        var $error = $(guiltyEl).next();
        if ($error.attr("cr-guilt") === $(guiltyEl).attr("name")) {
            this.hideError($error);
        }
    };

    Crudity.prototype.handleSubmit = function (e) {
        var self = this;
        // We prevent the form to be submitted
        e.preventDefault();
        this.hideAllErrors();
        this.disableSubmitButton();
        // We get the potential configuration we set for this form
        var sData = this.getDataToSend();
        var url = this.$el.attr("action") || window.location.href;
        var method = this.$el.attr("method") || "post";
        $.ajax(url, {
            data: sData,
            dataType: "json",
            type: method,
            success: function (response) {
                self.enableSubmitButton();
                if (response.status === 1) {
                    self.$el.trigger("cruditySuccess");
                } else {
                    self.handleErrors(response.errors);
                }
            },
            error: function () {
                self.enableSubmitButton();
                self.showGlobalError(this.options.messages.fail);
            }
        });
    };


    Crudity.prototype.hideAllErrors = function () {
        var self = this;
        // We hide the potential errors shown in the form
        this.$el.find('.cr-error').each(function () {
            self.hideError($(this));
        });
    };

    Crudity.prototype.disableSubmitButton = function () {
        // We disable and set the submit button(s) at load state in order to prevent from a new submit
        this.$el.find("[type='submit']").attr("disabled", true).addClass("cr-submit--loading");
    };

    Crudity.prototype.enableSubmitButton = function () {
        this.$el.find("[type='submit']").removeAttr("disabled").removeClass("cr-submit--loading");
    };

    Crudity.prototype.getCurrentFieldValuesAsArray = function () {
        // We get all the data from all the form fields into an array
        var values = this.$el.serializeArray();
        // We add the checkboxes not checked as they're not handled by serializeArray
        this.$el.find("input[type='checkbox']:not(:checked)").each(function () {
            values.push({name: $(this).attr("name"), value: 0});
        });
        return values;
    };

    Crudity.prototype.getCrudityPrimaryParams = function () {
        return [
            {name: "crudity_form_id", value: this.id},
            {name: "crudity_form_action", value: this.action},
            {name: "crudity_form_row_id", value: this.rowId}
        ];
    };

    Crudity.prototype.getCruditySecondaryParams = function () {
        var params = [];
        if (this.params) {
            $.each(this.params, function (key, value) {
                params.push({name: key, value: value});
            });
        }
        return params;
    };

    Crudity.prototype.getDataToSend = function () {
        var sData = this.getCurrentFieldValuesAsArray();
        sData.push.apply(sData, this.getCrudityPrimaryParams());
        sData.push.apply(sData, this.getCruditySecondaryParams());
        return sData;
    };

    Crudity.prototype.showGlobalError = function (content) {
        $("<div>" + content + "</div>").appendTo(this.$el).hide().slideDown();
    };

    Crudity.prototype.handleErrors = function (errors) {
        var self = this;
        // We handle the potential errors display
        $.each(errors.fields, function (fieldName, rows) {
            $.each(rows, function (index, error) {
                var $guilt = self.$el.find("[name='" + fieldName + "']").eq(index);
                if ($guilt.length === 0) {
                    $guilt = self.$el.find("[name^='" + fieldName + "[']").eq(index);
                }
                if (self.options.errorHighlighted) {
                    $guilt.addClass("cr-guilt--highlight");
                }
                if (self.options.errorGrouped === false) {
                    self.displayError($guilt, error.message, "");
                } else {
                    self.showGlobalError(error.message);
                }
            });
        });
    };

    Crudity.prototype.setActionToDelete = function (id, text) {
        this.$el.find(".cr__fieldset--delete").show();
        this.$el.find(".cr__fieldset--edit").hide();
        this.$el.find(".cr__placeholder--name").html(text);
        this.rowId = id;
        this.action = "delete";
    };

    Crudity.prototype.populate = function (id, text) {
        var self = this;
        this.$el.find(".cr__show--edit").hide();
        this.$el.find(".cr__show--create").hide();
        if (!id) {
            this.$el.find(".cr__show--create").show();
        } else {
            this.$el.find(".cr__show--edit").show();
        }
        this.$el.find(".cr__fieldset--delete").hide();
        this.$el.find(".cr__fieldset--edit").show();
        this.$el.find(".cr__placeholder--name").html(text);
        this.setRowId(id);
        var sData = {
            crudity_form_row_id: id,
            crudity_form_action: "populate"
        };
        sData["crudity_form_id"] = this.id;
        $.post(window.location.href, sData, function (response) {
            $.each(response.fields, function (key, value) {
                var $field = self.$el.find("[name='" + key + "']");
                var type = $field.attr("type");
                if ($field[0].tagName === "SELECT") {
                    type = "select";
                }
                if (type === "checkbox") {
                    if (value === "1") {
                        $field.attr("checked", true);
                    } else {
                        $field.removeAttr("checked");
                    }
                } else if (type === "select") {
                    $field.data("value", value);
                    $field.val(value);
                    if ($field.hasClass("selectly--crudity") === true) {
                        $field.trigger("selectlyRefresh");
                    }
                } else {
                    $field.val(value);
                }
            });
        }, "json");
    };

    Crudity.prototype.setActionToCreate = function () {
        this.action = 'create';
    };

    Crudity.prototype.setRowId = function (id) {
        if (id) {
            this.rowId = id;
            this.action = 'update';
        } else {
            this.action = 'create';
        }
    };

    Crudity.prototype.displayError = function ($field, message, additionalClass) {
        var $error = $field.next();
        if ($error.attr("cr-guilt") !== $field.attr("name")) {
            $error = $("<div class='cr-error " + additionalClass + "' cr-guilt='" + $field.attr("name") + "'></div>").insertAfter($field).hide();
        }
        $error.html(message).stop(true, false).slideDown();
    };

    Crudity.prototype.hideError = function ($error) {
        $error.slideUp(function () {
            $(this).remove();
        });
    };

    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                var crudityObj = new Crudity(this, options);
                $.data(this, 'plugin_' + pluginName, crudityObj);
            }
        });
    };

    $.crudity = {};
    // TODO Create Crudity API
    $.crudity.setAction = function ($obj, action) {
        var crudity = $obj.data('plugin_' + pluginName);

        if (typeof crudity === 'undefined')
            return;

        switch(action) {
            case 'create':
                crudity.setActionToCreate();
                break;
            case 'update':
                crudity.setActionToUpdate();
                break;
            case 'delete':
                crudity.setActionToDelete();
                break;
            default:
                break;
        }
    };

})(jQuery, window, document);