/* becvar-site: front-end visitor status update */ 
function sendOnlineStatus() {
    fetch('/api/visitor/update/activity', {
        method: 'POST'
    });
}

// send online status after 1 min
setInterval(sendOnlineStatus, 10000);

// send online status on page init
sendOnlineStatus();
