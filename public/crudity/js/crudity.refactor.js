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
        defaults = {};

    function Crudity(el, options) {

        this.name = pluginName;

        this.el = el;
        this.$el = $(el);

        this.options = $.extend({}, defaults, options);

        this.init();
        return this;
    }

    Crudity.prototype.init = function () {
        var self = this;
        // On form submission
        this.onSubmit = function(e) {self.handleSubmit(e);};
        this.$el.on("submit", this.onSubmit);


        this.$el.on("change", ".cr-guilt--highlight", function () {
            $(this).removeClass("cr-guilt--highlight");
            var $error = $(this).next();
            if ($error.attr("cr-guilt") === $(this).attr("name")) {
                $error.crHideError();
            }
        }).on("crudityAddParam", function (e, addedParams) {
            var params = $(this).data("crudity-added-params");
            if (!params) {
                params = {};
            }
            $.each(addedParams, function (key, value) {
                params[key] = value;
            });
            $(this).data("crudity-added-params", params);
        });
    };

    Crudity.prototype.handleSubmit = function (e) {
        var self = this;
        // We prevent the form to be submitted
        e.preventDefault();
        this.hideAllErrors();
        this.disableSubmitButton();
        // We get the potential configuration we set for this form
        var params = this.$el.data("config");
        var sData = this.getDataToSend();
        var url = this.$el.attr("action") || window.location.href;
        var method = this.$el.attr("method") || "post";
        $.ajax(url, {
            data:     sData,
            dataType: "json",
            type:     method,
            success:  function (response) {
                self.enableSubmitButton();
                if (response.status === 1) {
                    self.$el.trigger("cruditySuccess");
                } else {
                    self.handleErrors(response.errors, params);
                }
            },
            error:    function () {
                self.enableSubmitButton();
                self.showGlobalError(params.messages.fail);
            }
        });
    };


    Crudity.prototype.hideAllErrors = function () {
        // We hide the potential errors shown in the form
        $(this).find('.cr-error').each(function () {
            $(this).crHideError();
        });
    };

    Crudity.prototype.disableSubmitButton = function () {
        // We disable and set the submit button(s) at load state in order to prevent from a new submit
        $(this).find("[type='submit']").attr("disabled", true).addClass("cr-submit--loading");
    };

    Crudity.prototype.enableSubmitButton = function () {
        $(this).find("[type='submit']").removeAttr("disabled").removeClass("cr-submit--loading");
    };

    Crudity.prototype.getCurrentFieldValuesAsArray = function () {
        // We get all the data from all the form fields into an array
        var values = $(this).serializeArray();
        // We add the checkboxes not checked as they're not handled by serializeArray
        $(this).find("input[type='checkbox']:not(:checked)").each(function () {
            values.push({name: $(this).attr("name"), value: 0});
        });
        return values;
    };

    Crudity.prototype.getCrudityPrimaryParams = function () {
        return [
            {name: "crudity_form_id", value: $(this).attr("id")},
            {name: "crudity_form_action", value: $(this).data("crudity-action")},
            {name: "crudity_form_row_id", value: $(this).data("crudity-row-id")}
        ];
    };

    Crudity.prototype.getCruditySecondaryParams = function () {
        var params = [];
        var addedParams = $(this).data("crudity-added-params");
        if (addedParams) {
            $.each(addedParams, function (key, value) {
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

    Crudity.prototype.handleErrors = function (errors, params) {
        var self = this;
        // We handle the potential errors display
        $.each(errors.fields, function (fieldName, rows) {
            $.each(rows, function (index, error) {
                var $guilt = $(this).find("[name='" + fieldName + "']").eq(index);
                if ($guilt.length === 0) {
                    $guilt = $(this).find("[name^='" + fieldName + "[']").eq(index);
                }
                if (params.errorHighlighted) {
                    $guilt.addClass("cr-guilt--highlight");
                }
                if (params.errorGrouped === false) {
                    $guilt.crDisplayError(error.message, "");
                } else {
                    self.showGlobalError(error.message);
                }
            });
        });
    };

    Crudity.prototype.setDelete = function (id, text) {
        return this.each(function () {
            $(this).find(".cr__fieldset--delete").show();
            $(this).find(".cr__fieldset--edit").hide();
            $(this).find(".cr__placeholder--name").html(text);
            $(this).data("crudity-row-id", id);
            $(this).data("crudity-action", "delete");
        });
    };

    Crudity.prototype.populate = function (id, text) {
        return this.each(function () {
            var $form = $(this);
            $form.find(".cr__show--edit").hide();
            $form.find(".cr__show--create").hide();
            if (!id) {
                $form.find(".cr__show--create").show();
            } else {
                $form.find(".cr__show--edit").show();
            }
            $form.find(".cr__fieldset--delete").hide();
            $form.find(".cr__fieldset--edit").show();
            $form.find(".cr__placeholder--name").html(text);
            $(this).crSetRowId(id);
            var sData = {
                crudity_form_row_id: id,
                crudity_form_action: "populate"
            };
            sData["crudity_form_id"] = $(this).attr("id");
            $.post(window.location.href, sData, function (response) {
                $.each(response.fields, function (key, value) {
                    var $field = $form.find("[name='" + key + "']");
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
        });
    };
    Crudity.prototype.setCreate = function () {
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
    Crudity.prototype.displayError = function (message, additionalClass) {
        var $error = $(this).next();
        if ($error.attr("cr-guilt") !== $(this).attr("name")) {
            $error = $("<div class='cr-error " + additionalClass + "' cr-guilt='" + $(this).attr("name") + "'></div>").insertAfter($(this)).hide();
        }
        $error.html(message).stop(true, false).slideDown();
    };
    Crudity.prototype.hideError = function () {
        this.$el.slideUp(function () {
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
    $.crudity.setAction = function ($obj) {
        var crudity = $obj.data('plugin_' + pluginName);

        if (typeof crudity === 'undefined')
            return;
    };

})(jQuery, window, document);