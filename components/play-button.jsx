import React from "react";
import cx from "classnames";

export const PlayButton = props => {
    const { playing, bigControl = false, onPlayPause } = props;

    return (
        <button
            className={ cx(
                "player-playpause", { "player-control-big": bigControl },
                "fa", !playing ? "fa-play" : "fa-pause"
            ) }
            aria-label={ Omeka.jsTranslate(!playing ? "Play" : "Pause") }
            onClick={ onPlayPause }
        />
    );
};