$.fn.extend({
    selectly: function() {

        return this.each(function() {
        var self = this;

        var speed = 150;

        function addOption(params) {
            var html = '<div class="selectly__option" data-value="' + params.id + '">'
                    + '<span class="selectly-option__text">' + params.name + '</span>';
            if(params.deletable === true) {
                html += '<a href="javascript:;" class="selectly__remove"></a>';
            } else {
                html += '<a href="javascript:;" class="selectly__remove selectly__inactive"></a>';
            }
            html += '<a href="javascript:;" class="selectly__edit"></a>'
                    + '</div>';
            self.$select.find(".selectly__list").append(html);
            self.$selectField.append("<option value=" + params.id + ">" + params.name + "</option>")
        }

        function refreshSelect(extraParams) {
            var dependencies = self.$selectField.data("selectly-dependencies");
            if(!dependencies) {
                dependencies = {};
            }
            if(!extraParams) {
                extraParams = {};
            }
            $.extend(dependencies, extraParams);
            
            self.$select.find(".selectly__list").html("");
            self.$selectField.html("");
            $.post(self.url.getAll, dependencies, function(response) {
                self.$select.find(".selectly__list").html("");
                self.$selectField.html("");
                $.each(response.options, function(key, params) {
                    addOption(params);
                });
               selectOption();
            }, "json");
        }
        
        function showList() {
         /*   self.$list.css({
               position: self.position,
               top: "100%",
               opacity: 0,
               display: "block"
            }).animate({opacity: 1}, speed);*/
           /* self.$list.css({
               position: self.position,
               top: "100%",
               opacity: 0,
               display: "block"
            });*/self.$list.css({
               position: self.position,
               top: "100%",
               opacity: 1
            });
            self.$list.slideDown("fast");
        }
        
        function hideList() {
            /*self.$list.animate({opacity: 0}, speed, function() {
                $(this).hide();
            });*/
            self.$list.slideUp("fast");
        }
        
        function toggleList() {
            if(self.$list.is(":visible")) {
                hideList();
            } else {
                showList();
            }
        }
        
        function selectOption(id) {
            if(!id) {
                if(self.$selectField.val()) {
                    id = self.$selectField.val();
                } else {
                    id = self.$selectField.find("option").eq(0).val();
                }
            }
            if(id) {
                var $option = self.$list.find("[data-value='" + id + "']");
                self.$frame.html($option.find(".selectly-option__text").html());
                self.$selectField.val(id);
                if(self.crudityMode === true) {
                    self.$selectField.data("value", id);
                }
            } else {
                self.$frame.html("");
                self.$selectField.val(null);
                if(self.crudityMode === true) {
                    self.$selectField.data("value", null);
                }
            }
            self.$selectField.trigger("selectlyChange", [id]);
            hideList();
        }

            var html = '<div class="selectly">'
                    + '<a href="javascript:;" class="selectly__add"></a>'
                    + '<div class="selectly__wrapper">'
                    + '<div class="selectly__frame"></div>'
                    + '<div class="selectly__list"> '
                    + '</div>'
                    + '</div>'
                    + '</div>';

            self.url = {};
            self.crudityMode = false;
            
            if($(this).hasClass("selectly--crudity")) {
                self.crudityMode = true;
            }
            
            self.url.getAll = $(this).data("selectly-get-all");
            self.url.get = $(this).data("selectly-get");
            self.url.create = $(this).data("selectly-create");
            self.url.update = $(this).data("selectly-update");
            self.url.delete = $(this).data("selectly-delete");
            self.position = $(this).data("selectly-position");            
            if(!self.position) {
                self.position = "absolute";
            }            
            self.formClass = $(this).data("selectly-form");
            self.$form = $(self.formClass);
            
            var fieldsetEditClass = ".selectly-form__fieldset--edit";
            var fieldsetDeleteClass = ".selectly-form__fieldset--delete";
            
            if(self.crudityMode === true) {
                fieldsetEditClass = ".cr__fieldset--edit";
                fieldsetDeleteClass = ".cr__fieldset--delete";
            }
            
            self.$fieldsetEdit      = self.$form.find(fieldsetEditClass);
            self.$fieldsetDelete    = self.$form.find(fieldsetDeleteClass);

            self.$select = $(this).before(html).prev();
            self.$select.find(".selectly__wrapper").css({position: "relative"});
            self.$list = self.$select.find(".selectly__list");
            self.$frame = self.$select.find(".selectly__frame");
            $(this).hide();
            self.$selectField = $(this);
            refreshSelect();
            
            self.$list.hide();

            self.$select.find(".selectly__frame").on("click", function(event) {
                toggleList();
                event.stopPropagation();
            });
            
            self.$select.find(".selectly__add").on("click", function() {
                hideList();
                if(self.crudityMode === true) {
                    self.$form.crPopulate();
                }
                self.$select.riplace(self.$form);
                self.$fieldsetEdit.show();
                self.$fieldsetDelete.hide();
                self.$form.attr("action", self.url.create);
                self.$form.data("selectly-edit-id", null);
            });
            self.$list.on("click", ".selectly__remove", function(event) {
                if(!$(this).hasClass('selectly__inactive')) {
                    hideList();
                    self.$select.riplace(self.$form);
                    self.$fieldsetDelete.show();
                    self.$fieldsetEdit.hide();
                    var id = $(this).closest(".selectly__option").data("value");
                    var text = $(this).closest(".selectly__option").text();
                    if(self.crudityMode === true) {
                        self.$form.crSetDelete(id, text);
                    }
                    self.$form.data("selectly-edit-id", id);
                    self.$form.attr("action", self.url.delete);
                }
                event.stopPropagation();
            });
            self.$list.on("click", ".selectly__edit", function(event) {
                event.stopPropagation();
                hideList();
                var id = $(this).closest(".selectly__option").data("value");
                var text = $(this).closest(".selectly__option").text();
                if(self.crudityMode !== true) {
                    $.post(self.url.get, {id: id}, function(response) {
                        $.each(response, function(key, value) {
                            $input = self.$form.find('[name="' + key + '"]');
                            if ($input.size() > 0) {
                                $input.val(value);
                            }
                        });
                    }, "json");
                } else {
                    self.$form.crPopulate(id, text);
                }
                self.$select.riplace(self.$form);
                self.$fieldsetEdit.show();
                self.$fieldsetDelete.hide();
                self.$form.attr("action", self.url.update);
                self.$form.data("selectly-edit-id", id);
            });
            
            self.$form.on("click", ".selectly-form__cancel", function(event) {
                self.$form.riplace(self.$select);
                event.preventDefault();
            });
            
            $("html").on("click", function(event) {
                hideList();
            });

            if(self.crudityMode !== true) {
                self.$form.on("submit", function() {
                    var $form = $(this);
                    var sData = $(this).serializeArray();
                    if(self.$form.data("selectly-edit-id")) {
                        sData.push({ name: "id", value: self.$form.data("selectly-edit-id")});
                    }
                    $.ajax($(this).attr("action"), {
                        data: sData,
                        dataType: "json",
                        type: $form.attr("method"),
                        success: function(response) {
                           self.$form.riplace(self.$select);
                           refreshSelect();
                        }
                    });
                    return false;
                });
            } else {
                self.$form.on("cruditySuccess", function(e) {
                    e.stopPropagation();
                    self.$form.riplace(self.$select);
                    refreshSelect();
                });
            }
            
            self.$list.on("click", ".selectly__option", function() {
                var id = $(this).data("value");
                selectOption(id);
            });
            
            self.$selectField.on("selectlyRefresh", function(e, params) {
                refreshSelect(params);
            });
            
            self.$selectField.on("selectlyAddDependency", function(e, addedDependencies) {
                var dependencies = $(this).data("selectly-dependencies");
                if(!dependencies) {
                    dependencies = {};
                }
                $.each(addedDependencies, function(key, value) {
                    dependencies[key] = value;
                });
                $(this).data("selectly-dependencies", dependencies);
                refreshSelect();
            });


        });
    }
});


$().ready(function() {
    $("select.selectly").selectly();
});