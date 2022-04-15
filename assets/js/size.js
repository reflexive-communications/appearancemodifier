/**
 * In case of the profile is loaded in iframe, the height of the content
 * has to be passed to the parent js, to be able to update the iframe
 * container height. The communication is based on messages. In case of
 * onload event or resize event, it sends a message to the parent window.
 * The message contains a type, that has to be resize, and a height that
 * stores the value of the current height.
 * Event listener is not necessary as we don't expect replies or any
 * other kind of messages from the parent.
 */
// It returns true, if the self window object is NOT the top level window object.
function inIframe() {
    try {
        return window.self !== window.top;
    } catch (e) {
        return true;
    }
}

// It sends the resize message to the parent iframe.
function sendResizeMessage() {
    let bodyElement = window.self.document.querySelector('body');
    let message = {
        type: "resize",
        height: bodyElement.offsetHeight,
    };
    let to = allowedMessageReceiver || '*';
    window.self.parent.postMessage(message, to);
}

// The resize event handler function.
function resizeEventHandler() {
    setTimeout(sendResizeMessage, 200);
}

// onload
(function () {
    if (inIframe()) {
        setTimeout(sendResizeMessage, 200);
        window.self.addEventListener('resize', resizeEventHandler);
    }
})();
