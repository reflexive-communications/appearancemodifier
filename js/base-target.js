/**
 * If profile is loaded in iframe, this sets the links to open
 * in the parent window instead of the iframe
 */

/**
 * Check if the self window object is NOT the top level window object
 */
function inIframe() {
    try {
        return window.self !== window.top;
    } catch (e) {
        return true;
    }
}

/**
 * Set the target attribute of the base tag to _top value
 */
function baseTagTarget() {
    let base = window.self.document.querySelector('base');
    if (base === null) {
        base = window.self.document.querySelector('head').appendChild(document.createElement('base'));
    }
    base.setAttribute('target', '_parent');
}

/**
 * On load
 */
(function () {
    if (inIframe()) {
        baseTagTarget();
    }
})();
