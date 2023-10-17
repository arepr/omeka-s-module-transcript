import React, { useState, useMemo, useEffect } from "react";
import cx from "classnames";

export const TranscriptSidebar = props => {
    const { playerRef, textTracks, defaultTrack, onSeek } = props;

    const internalPlayer = playerRef?.getInternalPlayer();

    const [ selectedTrack, setSelectedTrack ] = useState(defaultTrack);
    const activeTrack = useMemo(() => {
        if (!internalPlayer) { return false; }

        return [ ...internalPlayer.textTracks ].find(track => {
            return track.language == selectedTrack;
        });
    }, [ internalPlayer, selectedTrack ]);

    const [ cues, setCues ] = useState([]);
    const [ activeCues, setActiveCues ] = useState([]);
    
    const [ isClosed, setClosed ] = useState(false);

    useEffect(() => {
        if (!internalPlayer) { return; }
        
        for (const track of internalPlayer.textTracks) {
            track.mode = "hidden";
        }
    }, [ internalPlayer ]);

    const updateCues = () => {
        setCues([ ...activeTrack.cues ]);
        updateActiveCues();
    };

    const updateActiveCues = () => {
        setActiveCues([ ...activeTrack.activeCues ]);
    };

    useEffect(() => {
        if (!activeTrack) { return; }

        updateCues();

        activeTrack.addEventListener("loaded", updateCues);
        activeTrack.addEventListener("cuechange", updateActiveCues);

        return () => {
            activeTrack.removeEventListener("loaded", updateCues);
            activeTrack.removeEventListener("cuechange", updateActiveCues);
        };
    }, [ activeTrack ]);

    if (isClosed) { return null; }

    return (
        <div className={ cx(
            "player-sidebar", { "loading": !cues.length }
        ) } >
            <div className="player-header">
                <select
                    value={ selectedTrack }
                    onChange={ event => setSelectedTrack(event.target.value) }
                    aria-label={ Omeka.jsTranslate("Transcript language") }
                >
                    { textTracks.map(track => (
                        <option
                            value={ track.language }
                            key={ track.language }
                        >
                            { track["language-label"] }
                        </option>
                    )) }
                </select>
                <button
                    className="player-close fas fa-times"
                    aria-label={ Omeka.jsTranslate("Close transcript") }
                    onClick={ () => setClosed(true) }
                />
            </div>
            <div className="player-track-container">
                <div
                    className="player-track active"
                    lang={ activeTrack?.language }
                >
                    { cues.map(cue => {
                        const isActive = activeCues.some(activeCue => {
                            return cue.startTime === activeCue.startTime &&
                                cue.endTime === activeCue.endTime;
                        }); 

                        return (
                            <p
                                className={ isActive ? "active" : "" }
                                onClick={ () => onSeek(cue.startTime + 0.1) }
                                key={ cue.text }
                            >
                                { cue.text }
                            </p>
                        );
                    }) }
                </div>
            </div>
        </div>
    );
};