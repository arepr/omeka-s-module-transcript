$(document).ready(function () {
    $("body").on("DOMNodeInserted", function (event) {
        const container = $(event.target);
        if (container.is(".media")) {
            container.find(".chosen-select.webvtt-locale").chosen(chosenOptions);
            container.find(".webvtt-subtitles-file").on("change", function () {
                const template = container.find("[data-template]");
                const fancyTemplate = template.next(".chosen-container");
                const inputs = template.parents(".inputs");
                
                inputs.find(".webvtt-locale, .chosen-container, label")
                    .not(template).not(fancyTemplate).remove();
                
                if (this.files.length > 0) {
                    for (var i = 0; i < this.files.length; i++) {
                        const select = template.clone()
                            .removeAttr("disabled")
                            .removeAttr("data-template")
                            .show();
                        
                        var id = select.attr("name").replace("__subtitleIndex__", i);
                        select.attr("name", id).attr("id", id);
                        
                        if (this.files.length != 1) {
                            inputs.append(
                                $("<label>").text(this.files[i].name)
                                    .attr("for", id));
                        }
                        
                        inputs.append(select);
                        select.chosen(chosenOptions);
                    }
                    
                    fancyTemplate.hide();
                } else {
                    fancyTemplate.show();
                }
            });
        }
    });
});