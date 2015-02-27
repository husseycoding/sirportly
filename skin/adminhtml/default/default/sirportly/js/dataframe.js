var dataframe = Class.create({
    afterInit: function() {
        $("row_sirportly_general_key").insert({ before: '<tr><td class="label">Data Frame URL</td><td class="value"><strong>' + this.url + '</strong><p class="note"><span>Use this URL when creating the data frame in Sirportly.</span></p></td></tr>' });
        this.initCustomDate();
    },
    initCustomDate: function() {
        this.setCustomDateVisibility();
        $("sirportly_general_dateformat").observe("change", function(e) {
            this.setCustomDateVisibility();
        }.bind(this));
    },
    setCustomDateVisibility: function() {
        if ($("sirportly_general_dateformat").value == 1) {
            $("row_sirportly_general_customdate").show();
        } else {
            $("row_sirportly_general_customdate").hide();
        }
    }
});

document.observe("dom:loaded", function() {
    if (typeof(thisdataframe) == "object" && $("sirportly_general_key")) {
        thisdataframe.afterInit();
    }
});