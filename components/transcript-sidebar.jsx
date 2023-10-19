import React, { useRef, useState, useEffect } from "react";
import { WebVTT } from "videojs-vtt.js";
import cx from "classnames";

export const TranscriptSidebar = props => {
    const { textTracks, defaultTrack, playheadTime, onSeek } = props;

    const firstCueRef = useRef();
    const trackContainerRef = useRef();

    const [ selectedTrack, setSelectedTrack ] = useState(defaultTrack);
    const [ cues, setCues ] = useState([]);
    const [ isScrolled, setScrolled ] = useState(false);
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

    useEffect(() => {
        if (!firstCueRef?.current || !trackContainerRef?.current) { return; }

        const callback = entries => {
            setScrolled(!entries[0].isIntersecting);
        };

        const observer = new IntersectionObserver(callback, {
            root: trackContainerRef.current,
            threshold: 0.75
        });
        observer.observe(firstCueRef.current);

        return () => observer.unobserve(firstCueRef.current);
    }, [ cues ]);

    if (isClosed) { return null; }

    return (
        <div className={ cx(
            "player-sidebar", { "loading": !cues.length }
        ) } >
            <div className={ cx(
                "player-header", { "scrolled": isScrolled }
            ) }>
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
            <div
                ref={ trackContainerRef }
                className="player-track-container"
            >
                <div
                    className="player-track active"
                    lang={ selectedTrack }
                >
                    { cues.map((cue, index) => {
                        const isActive = cue.startTime <= playheadTime &&
                            cue.endTime >= playheadTime;

                        return (
                            <p
                                ref={ index === 0 ? firstCueRef : undefined }
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