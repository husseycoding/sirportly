var dataframe = Class.create({
    afterInit: function() {
        $("row_sirportly_general_key").insert({ before: '<tr><td class="label">Data Frame URL</td><td class="value"><strong>' + this.url + '</strong><p class="note"><span>Use this URL when creating the data frame in Sirportly.</span></p></td></tr>' });
    }
});

document.observe("dom:loaded", function() {
    if (typeof(thisdataframe) == "object" && $("sirportly_general_key")) {
        thisdataframe.afterInit();
    }
});