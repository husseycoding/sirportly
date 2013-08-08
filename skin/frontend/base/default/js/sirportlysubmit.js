var sirportlysubmit = Class.create({
    afterInit: function() {
        this.addSubject();
        this.scrollToTop();
        this.form = $("contactForm");
        this.form.observe("submit", function(e) {
            Event.stop(e);
            if (contactForm.validator && contactForm.validator.validate()) {
                this.disableButton();
                var parameters = this.form.serialize(true);
                new Ajax.Request("/sirportly/ticket/create", {
                    parameters: parameters,
                    onSuccess: function(response) {
                        var contentarray = response.responseText.evalJSON();
                        if (typeof(contentarray) == "object") {
                            var status = contentarray.status;
                            if (status == "success") {
                                window.location.reload();
                            } else {
                                this.enableButton();
                                this.failedSubmit();
                            }
                        } else {
                            this.enableButton();
                            this.failedSubmit();
                        }
                    }.bind(this)
                });
            }
        }.bindAsEventListener(this));
    },
    disableButton: function() {
        this.form.down("button").disable();
        this.form.down("button").setStyle({
            opacity: 0.5,
            cursor: "default"
        });
    },
    enableButton: function() {
        this.form.down("button").enable();
        this.form.down("button").setStyle({
            opacity: 1,
            cursor: "pointer"
        });
    },
    scrollToTop: function() {
        if ($$("ul.messages")[0]) {
            $$("body")[0].scrollTo();
        }
    },
    failedSubmit: function() {
        this.form.stopObserving("submit");
        this.form.submit();
    },
    addSubject: function() {
        var el = new Element("li", {"class":"wide"}).update(
            new Element("label", {"for":"subject", "class":"required"}).update("<em>*</em>Subject")
        );
        el.insert({
            bottom:new Element("div", {"class":"input-box"}).update(
                new Element("input", {"class":"input-text required-entry", "type":"text", "value":"", "id":"subject", "name":"subject", "title":"Subject"})
            )
        });
            
        $("comment").up("li").insert({
            before:el
        });
    }
});

document.observe("dom:loaded", function() {
    if (typeof(thissirportlysubmit) == "object") {
        thissirportlysubmit.afterInit();
    }
});