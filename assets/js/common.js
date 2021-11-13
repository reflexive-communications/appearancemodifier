// It returns true, if the self window object is NOT the top level window object.
function inIframe () {
    try {
        return window.self !== window.top;
    } catch (e) {
        return true;
    }
}
// It sends the reize message to the parent iframe.
function sendResizeMessage() {
    let bodyElement = window.self.document.querySelector('body');
    let message = {
        type: "resize",
        height: bodyElement.clientHeight,
    };
    window.self.parent.postMessage(message, "*");
}
// The resize event handler function.
function resizeEventHandler() {
    setTimeout(sendResizeMessage, 1000);
}
// It sets the target attribute of the base tag to _top value
function baseTagTarget() {
    let base = window.self.document.querySelector('base');
    if (base === null) {
        base = window.self.document.querySelector('head').appendChild(document.createElement('base'));
    }
    base.setAttribute('target', '_parent');
}
