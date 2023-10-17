import React, { useState } from "react";
import cx from "classnames";

export const SettingsList = props => {
    const { links, activeSource, onSourceChange } = props;

    const [ isOpen, setOpen ] = useState(false);

    if (links.length <= 1) { return null; }

    return (
        <div className={ cx(
            "player-settings-container", { "active": isOpen }
        ) }>
            <button
                className="player-settings fa fa-cog"
                aria-label={ Omeka.jsTranslate("Settings") }
                aria-haspopup="true"
                aria-expanded="false"
                onClick={ () => setOpen(!isOpen) }
            />
            <ul className="player-settings-list" role="menu">
                { links.map(link => (
                    <li key={ link.link }>
                        <a
                            className={ cx(
                                "radio", { "checked": link == activeSource }
                            ) }
                            role="menuitemradio"
                            aria-checked={ link == activeSource }
                            onClick={ () => onSourceChange(link) }
                        >
                            { link.rendition == "adaptive" ?
                                "Auto" : link.rendition
                            }
                        </a>
                    </li>
                )) }
            </ul>
        </div>
    );
};