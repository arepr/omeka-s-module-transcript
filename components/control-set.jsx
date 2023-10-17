import React from "react";

export const ControlSet = props => {
    const { compact, children } = props;

    if (compact) {
        return (
            <>{ children }</>
        );
    }

    return (
        <div className="player-controlset">
            { children }
        </div>
    );
};