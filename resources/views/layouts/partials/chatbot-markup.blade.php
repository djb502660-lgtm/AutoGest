<script>
window.AutoGestChat = window.AutoGestChat || {};

window.AutoGestChat.escapeHtml = function (text) {
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
};

window.AutoGestChat.formatReply = function (text) {
    return window.AutoGestChat.escapeHtml(text)
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\n/g, '<br>');
};
</script>
