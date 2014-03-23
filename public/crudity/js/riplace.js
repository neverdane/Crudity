$.fn.extend({
    riplace: function($comer, effectName, effectParams) {

        if (!effectName) {
            effectName = "slide";
        }

        var defaultSpeed = 200;
        var self = this;
        self.$runaway = $(this);
        self.$comer = $comer;

        var defaultContainerEffect = {
            effect: "adjust",
            params: {
                overflow: "visible"
            }
        };

        var noEffect = {
            effect: "stay",
            params: null
        };

        var end = function() {
            self.$runaway.hide();
            self.$comer.css({
                position: "relative"
            });
            self.$container.replaceWith(self.$container.contents());
        };

        var effectsCollections = {
            slide: {
                container: defaultContainerEffect,
                runaway: noEffect,
                comer: {
                    effect: "slide",
                    params: {
                        side: "top"
                    }
                }
            }
        };

        var containerEffects = {
            adjust: {
                init: function(rip, params) {
                    rip.$container.css({
                        position: "relative",
                        width: rip.$runaway.outerWidth(),
                        height: rip.$runaway.outerHeight(),
                        overflow: params.overflow
                    });
                },
                go: function(rip, params) {
                    var dimensions = rip.$comer.getHiddenDimensions();
                    var comerWidth = dimensions.outerWidth;
                    var comerHeight = dimensions.outerHeight;
                    rip.$container.animate({
                        width: comerWidth,
                        height: comerHeight
                    }, params.speed);
                }
            }
        }

        var elemEffects = {
            stay: {
                come: {
                    init: function(rip) {
                        rip.$comer.css({
                            position: "absolute",
                            top: 0,
                            left: 0
                        });
                    },
                    go: function(rip) {

                    }
                },
                runaway: {
                    init: function(rip) {
                        rip.$runaway.css({
                            position: "absolute",
                            top: 0,
                            left: 0
                        });

                    },
                    go: function(rip) {

                    }
                }
            },
            slide: {
                come: {
                    init: function(rip, params) {
                        var css = {
                            position: "absolute",
                            left: 0,
                            "z-index": 0
                        };
                        css[params.side] = "-" + rip.$comer.outerHeight() + "px";
                        rip.$comer.css(css);
                    },
                    go: function(rip, params) {
                        var css = {};
                        css[params.side] = 0;
                        rip.$comer.animate(css, params.speed, end);
                    }
                },
                runaway: {
                    init: function(rip) {
                    },
                    go: function(rip) {

                    }
                }
            },
            flip: {
                come: {
                    init: function(rip, params) {
                        var css = {
                            position: "absolute",
                            left: 0,
                            "z-index": 0,
                            transform: "rotateX(10deg)"
                        };
                        css[params.side] = "-" + rip.$comer.outerHeight() + "px";
                        rip.$comer.css(css);
                    },
                    go: function(rip, params) {
                        var css = {};
                        css[params.side] = 0;
                        rip.$comer.animate(css, params.speed, end);
                    }
                },
                runaway: {
                    init: function(rip) {
                    },
                    go: function(rip) {

                    }
                }
            }
        };

        function manageElement(elementName, functionName) {
            var containerName = (elementName === "container") ? containerEffects : elemEffects;
            var effectFunctionBase = containerName[effectsCollections[effectName][elementName].effect];
            var params = effectsCollections[effectName][elementName].params;
            if (elementName !== "container") {
                effectFunctionBase = effectFunctionBase[(elementName === "runaway") ? "runaway" : "come"];
                if (!params) {
                    params = {};
                }
                if (!params.speed) {
                    params.speed = defaultSpeed;
                }
            }
            effectFunctionBase[functionName](self, params);
        }

        return this.each(function() {
            var $container = $("<div class='tmpContainer'></div>");
            $(this).wrap($container);
            self.$container = $(this).parent();
            self.$container.append($comer);
            var collection = effectsCollections[effectName];

            $comer.show();

            manageElement("container", "init");
            manageElement("runaway", "init");
            manageElement("comer", "init");
            manageElement("container", "go");
            manageElement("runaway", "go");
            manageElement("comer", "go");

            /*containerEffects[collection.container.effect].init(self, collection.container.params);
             elemEffects[collection.runaway.effect].runaway.init(self, collection.runaway.params);
             elemEffects[collection.comer.effect].come.init(self, collection.comer.params);
             
             containerEffects[collection.container.effect].go(self, collection.container.params);
             elemEffects[collection.runaway.effect].runaway.go(self, collection.runaway.params);
             elemEffects[collection.comer.effect].come.go(self, collection.comer.params);*/

            /*if(shortcut !== "prev") {
             $tmpContainer.append($replacer);
             } else {
             $tmpContainer.prepend($replacer);
             }
             console.log($replacer);
             $replacer.css({
             position: "absolute",
             top: 0,
             left: 0
             });
             $(this).fadeOut();
             $replacer.fadeIn(function() {
             $(this).css({
             position: "relative"
             });
             $tmpContainer.replaceWith($tmpContainer.contents());
             });*/
        });
    }
});