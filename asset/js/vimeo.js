$(document).ready(function () {
    // User events
    $(".vimeo-playpause, .vimeo-cellophane").click(togglePlayPause);
    
    $(".vimeo-timecode")
        .on('change', seekTimecode)
        .on('mousemove', timecodeHover);
        
    $(".vimeo-controls input[type=\"range\"]").on('input', function () {
        sliderHack($(this));
    });
    
    $(".vimeo-mute").click(toggleMute);
    
    $(".vimeo-volume").on('change', setVolume);
    
    $(".vimeo-timecode, .vimeo-volume").on('keydown', jumpFive);
    
    $(".vimeo-settings").click(function () {
        $(".vimeo-settings-container").toggleClass("active");
    });
    
    $(".vimeo-settings-list li a").click(setResolution);
    
    if (document.pictureInPictureEnabled) {
        $(".vimeo-pip").click(togglePictureInPicture);
    } else {
        $(".vimeo-pip").remove();
    }
    
    const tester = $("video")[0];
    if (document.fullscreenEnabled ||
        document.webkitFullscreenEnabled ||
        document.mozFullScreenEnabled ||
        tester.webkitEnterFullscreen ||
        tester.requestFullscreen) {
        $(".vimeo-fullscreen").click(toggleFullscreen);
        $(document).on("fullscreenchange", uiFullscreen);
    } else {
        $(".vimeo-fullscreen").remove();
    }
    
    $(".vimeo-header select").on('change', uiTrackLanguage);
    
    $(".vimeo-close").click(function () {
        $(".vimeo-sidebar").remove();
    });
    
    // Player events
    $(".vimeo-container video")
        .each(hlsBootstrap)
        .each(setTextTrackMode)
        .on("loadedmetadata", buildTrackDOM)
        .on("play", uiPlay)
        .on("pause", uiPause)
        .on("timeupdate", uiTimecode)
        .on("durationchange", uiDuration)
        .on("progress", uiBuffer)
        .on("waiting", uiBeginBuffering)
        .on("playing", uiEndBuffering)
        .on("playing", setTextTrackMode)
        .on("volumechange", uiVolume);
     
    $(".vimeo-container video track")
        .on("cuechange", uiCueChange);
});

function component(sibling, selector) {
    return $(sibling).parents(".vimeo-container").find(selector);
}

function video(sibling) {
    return component(sibling, "video")[0];
}

function textTrack(sibling) {
    const lang = component(sibling, ".vimeo-header select").val();
    const tracks = video(sibling).textTracks;
    for (var i = 0; i < tracks.length; i++) {
        if (tracks[i].language == lang) {
            return tracks[i];
        }
    }
    
    return false;
}

function hlsBootstrap() {
    if (Hls.isSupported()) {
        const player = $(this);
        const source = player.find("source[type=\"application/vnd.apple.mpegurl\"]");
        if (source.length != 0) {
            var hls = new Hls();
            hls.loadSource(source.attr('src'));
            hls.attachMedia(player[0]);
            player.data("hls", hls);
        }
    }
}

function setTextTrackMode() {
    for (var i = 0; i < this.textTracks.length; i++) {
        this.textTracks[i].mode = (this.textTracks[i].kind == 'metadata')
            ? 'hidden' : 'disabled';
    }
}

function buildTrackDOM() {
    if (component(this, ".vimeo-track").length > 0) {
        return;
    }
    
    const player = video(this);
    const time = player.currentTime;
    const lang = component(this, ".vimeo-header select").val();
    
    for (var i = 0; i < player.textTracks.length; i++) {
        if (player.textTracks[i].kind != 'metadata') { continue; }
        
        var container = $("<div>").addClass("vimeo-track")
            .attr("lang", player.textTracks[i].language);
            
        if (lang == player.textTracks[i].language) {
            container.addClass("active");
        }
        
        for (var j = 0; j < player.textTracks[i].cues.length; j++) {
            var elem = $("<p>").html(player.textTracks[i].cues[j].getCueAsHTML())
                .attr("data-index", j)
                .click(seekToCuePoint);
                
            if (lang == player.textTracks[i].language &&
                time >= player.textTracks[i].cues[j].startTime &&
                time <= player.textTracks[i].cues[j].endTime) {
                elem.addClass("active");
            }
            
            container.append(elem);
        }
        
        component(this, ".vimeo-track-container").append(container);
    }
}

function seekToCuePoint() {
    var index = parseInt($(this).attr("data-index"));
    var time = textTrack(this).cues[index].startTime + 0.1;
    component(this, ".vimeo-timecode").val(time).trigger('change');
}

function togglePlayPause() {
    const player = video(this);
    player.paused ? player.play() : player.pause();
}

function seekTimecode() {
    const seconds = $(this).val();
    video(this).currentTime = seconds;
}

function timecodeHover(event) {
    const width = $(this).width();
    const center = width / 2;
    
    const thumbWidth = 7;
    const offset = (event.offsetX - center) * thumbWidth / center;
    
    const percent = Math.max(0, Math.min(1, (event.offsetX + offset) / width));
    const timecode = percent * $(this).attr("max");
    
    $(".vimeo-timecode-tooltip").css("--mouseX", event.offsetX + "px")
        .text(formatTime(timecode));
}

function toggleMute() {
    const player = video(this);
    player.volume = (player.volume == 0) ? 1 : 0;
    component(this, ".vimeo-volume").val(player.volume);
    uiVolume();
}

function setVolume() {
    video(this).volume = $(this).val();
}

