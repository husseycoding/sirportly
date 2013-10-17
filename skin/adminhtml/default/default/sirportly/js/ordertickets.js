var ordertickets = Class.create({
    showTarget: function(reference) {
        $("target-" + reference).show();
    },
    hideTarget: function(reference) {
        $("target-" + reference).hide();
    },
    hideTargets: function() {
        $$("tr.sirportly-ticket-target").each(function(e) {
            e.hide();
        }.bind(this));
    },
    shouldUpdate: function(reference) {
        if ($("target-" + reference).visible()) {
            return false;
        } else {
            return true;
        }
    },
    getUpdates: function(reference) {
        if (this.shouldUpdate(reference)) {
            var parameters = {reference:reference};
            new Ajax.Request(this.updateurl, {
                parameters: parameters,
                onSuccess: function(response) {
                    var contentarray = response.responseText.evalJSON();
                    if (typeof(contentarray) == "object") {
                        if (contentarray.result == "success") {
                            var updates = contentarray.updates;
                            this.updateTicket(reference, updates);
                        } else {
                            alert("Failed to get updates!")
                        }
                    }
                }.bind(this)
            });
        } else {
            this.hideTarget(reference);
        }
    },
    updateTicket: function(reference, updates) {
        var html = "";
        updates.each(function(e) {
            html += "<div class=\"ticket-update ticket-update-timestamp\"><span>Timestamp:</span>" + e.timestamp + "</div>";
            html += "<div class=\"ticket-update ticket-update-name\"><span>Author:</span>" + e.name + "</div>";
            html += "<div class=\"ticket-update ticket-update-subject\"><span>Subject:</span>" + e.subject + "</div>";
            html += "<div class=\"ticket-update ticket-update-message\">" + e.message + "</div>";
        }.bind(this));
        $("target-" + reference).down().update(html);
        this.showTarget(reference);
    }
});