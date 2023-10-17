import React from "react";

export const JumpButton = props => {
    const { forwards = true, onJump } = props;

    return (
        <button
            className={ forwards ? "player-skip-forward" : "player-skip-backward" }
            title={ Omeka.jsTranslate(
                forwards ? "Skip forwards 15 seconds" : "Skip backwards 15 seconds"
            ) }
            onClick={ () => onJump(forwards ? 15 : -15) }
        />
    );
};