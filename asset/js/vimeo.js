var player;
$(window).on("load", function () {
    // Figure out and save aspect ratio for each video
    // and remove the hardcoded width/height
    var iframe = $("iframe[src*='vimeo.com/']");
    iframe.parent()
        .css('--aspect-width', iframe[0].width)
        .css('--aspect-height', iframe[0].height);
    iframe.removeAttr('height')
        .removeAttr('width');
        
    // No need to setup functionality if there aren't any
    // text tracks
    if ($(".vimeo-sidebar").length == 0) { return; }
    
    downloadTracks();
    
    // Link with Vimeo player API
    player = new Vimeo.Player(iframe[0]);
    player.on('cuepoint', changeCue);
    player.on('seeked', restageActiveCues);
    
    // Language changes
    $(".vimeo-sidebar select").on('change', switchTrack);
    
    // Close button
    $("#vimeo-close").click(function () {
        $(".vimeo-sidebar").remove();
    });
    
    // Setup initial track UI
    switchTrack();
});

function seekToCuePoint() {
    var id = parseInt(this.id.substr(11));
    var time = (getCurrentTrack().cues[id].startTime +
        getCurrentTrack().cues[id].endTime) / 2;
    player.setCurrentTime(time);
}

function getCurrentTrack() {
    var lang = $(".vimeo-header select").val();
    var tracks = $(".vimeo-sidebar video")[0].textTracks;
    for (var i = 0; i < tracks.length; i++) {
        if (tracks[i].language == lang) {
            return tracks[i];
        }
    }
}

function downloadTracks() {
    var tracks = $(".vimeo-sidebar video")[0].textTracks;
    for (var i = 0; i < tracks.length; i++) {
        tracks[i].mode = "showing";
    }
}

function switchTrack() {
    var track = getCurrentTrack();    
    buildTrackDOM(track);
    player.getCurrentTime().then(function (time) {
        restageActiveCues({ seconds: time });
    });
    replaceCuePoints(track);
}

function buildTrackDOM(track) {
    var container = $("<div>").addClass("vimeo-track");
    for (var i = 0; i < track.cues.length; i++) {
        container.append(
            $("<p>").html(track.cues[i].getCueAsHTML())
                .attr("id", "vimeo-line-" + i)
                .click(seekToCuePoint)
        );
    }
    
    $(".vimeo-track").remove();
    $(".vimeo-track-container").append(container);
}

function replaceCuePoints(track) {
    player.getCuePoints().then(function (cues) {
        for (var i = 0; i < cues.length; i++) {
            player.removeCuePoint(cues[i].id);
        }
    });
    
    for (var i = 0; i < track.cues.length; i++) {
        player.addCuePoint(track.cues[i].startTime, { type: "start", id: i });
        player.addCuePoint(track.cues[i].endTime, { type: "end", id: i });
    }
}

function changeCue(event) {
    var line = $("#vimeo-line-" + event.data.id);
    (event.data.type == "start") ?
        line.addClass("active") : line.removeClass("active");
}

function restageActiveCues(event) {
    $(".vimeo-track p").removeClass("active");
    
    var track = getCurrentTrack();
    for (var i = 0; i < track.cues.length; i++) {
        if (track.cues[i].startTime <= event.seconds &&
            track.cues[i].endTime >= event.seconds) {
            $("#vimeo-line-" + i).addClass("active");
        }
    }
}