var ordertickets = Class.create({
    initialize: function() {
        this.currentreference = '';
        this.hideTargets();
        if ($("sirportly-ticket-new")) {
            this.createNewModal();
            this.addNewLinkListener();
            this.addDepartmentListener("new");
            this.addTeamListener("new");
            this.addSubmitNewListener();
            this.addSubjectListener();
            this.addCommentListener();
            this.disableSubmit();
        }
    },
    afterInit: function() {
        this.openTicket();
    },
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
            var parameters = { reference:reference, orderid:this.orderid };
            new Ajax.Request(this.updateurl, {
                parameters: parameters,
                onSuccess: function(response) {
                    var contentarray = response.responseText.evalJSON();
                    if (typeof(contentarray) == "object") {
                        if (contentarray.result == "success") {
                            var html = contentarray.html;
                            this.updateTicket(reference, html);
                            this.createUpdateModal(reference);
                            this.createReassignModal(reference);
                            this.currentreference = "-" + reference;
                            this.addUpdateListeners(reference);
                            this.addReassignListeners(reference);
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
    updateTicket: function(reference, html) {
        $("target-" + reference).down().update(html);
        this.showTarget(reference);
    },
    createNewModal: function() {
        new Control.Modal($("sirportly-ticket-new"), {
            overlayOpacity:0.2,
            position:"relative",
            fade:true,
            closeOnClick:false,
            draggable:"sirportly-ticket-new-popup"
        });
    },
    createUpdateModal: function(reference) {
        if ($("sirportly-ticket-update-" + reference)) {
            new Control.Modal($("sirportly-ticket-update-" + reference), {
                overlayOpacity:0.2,
                position:"relative",
                fade:true,
                closeOnClick:false,
                draggable:"sirportly-ticket-update-popup"
            });
        }
    },
    createReassignModal: function(reference) {
        if ($("sirportly-ticket-reassign-" + reference)) {
            new Control.Modal($("sirportly-ticket-reassign-" + reference), {
                overlayOpacity:0.2,
                position:"relative",
                fade:true,
                closeOnClick:false,
                draggable:"sirportly-ticket-reassign-popup"
            });
        }
    },
    addDepartmentListener: function(type, reassign) {
        if (reassign) {
            var append = "_reassign";
        } else {
            var append = "";
        }
        $("sirportly_department" + append + this.currentreference).observe("change", function(e) {
            e.target.disable();
            var reference = this.currentreference.substring(1);
            var parameters = { department:e.target.value, reference:reference, type:type };
            $("sirportly_team" + append + this.currentreference).disable();
            $("sirportly_user" + append + this.currentreference).disable();
            if ($("sirportly_response" + append + this.currentreference)) {
                $("sirportly_response" + append + this.currentreference).disable();
            }
            this.disableSubmit(reassign);
            new Ajax.Request(this.teamsurl, {
                parameters: parameters,
                onSuccess: function(response) {
                    var contentarray = response.responseText.evalJSON();
                    if (contentarray.teamshtml) {
                        $("sirportly_team" + append + this.currentreference).update(contentarray.teamshtml);
                        $("sirportly_team" + append + this.currentreference).enable();
                    }
                    if (contentarray.usershtml) {
                        $("sirportly_user" + append + this.currentreference).update(contentarray.usershtml);
                        $("sirportly_user" + append + this.currentreference).enable();
                    }
                    if (contentarray.responseshtml) {
                        $("sirportly_response" + this.currentreference).update(contentarray.responseshtml);
                        $("sirportly_response" + this.currentreference).enable();
                        $("sirportly_comment" + this.currentreference).clear();
                    }
                    this.enableSubmit(reassign);
                    e.target.enable();
                }.bind(this)
            });
        }.bind(this));
    },
    addTeamListener: function(type, reassign) {
        if (reassign) {
            var append = "_reassign";
        } else {
            var append = "";
        }
        $("sirportly_team" + append + this.currentreference).observe("change", function(e) {
            e.target.disable();
            var reference = this.currentreference.substring(1);
            var parameters = { team:e.target.value, reference:reference, type:type };
            $("sirportly_user" + append + this.currentreference).disable();
            this.disableSubmit(true);
            new Ajax.Request(this.usersurl, {
                parameters: parameters,
                onSuccess: function(response) {
                    $("sirportly_user" + append + this.currentreference).update(response.responseText);
                    $("sirportly_user" + append + this.currentreference).enable();
                    this.enableSubmit(true);
                    e.target.enable();
                }.bind(this)
            });
        }.bind(this));
    },
    addSubmitNewListener: function() {
        $$("span.sirportly-ticket-new-popup-submit")[0].down().observe("click", function(e) {
            this.disableSubmit();
            var parameters =  $("sirportly-ticket-new-form").serialize(true);
            new Ajax.Request(this.submitnewurl, {
                parameters: parameters,
                onSuccess: function(response) {
                    var contentarray = response.responseText.evalJSON();
                    if (typeof(contentarray) == "object") {
                        if (contentarray.status == "success") {
                            this.enableSubmit();
                            this.clearForm();
                            Control.Modal.close()
                            varienTabs.prototype.showTabContent($("sales_order_view_tabs_sirportly_tickets"));
                        } else {
                            this.enableSubmit();
                            alert("Failed to create ticket!")
                        }
                    }
                }.bind(this)
            });
        }.bind(this));
    },
    addSubmitUpdateListener: function() {
        $$("span.sirportly-ticket-update-popup-submit" + this.currentreference)[0].down().observe("click", function(e) {
            this.disableSubmit();
            var parameters =  $("sirportly-ticket-update-form" + this.currentreference).serialize(true);
            new Ajax.Request(this.submitupdateurl, {
                parameters: parameters,
                onSuccess: function(response) {
                    var contentarray = response.responseText.evalJSON();
                    if (typeof(contentarray) == "object") {
                        if (contentarray.status == "success") {
                            this.enableSubmit();
                            this.clearForm();
                            Control.Modal.close();
                            window.ticketreference = this.currentreference.substring(1);
                            varienTabs.prototype.showTabContent($("sales_order_view_tabs_sirportly_tickets"));
                        } else {
                            this.enableSubmit();
                            if (contentarray.status == 'empty') {
                                alert("Nothing to update!");
                            } else {
                                alert("Failed to update ticket!");
                            }
                        }
                    }
                }.bind(this)
            });
        }.bind(this));
    },
    addSubmitReassignListener: function() {
        $$("span.sirportly-ticket-reassign-popup-submit" + this.currentreference)[0].down().observe("click", function(e) {
            this.disableSubmit(true);
            var parameters =  $("sirportly-ticket-reassign-form" + this.currentreference).serialize(true);
            new Ajax.Request(this.reassignurl, {
                parameters: parameters,
                onSuccess: function(response) {
                    var contentarray = response.responseText.evalJSON();
                    if (typeof(contentarray) == "object") {
                        if (contentarray.status == "success") {
                            this.enableSubmit(true);
                            Control.Modal.close();
                            window.ticketreference = this.currentreference.substring(1);
                            varienTabs.prototype.showTabContent($("sales_order_view_tabs_sirportly_tickets"));
                        } else {
                            this.enableSubmit(true);
                            if (contentarray.status == 'empty') {
                                alert("Selections unchanged, cannot reassign!");
                            } else {
                                alert("Failed to reassign ticket!");
                            }
                        }
                    }
                }.bind(this)
            });
        }.bind(this));
    },
    addSubjectListener: function() {
        $("sirportly_subject").observe("keyup", function(e) {
            this.enableSubmit();
        }.bind(this));
    },
    addCommentListener: function() {
        $("sirportly_comment" + this.currentreference).observe("keyup", function(e) {
            this.enableSubmit();
        }.bind(this));
    },
    disableSubmit: function(reassign) {
        if (this.currentreference) {
            if (reassign) {
                var submit = $$("span.sirportly-ticket-reassign-popup-submit" + this.currentreference)[0].down();
            } else {
                var submit = $$("span.sirportly-ticket-update-popup-submit" + this.currentreference)[0].down();
            }
        } else {
            var submit = $$("span.sirportly-ticket-new-popup-submit")[0].down();
        }
        submit.disable();
        submit.setOpacity(0.5);
    },
    enableSubmit: function(reassign) {
        if (this.currentreference) {
            if (reassign) {
                var submit = $$("span.sirportly-ticket-reassign-popup-submit" + this.currentreference)[0].down();
            } else {
                var submit = $$("span.sirportly-ticket-update-popup-submit" + this.currentreference)[0].down();
            }
            submit.enable();
            submit.setOpacity(1);
        } else {
            if ($("sirportly_subject").value && $("sirportly_comment").value) {
                var submit = $$("span.sirportly-ticket-new-popup-submit" + this.currentreference)[0].down();
                submit.enable();
                submit.setOpacity(1);
            } else {
                this.disableSubmit();
            }
        }
    },
    clearForm: function() {
        $("sirportly_subject" + this.currentreference).value = "";
        $("sirportly_comment" + this.currentreference).value = "";
    },
    addUpdateListeners: function(reference) {
        if ($("sirportly-ticket-update-" + reference)) {
            this.addDepartmentListener("update");
            this.addTeamListener("update");
            this.addSubmitUpdateListener();
            this.addCommentListener();
            this.addUpdateLinkListener(reference);
            this.addUpdateDeleteListener();
            this.addPrivateCheckboxListener();
            this.addResponseListener();
        }
    },
    addReassignListeners: function(reference) {
        if ($("sirportly-ticket-reassign-" + reference)) {
            this.addDepartmentListener("reassign", true);
            this.addTeamListener("reassign", true);
            this.addSubmitReassignListener();
            this.addReassignLinkListener(reference);
        }
    },
    addUpdateLinkListener: function(reference) {
        $("sirportly-ticket-update-" + reference).observe("click", function(e) {
            var reference = e.target.id;
            reference = reference.replace("sirportly-ticket-update", "");
            this.currentreference = reference;
        }.bind(this));
    },
    addReassignLinkListener: function(reference) {
        $("sirportly-ticket-reassign-" + reference).observe("click", function(e) {
            var reference = e.target.id;
            reference = reference.replace("sirportly-ticket-reassign", "");
            this.currentreference = reference;
        }.bind(this));
    },
    addUpdateDeleteListener: function() {
        $$("a.ticket-update-delete").each(function(el) {
            el.observe("click", function(e) {
                if (confirm("Are you sure?")) {
                    var ticket = e.target.id.split("_");
                    var update = ticket.pop();
                    ticket = ticket.pop();
                    var parameters =  { ticket:ticket, update:update };
                    new Ajax.Request(this.deleteupdateurl, {
                        parameters: parameters,
                        onSuccess: function(response) {
                            var contentarray = response.responseText.evalJSON();
                            if (typeof(contentarray) == "object") {
                                if (contentarray.status == "success") {
                                    this.hideTarget(ticket);
                                    this.getUpdates(ticket);
                                } else {
                                    alert("Failed to delete update!");
                                }
                            }
                        }.bind(this)
                    });
                }
            }.bind(this));
        }.bind(this));
    },
    addNewLinkListener: function() {
        $("sirportly-ticket-new").observe("click", function(e) {
            this.currentreference = "";
        }.bind(this));
    },
    openTicket: function() {
        if (window.ticketreference) {
            if ($("target-" + window.ticketreference)) {
                this.getUpdates(window.ticketreference);
            }
            window.ticketreference = null;
        }
    },
    addPrivateCheckboxListener: function() {
        $("sirportly_private" + this.currentreference).observe("change", function(e) {
            if (e.target.checked) {
                var notify = $("sirportly_notify" + this.currentreference);
                notify.checked = false;
                notify.disable();
            } else {
                $("sirportly_notify" + this.currentreference).enable();
            }
        }.bind(this));
    },
    addResponseListener: function() {
        $("sirportly_response" + this.currentreference).observe("change", function(e) {
            if (e.target.value) {
                $("sirportly_comment" + this.currentreference).value += "{{response." + e.target.value + "}}";
                e.target.clear();
            }
        }.bind(this));
    }
});