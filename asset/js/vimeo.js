var player, shadowPlayer;
var ios = false;

$(document).ready(function () {
    player = new Vimeo.Player(
        $(".vimeo-container iframe").attr("aria-hidden", "true")[0]);
    shadowPlayer = $(".vimeo-sidebar video")[0];
    
    ios = (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1) ||
        /(iPad|iPhone|iPod)/gi.test(navigator.platform);
    const pipCapable = document.pictureInPictureEnabled && !window.chrome;
    const fullscreenCapable = document.fullscreenEnabled ||
        document.webkitFullscreenEnabled ||
        document.mozFullScreenEnabled ||
        (ios && (shadowPlayer.webkitEnterFullscreen || shadowPlayer.requestFullscreen));
    
    // Play/pause UI
    $("#vimeo-playpause, #vimeo-cellophane").click(togglePlayPause);
    player.on('play', updatePlay);
    player.on('pause', updatePause);
    
    $(document).on("keypress", function (event) {
        if (event.which == 32 &&
            !$(event.target).is('input[type="text"], textarea, button, a, select')) {
            togglePlayPause();
            event.preventDefault();
        }
    });
    
    // Timecode and buffer UI
    player.getDuration().then(function (duration) {
        $("#vimeo-timecode").attr("max", duration);
        $("#vimeo-duration").text(formatTime(duration));
    });
    
    $("#vimeo-timecode").on('change', function () {
        const seconds = $(this).val();
        player.setCurrentTime(seconds);
        updateTimecode(seconds);
    });
    
    player.on('timeupdate', function (event) {
        if (!$("#vimeo-timecode").is(":active")) {
            $("#vimeo-timecode").val(event.seconds);
            updateTimecode(event.seconds);
        }
    });
    
    player.on('progress', function (event) {
        $("#vimeo-buffer").val(event.percent);
    });
    
    // Volume and mute UI
    player.setVolume(1);
    
    $("#vimeo-mute").click(function () {
        player.getVolume().then(function (prevVolume) {
            player.setVolume((prevVolume == 0) ? 1 : 0);
        });
    });
    
    $("#vimeo-volume").on('change', function () {
        const volume = $(this).val();
        player.setVolume(volume / 100);
    });
    
    $("#vimeo-timecode, #vimeo-volume").on('keydown', jumpFive);
    
    player.on('volumechange', function (event) {
        const volume = event.volume * 100;
        $("#vimeo-volume").val(volume);
        updateVolume(volume);
    });
    
    // Fullscreen and picture-in-picture UI
    if (fullscreenCapable) {
        $("#vimeo-fullscreen").click(toggleFullscreen);
        player.on("fullscreenchange", updateFullscreen);
    } else {
        $("#vimeo-fullscreen").remove();
    }
    
    if (pipCapable) {
        player.on("loadeddata", function () {
            $("#vimeo-pip").removeAttr("disabled").click(togglePictureInPicture);
            player.off("loadeddata");
        });
    } else {
        $("#vimeo-pip").remove();
    }
    
    $(".vimeo-controls input[type=\"range\"]").on('input', function () {
        sliderHack($(this));
    });
    
    // Buffering UI
    player.on("bufferstart", function () { $(".vimeo-aspect").addClass("buffering"); });
    player.on("bufferend", function () { $(".vimeo-aspect").removeClass("buffering"); });
    
    // No need to setup functionality if there aren't any
    // text tracks
    if ($(".vimeo-sidebar").length == 0) { return; }
    
    downloadTracks();
    
    $(".vimeo-sidebar track[default]").on("load", function () {
        // Setup initial track UI
        switchTrack();
    });
    
    player.on('cuepoint', changeCue);
    player.on('seeked', restageActiveCues);
    
    $(".vimeo-sidebar select").on('change', switchTrack);
    
    $("#vimeo-close").click(function () {
        $(".vimeo-sidebar").remove();
    });
});

function updatePlay() {
    setARIALabel($("#vimeo-playpause").attr("class", "fa fa-pause"), "pause");
    $(".vimeo-aspect").removeClass("paused");
}

function updatePause() {
    setARIALabel($("#vimeo-playpause").attr("class", "fa fa-play"), "play");
    $(".vimeo-aspect").addClass("paused");
}

function updateTimecode(seconds) {
    sliderHack($("#vimeo-timecode")
        .attr("aria-valuetext", formatTime(seconds)));
}

function updateVolume(volume) {
    const icon = (volume == 0) ? "fa fa-volume-off" :
        (volume <= 50) ? "fa fa-volume-down" : "fa fa-volume-up";
    const state = (volume == 0) ? "unmute" : "mute";
    
    setARIALabel($("#vimeo-mute").attr("class", icon), state);
    sliderHack($("#vimeo-volume").attr("aria-valuetext", volume + "%"));
}

function updateFullscreen() {
    const state = (document.fullscreenElement ||
        document.webkitFullscreenElement ||
        document.mozFullScreenElement) ? "close" : "open";
    
    setARIALabel($("#vimeo-fullscreen")
        .toggleClass("fa-expand").toggleClass("fa-compress"), state);
}

function togglePlayPause() {
    player.getPaused().then(function (paused) {
        if (paused) { player.play(); updatePlay(); }
        else { player.pause(); updatePause(); }
    });
}

function toggleFullscreen() {
    const container = $(".vimeo-aspect")[0];
    
    if (document.fullscreenElement) {
        document.exitFullscreen();
    } else if (document.webkitFullscreenElement) {
        document.webkitExitFullscreen();
    } else if (document.mozFullScreenElement) {
        document.mozExitFullScreen();
    } else if (ios) {
        player.requestFullscreen();
    } else if (container.requestFullscreen) {
        container.requestFullscreen()
    } else if (container.webkitRequestFullscreen) {
        container.webkitRequestFullscreen();
    } else if (container.mozRequestFullScreen) {
        container.mozRequestFullScreen();
    }
}

function togglePictureInPicture() {
    player.getPictureInPicture().then(function (open) {
        (open) ? player.exitPictureInPicture() :
            player.requestPictureInPicture();
    });
}

function jumpFive(event) {
    if (event.which == 37 || event.which == 39) {
        const increment = (event.which == 37) ? -5 : 5;
        $(this).val(function (i, val) {
            return parseInt(val) + increment;
        }).trigger('change');
        return false;
    }
}

function setARIALabel(elem, data) {
    const next = elem.attr("data-label-" + data);
    return elem.attr("aria-label", next);
}

function sliderHack(elem) {
    const percent = (elem.val() / elem.attr("max")) * 100;
    return elem.css("--value", percent + "%");
}

function seekToCuePoint() {
    var id = parseInt(this.id.substr(11));
    var time = getCurrentTrack().cues[id].startTime + 0.1;
    player.setCurrentTime(time);
}

function getCurrentTrack() {
    var lang = $(".vimeo-header select").val();
    var tracks = shadowPlayer.textTracks;
    for (var i = 0; i < tracks.length; i++) {
        if (tracks[i].language == lang) {
            return tracks[i];
        }
    }
}

function downloadTracks() {
    var tracks = shadowPlayer.textTracks;
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
    var container = $("<div>").addClass("vimeo-track")
        .attr("lang", track.language);
        
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

function formatTime(seconds) {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.round(seconds % 60);
    return [
        h,
        m > 9 ? m : (h ? '0' + m : m || '0'),
        s > 9 ? s : '0' + s
    ].filter(Boolean).join(':');
}