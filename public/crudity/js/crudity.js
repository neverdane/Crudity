(function ($) {
    $.fn.extend({
        crudity: function () {
            return this.each(function () {
                $(this).on("submit", function (e) {
                    e.preventDefault();
                    var $form = $(this);
                    $form.find('.cr-error').each(function () {
                        $(this).crHideError();
                    });
                    var params = $form.data("crudity-params");
                    $form.find("[type='submit']").attr("disabled", true).addClass("cr-submit--loading");
                    var sData = $form.serializeArray();
                    sData.push({name: "crudity_form_id", value: $(this).attr("id")});
                    sData.push({name: "crudity_form_action", value: $(this).data("crudity-action")});
                    sData.push({name: "crudity_form_row_id", value: $(this).data("crudity-row-id")});

                    $form.find("input[type='checkbox']:not(:checked)").each(function () {
                        sData.push({name: $(this).attr("name"), value: 0});
                    });

                    var addedParams = $form.data("crudity-added-params");
                    if (addedParams) {
                        $.each(addedParams, function (key, value) {
                            sData.push({name: key, value: value});
                        });
                    }
                    $.ajax($(this).attr("action"), {
                        data: sData,
                        dataType: "json",
                        type: $form.attr("method") || "post",
                        success: function (response) {
                            $form.find("[type='submit']").removeAttr("disabled").removeClass("cr-submit--loading");
                            $.each(response.errors, function (idx, error) {
                                var $guilt = $form.find("[name='" + error.guilt + "']");
                                if (params.highlightGuilt) {
                                    $guilt.addClass("cr-guilt--highlight");
                                }
                                if (params.errors.separate === true) {
                                    $guilt.crDisplayError(error.message, params.errors.class);
                                } else {
                                    $("<div>" + error.message + "</div>").appendTo($form).hide().slideDown();
                                }
                            });
                            if (response.errors.length === 0) {
                                $form.trigger("cruditySuccess");
                            } else {
                            }
                        },
                        error: function () {
                            $form.find("[type='submit']").removeAttr("disabled").removeClass("cr-submit--loading");
                            $("<div>" + params.messages.fail + "</div>").appendTo($form).hide().slideDown();
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