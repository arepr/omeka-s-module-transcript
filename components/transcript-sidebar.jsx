import React, { useState, useEffect } from "react";
import { WebVTT } from "videojs-vtt.js";
import cx from "classnames";

export const TranscriptSidebar = props => {
    const { textTracks, defaultTrack, playheadTime, onSeek } = props;

    const [ selectedTrack, setSelectedTrack ] = useState(defaultTrack);
    const [ cues, setCues ] = useState([]);
    const [ isClosed, setClosed ] = useState(false);

    useEffect(() => {
        const vttFetch = async () => {
            const url = textTracks.find(track => {
                return track.language == selectedTrack;
            }).storage;

            const response = await fetch(url);
            const text = await response.text();

            let parsedCues = [];
            const parser = new WebVTT.Parser(window, WebVTT.StringDecoder());
            parser.oncue = cue => parsedCues.push(cue);
            parser.parse(text);
            parser.flush();

            setCues(parsedCues);
        };
        vttFetch();
    }, [ textTracks, selectedTrack ]);

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
                    lang={ selectedTrack }
                >
                    { cues.map(cue => {
                        const isActive = cue.startTime <= playheadTime &&
                            cue.endTime >= playheadTime;

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