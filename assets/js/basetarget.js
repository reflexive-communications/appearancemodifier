/*
 * On case of the profile is loaded in iframe, it sets the links to open
 * in the parent window instead of the iframe.
 * */
// onload
(function() {
    if (inIframe()) {
        baseTagTarget();
    }
})();
