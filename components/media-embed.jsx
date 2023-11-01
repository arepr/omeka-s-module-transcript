import React, { useRef, useState, useMemo, useCallback } from "react";
import ReactPlayer from "react-player";
import cx from "classnames";
import { TranscriptSidebar } from "./transcript-sidebar";
import { PlayButton } from "./play-button";
import { JumpButton } from "./jump-button";
import { TimecodeSlider } from "./timecode-slider";
import { VolumeSlider } from "./volume-slider";
import { SettingsList } from "./settings-list";
import { FullscreenButton } from "./fullscreen-button";
import { ControlSet } from "./control-set";

import "../asset/css/style.css";

export const MediaEmbed = props => {
    const { type, links, textTracks, defaultTrack, poster, color, compactMode, hideTranscript } = props;

    const isVideo = type == "video";

    const playerRef = useRef();
    const containerRef = useRef();

    const [ activeSource, setActiveSource ] = useState(links[0]);

    const [ playing, setPlaying ] = useState(false);
    const [ volume, setVolume ] = useState(1);
    const [ muted, setMuted ] = useState(false);
    const [ playheadTime, setPlayheadTime ] = useState(0.0);
    const [ bufferTime, setBufferTime ] = useState(0.0);
    const [ duration, setDuration ] = useState(0);
    const [ pip, setPIP ] = useState(false);
    const [ pipSupported, setPIPSupported ] = useState(
        ReactPlayer.canEnablePIP(links[0].link)
    );

    const elemWrapper = useCallback(({ children }) => (
        <>{ children }</>
    ), []);

    const playerConfig = {
        forceVideo: isVideo,
        forceAudio: !isVideo,
        attributes: {
            playsInline: true
        }
    };

    const handleProgress = progress => {
        setPlayheadTime(progress.playedSeconds);
        setBufferTime(progress.loadedSeconds);
    };
    const handleSeek = timecode => playerRef.current?.seekTo(timecode, 'seconds');
    const handleJump = amount => handleSeek(playheadTime + amount);

    return (
        <div
            className={ cx(
                "player-container", isVideo ? "player-video" : "player-audio",
                { "player-compact": compactMode }
            ) }
            role="region"
            aria-label={ Omeka.jsTranslate(isVideo ? "Video player" : "Audio player") }
            style={{ "--player-color": color }}
        >
            <div
                className={ cx(
                    "player-aspect", { "paused": !playing }
                ) }
                ref={ containerRef }
            >
                { poster && (
                    <img
                        className={ cx(
                            "player-poster", { "front": !playing && playheadTime === 0 }
                        ) }
                        src={ poster }
                        aria-hidden="true"
                    />
                ) }
                <ReactPlayer
                    ref={ playerRef }
                    url={ activeSource.link }
                    wrapper={ elemWrapper }
                    config={ playerConfig }
                    playing={ playing }
                    onPlay={ () => setPlaying(true) }
                    onPause={ () => setPlaying(false) }
                    volume={ volume }
                    muted={ muted }
                    onProgress={ handleProgress }
                    onSeek={ playhead => setPlayheadTime(playhead) }
                    onDuration={ duration => setDuration(duration) }
                    pip={ pip }
                    onEnablePIP={ () => setPIPSupported(true) }
                    onDisablePIP={ () => setPIPSupported(false) }
                />
                { isVideo && (
                    <div
                        className="player-cellophane"
                        onClick={ () => setPlaying(!playing) }
                    />
                ) }
                <div className="player-controls">
                    <ControlSet compact={ isVideo }>
                        { !isVideo && (
                            <JumpButton
                                forwards={ false }
                                onJump={ handleJump }
                            />
                        ) }
                        <PlayButton
                            playing={ playing }
                            onPlayPause={ () => setPlaying(!playing) }
                            bigControl={ !isVideo }
                        />
                        { !isVideo && (
                            <JumpButton
                                forwards={ true }
                                onJump={ handleJump }
                            />
                        ) }
                    </ControlSet>
                    <ControlSet compact={ isVideo }>
                        <TimecodeSlider
                            playheadTime={ playheadTime }
                            bufferTime={ bufferTime }
                            duration={ duration }
                            timecodeLabel={ !isVideo }
                            onSeek={ handleSeek }
                        />
                        { isVideo && (
                            <>
                                <VolumeSlider
                                    volume={ volume }
                                    onVolume={ volume => setVolume(volume) }
                                    muted={ muted }
                                    onMuteToggle={ () => setMuted(!muted) }
                                />
                                <SettingsList
                                    links={ links }
                                    activeSource={ activeSource }
                                    onSourceChange={ source => setActiveSource(source) }
                                />
                                <FullscreenButton
                                    containerRef={ containerRef }
                                    pipSupported={ pipSupported }
                                    onPIPToggle={ () => setPIP(!pip) }
                                />
                            </>
                        ) }
                    </ControlSet>
                </div>
            </div>
            { Boolean(!hideTranscript && textTracks.length) && (
                <TranscriptSidebar
                    textTracks={ textTracks }
                    defaultTrack={ defaultTrack }
                    playheadTime={ playheadTime }
                    onSeek={ handleSeek }
                />
            ) }
        </div>
    );
};