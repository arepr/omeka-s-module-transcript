import React, { useState, useEffect } from "react";
import cx from "classnames";

export const FullscreenButton = props => {
    const { containerRef, pipSupported, onPIPToggle } = props;

    const fullscreenSupported = document.fullscreenEnabled;
    const [ isFullscreen , setFullscreen ] = useState(false);

    const handleFullscreenToggle = () => {
        if (isFullscreen) {
            document.exitFullscreen();
        } else {
            containerRef.current?.requestFullscreen();
        }
    };

    const handleFullscreenChange = event => {
        setFullscreen(document.fullscreenElement !== null);
    };

    useEffect(() => {
        document.addEventListener("fullscreenchange", handleFullscreenChange);
        return () => {
            document.removeEventListener("fullscreenchange", handleFullscreenChange);
        };
    }, []);
    
    return (
        <>
            { pipSupported && (
                <button
                    className="player-pip fa fa-external-link-square-alt"
                    aria-label={ Omeka.jsTranslate("Picture in picture") }
                    onClick={ onPIPToggle }
                />
            ) }
            { fullscreenSupported && (
                <button
                    className={ cx(
                        "player-fullscreen", "fa",
                        !isFullscreen ? "fa-expand" : "fa-compress"
                    ) }
                    aria-label={ Omeka.jsTranslate(
                        !isFullscreen ? "Fullscreen" : "Exit fullscreen"
                    ) }
                    onClick={ handleFullscreenToggle }
                />
            ) }
        </>
    );
};