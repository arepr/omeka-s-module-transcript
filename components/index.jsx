import React from 'react';
import ReactDOM from 'react-dom/client';
import { MediaEmbed } from './media-embed';

const rootContainers = document.querySelectorAll(".player-root");
rootContainers.forEach(root => {
    const reactRoot = ReactDOM.createRoot(root);
    const props = JSON.parse(root.getAttribute("data-props"));
    root.removeAttribute("data-props");
    
    reactRoot.render(
        <React.StrictMode>
            <MediaEmbed { ...props } />
        </React.StrictMode>
    );
});