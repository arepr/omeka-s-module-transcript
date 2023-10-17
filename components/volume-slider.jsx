import React from "react";
import cx from "classnames";

export const VolumeSlider = props => {
    const { volume, onVolume, muted, onMuteToggle } = props;

    const volumePercent = Math.floor(volume * 100) + "%";

    const iconName = muted ? "fa-volume-mute" :
        (volume == 0) ? "fa-volume-off" :
        (volume <= 0.5) ? "fa-volume-down" : "fa-volume-up";
    
    return (
        <>
            <button
                className={ cx(
                    "player-mute", "fa", iconName
                ) }
                aria-label={ Omeka.jsTranslate(!muted ? "Mute" : "Unmute") }
                onClick={ onMuteToggle }
            />
            <input
                className="player-volume"
                type="range"
                min="0"
                max="1"
                step="0.01"
                value={ volume }
                aria-label={ Omeka.jsTranslate("Volume") }
                aria-valuetext={ volumePercent }
                style={{ "--value": volumePercent }}
                onChange={ event => onVolume(event.target.value) }
            />
        </>
    )
};