function setResolution() {
    component(this, ".vimeo-settings-container").removeClass("active");
    component(this, ".vimeo-settings-list a.checked").removeClass("checked");
    
    const resolution = $(this).addClass("checked").text();
    const player = video(this);
    
    const time = player.currentTime;
    const playing = !player.paused;
    
    if (resolution == "Auto") {
        hlsBootstrap.call(player);
    } else {
        const source = component(this, "source[data-resolution=\"" + resolution + "\"");
        if (source.length == 0) { return; }
        
        var hls = $(player).data("hls");
        if (hls) {
            hls.detachMedia();
            $(player).removeData("hls");
        }
        
        $(player).attr("src", source.attr("src"));
        player.load();
    }
    
    player.currentTime = time;
    if (playing) { player.play(); }
}

function togglePictureInPicture() {
    const player = video(this);
    const open = document.pictureInPictureElement ||
        document.webkitPictureInPictureElement ||
        document.mozPictureInPictureElement;
    
    (open) ? document.exitPictureInPicture() :
        player.requestPictureInPicture();
}

function toggleFullscreen() {
    const container = component(this, ".vimeo-aspect")[0];
    
    if (document.fullscreenElement) {
        document.exitFullscreen();
    } else if (document.webkitFullscreenElement) {
        document.webkitExitFullscreen();
    } else if (document.mozFullScreenElement) {
        document.mozExitFullScreen();
    } else if (container.requestFullscreen) {
        container.requestFullscreen()
    } else if (container.webkitRequestFullscreen) {
        container.webkitRequestFullscreen();
    } else if (container.mozRequestFullScreen) {
        container.mozRequestFullScreen();
    } else {
        const player = video(this);
        if (player.webkitEnterFullscreen) {
            player.webkitEnterFullscreen();
        } else if (player.requestFullscreen) {
            player.requestFullscreen();
        }
    }
}

function uiPlay() {
    setARIALabel(component(this, ".vimeo-playpause")
        .attr("class", "vimeo-playpause fa fa-pause"), "pause");
    component(this, ".vimeo-aspect").removeClass("paused");
}

function uiPause() {
    setARIALabel(component(this, ".vimeo-playpause")
        .attr("class", "vimeo-playpause fa fa-play"), "play");
    component(this, ".vimeo-aspect").addClass("paused");
}

function uiTimecode() {
    sliderHack(component(this, ".vimeo-timecode")
        .attr("aria-valuetext", formatTime(this.currentTime)));
    
    if (!component(this, ".vimeo-timecode").is(":active")) {
        component(this, ".vimeo-timecode").val(this.currentTime);
    }
}

function uiDuration() {
    const duration = video(this).duration;
    component(this, ".vimeo-timecode").attr("max", duration);
    component(this, ".vimeo-duration").text(formatTime(duration));
}

function uiBuffer() {
    var buffered = 0;
    for (var i = 0; i < this.buffered.length; i++) {
        if (buffered < this.buffered.end(i)) {
            buffered = this.buffered.end(i);
        }
    }
    component(this, ".vimeo-buffer").val(buffered / this.duration);
}

function uiBeginBuffering() {
    component(this, ".vimeo-aspect").addClass("buffering");
}

function uiEndBuffering() {
    component(this, ".vimeo-aspect").removeClass("buffering");
}

function uiVolume() {
    const icon = (this.volume == 0) ? "fa fa-volume-off" :
        (this.volume <= 0.5) ? "fa fa-volume-down" : "fa fa-volume-up";
    const state = (this.volume == 0) ? "unmute" : "mute";
    
    setARIALabel(component(this, ".vimeo-mute").attr("class", "vimeo-mute " + icon), state);
    sliderHack(component(this, ".vimeo-volume").attr("aria-valuetext", (this.volume * 100) + "%"));
}

function uiFullscreen() {
    const state = (document.fullscreenElement ||
        document.webkitFullscreenElement ||
        document.mozFullScreenElement) ? "close" : "open";
    
    setARIALabel($(".vimeo-fullscreen")
        .toggleClass("fa-expand").toggleClass("fa-compress"), state);
}

function uiTrackLanguage() {
    const lang = component(this, ".vimeo-header select").val();
    component(this, ".vimeo-track.active").removeClass("active");
    component(this, ".vimeo-track[lang=\"" + lang + "\"").addClass("active");
    uiCueChange.call(this);
}

function uiCueChange() {
    component(this, ".vimeo-track.active p.active").removeClass("active");
    
    const cues = textTrack(this).cues;
    const time = video(this).currentTime;
    for (var i = 0; i < cues.length; i++) {
        if (time >= cues[i].startTime &&
            time <= cues[i].endTime) {
            component(this, ".vimeo-track.active p[data-index=\"" + i + "\"]").addClass("active");
        }
    }
}

function jumpFive(event) {
    if (event.which == 37 || event.which == 39) {
        const increment = ($(event.target).is(".vimeo-timecode")) ?
            ((event.which == 37) ? -5 : 5) :
            ((event.which == 37) ? -0.1 : 0.1);
        
        $(this).val(function (i, val) {
            return parseFloat(val) + increment;
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
    if (isNaN(percent)) { return; }
    return elem.css("--value", percent + "%");
}

function formatTime(seconds) {
    seconds = Math.round(seconds);
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.round(seconds % 60);
    return [
        h,
        m > 9 ? m : (h ? '0' + m : m || '0'),
        s > 9 ? s : '0' + s
    ].filter(Boolean).join(':');
}