/*
 * On case of the profile is loaded in iframe, it sets the links to open
 * in the parent window instead of the iframe.
 * */
// It returns true, if the self window object is NOT the top level window object.
function inIframe () {
    try {
        return window.self !== window.top;
    } catch (e) {
        return true;
    }
}
// It sets the target attribute of the base tag to _top value
function baseTagTarget() {
    let base = window.self.document.querySelector('base');
    if (base === null) {
        base = window.self.document.querySelector('head').appendChild(document.createElement('base'));
    }
    base.setAttribute('target', '_parent');
}
// onload
(function() {
    if (inIframe()) {
        baseTagTarget();
    }
})();
