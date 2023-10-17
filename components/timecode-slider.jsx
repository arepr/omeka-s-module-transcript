import React, { useEffect, useState } from "react";

export const TimecodeSlider = props => {
    const { playheadTime, bufferTime, duration, timecodeLabel, onSeek } = props;

    const playthroughPercent = Math.round((playheadTime / duration) * 100) + "%";

    const [ mouseX, setMouseX ] = useState("0px");
    const [ tooltipTimecode, setTooltipTimecode ] = useState("0:00");

    const timecodeHover = event => {
        const width = event.target.offsetWidth;
        const center = width / 2;
        
        const thumbWidth = 7;
        const offset = (event.nativeEvent.offsetX - center) * thumbWidth / center;
        
        const percent = Math.max(0, Math.min(1, (event.nativeEvent.offsetX + offset) / width));
        const timecode = percent * duration;
        
        setMouseX(event.nativeEvent.offsetX + "px");
        setTooltipTimecode(formatTime(timecode));
    };

    const formatTime = seconds => {
        seconds = Math.round(seconds);
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = Math.round(seconds % 60);
        return [
            h,
            m > 9 ? m : (h ? '0' + m : m || '0'),
            s > 9 ? s : '0' + s
        ].filter(Boolean).join(':');
    };

    return (
        <>
            { timecodeLabel && (
                <span
                    className="player-timecode-label"
                    aria-hidden="true"
                >
                    { formatTime(playheadTime) }
                </span>
            ) }
            <div className="player-timecode-container">
                <input
                    className="player-timecode"
                    type="range"
                    min="0"
                    value={ playheadTime }
                    max={ duration }
                    step={ 0.1 }
                    aria-label={ Omeka.jsTranslate("Timecode") }
                    aria-valuetext={ formatTime(playheadTime) }
                    style={{ "--value": playthroughPercent }}
                    onChange={ event => onSeek(event.target.value) }
                    onMouseMove={ timecodeHover }
                />
                <span
                    className="player-timecode-tooltip"
                    aria-hidden="true"
                    style={{ "--mouseX": mouseX }}
                >
                    { tooltipTimecode }
                </span>
                <progress
                    className="player-buffer"
                    value={ bufferTime }
                    max={ duration }
                    aria-hidden="true"
                />
            </div>
            <span
                className="player-duration"
                aria-label={ Omeka.jsTranslate("Duration") }
            >
                { formatTime(duration) }
            </span>
        </>
    );
};