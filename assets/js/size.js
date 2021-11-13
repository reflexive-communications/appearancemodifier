/*
 * On case of the profile is loaded in iframe, the height of the content
 * has to be passed to the parent js, to be able to update the iframe
 * container height. The communication is based on messages. On case of
 * onload event or resize event, it sends a message to the parent window.
 * The message contains a type, that has to be resize, and a height that
 * stores the value of the current height.
 * Event listener is not necessary as we don't expecect replies or any
 * other kind of messages from the parent.
 * */
// onload
(function() {
    if (inIframe()) {
        setTimeout(sendResizeMessage, 1000);
        window.self.addEventListener('resize', resizeEventHandler);
    }
})();
