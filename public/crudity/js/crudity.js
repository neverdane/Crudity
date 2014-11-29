(function ($) {
    $.fn.extend({
        crudity: function () {

            return this.each(function () {

                // We store the elem in order to keep it on this scope change
                var $form = $(this);

                function hideAllErrors() {
                    // We hide the potential errors shown in the form
                    $form.find('.cr-error').each(function () {
                        $(this).crHideError();
                    });
                }

                function disableSubmitButton() {
                    // We disable and set the submit button(s) at load state in order to prevent from a new submit
                    $form.find("[type='submit']").attr("disabled", true).addClass("cr-submit--loading");
                }

                function enableSubmitButton() {
                    $form.find("[type='submit']").removeAttr("disabled").removeClass("cr-submit--loading");
                }

                function getCurrentFieldValuesAsArray() {
                    // We get all the data from all the form fields into an array
                    var values = $form.serializeArray();
                    // We add the checkboxes not checked as they're not handled by serializeArray
                    $form.find("input[type='checkbox']:not(:checked)").each(function () {
                        values.push({name: $(this).attr("name"), value: 0});
                    });
                    return values;
                }

                function getCrudityPrimaryParams() {
                    return [
                        {name: "crudity_form_id", value: $form.attr("id")},
                        {name: "crudity_form_action", value: $form.data("crudity-action")},
                        {name: "crudity_form_row_id", value: $form.data("crudity-row-id")}
                    ];
                }

                function getCruditySecondaryParams() {
                    var params = [];
                    var addedParams = $form.data("crudity-added-params");
                    if (addedParams) {
                        $.each(addedParams, function (key, value) {
                            params.push({name: key, value: value});
                        });
                    }
                    return params;
                }

                function getDataToSend() {
                    var sData = getCurrentFieldValuesAsArray();
                    sData.push.apply(sData, getCrudityPrimaryParams());
                    sData.push.apply(sData, getCruditySecondaryParams());
                    return sData;
                }

                function showGlobalError(content) {
                    $("<div>" + content + "</div>").appendTo($form).hide().slideDown();
                }

                function handleErrors(errors, params) {
                    // We handle the potential errors display
                    $.each(errors.fields, function (idx, error) {
                        var $guilt = $form.find("[name='" + error.guilt + "']");
                        if (params.highlightGuilt) {
                            $guilt.addClass("cr-guilt--highlight");
                        }
                        if (params.errors.separate === true) {
                            $guilt.crDisplayError(error.message, params.errors.class);
                        } else {
                            showGlobalError(error.message);
                        }
                    });
                }


                // On form submission
                $form.on("submit", function (e) {
                    // We prevent the form to be submitted
                    e.preventDefault();
                    hideAllErrors();
                    disableSubmitButton();
                    // We get the potential configuration we set for this form
                    var params = $form.data("crudity-params");
                    var sData = getDataToSend();
                    var url = $form.attr("action") || window.location.href;
                    var method = $form.attr("method") || "post";
                    $.ajax(url, {
                        data: sData,
                        dataType: "json",
                        type: method,
                        success: function (response) {
                            enableSubmitButton();
                            if(response.status === 1) {
                                $form.trigger("cruditySuccess");
                            } else {
                                handleErrors(response.errors, params);
                            }
                        },
                        error: function () {
                            enableSubmitButton();
                            showGlobalError(params.messages.fail);
                        }
                    });
                });
                $(this).on("change", ".cr-guilt--highlight", function () {
                    $(this).removeClass("cr-guilt--highlight");
                    var $error = $(this).next();
                    if ($error.attr("cr-guilt") === $(this).attr("name")) {
                        $error.crHideError();
                    }
                });
                $(this).on("crudityAddParam", function (e, addedParams) {
                    var params = $(this).data("crudity-added-params");
                    if (!params) {
                        params = {};
                    }
                    $.each(addedParams, function (key, value) {
                        params[key] = value;
                    });
                    $(this).data("crudity-added-params", params);
                });
            });
        },
        crSetDelete: function (id, text) {
            return this.each(function () {
                $(this).find(".cr__fieldset--delete").show();
                $(this).find(".cr__fieldset--edit").hide();
                $(this).find(".cr__placeholder--name").html(text);
                $(this).data("crudity-row-id", id);
                $(this).data("crudity-action", "delete");
            });
        },
        crPopulate: function (id, text) {
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
        },
        crSetRowId: function (id) {
            return this.each(function () {
                if (id) {
                    $(this).data("crudity-row-id", id);
                    $(this).data("crudity-action", "update");
                } else {
                    $(this).data("crudity-action", "create");
                }
            });
        },
        crDisplayError: function (message, additionalClass) {
            return this.each(function () {
                var $error = $(this).next();
                if ($error.attr("cr-guilt") !== $(this).attr("name")) {
                    $error = $("<div class='cr-error " + additionalClass + "' cr-guilt='" + $(this).attr("name") + "'></div>").insertAfter($(this)).hide();
                }
                $error.html(message).stop(true, false).slideDown();
            });
        },
        crHideError: function () {
            return this.each(function () {
                $(this).slideUp(function () {
                    $(this).remove();
                });
            });
        }
    });

    $().ready(function () {
        $(".cr-form").crudity();
    });
})(jQuery);