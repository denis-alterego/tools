var xmlVersions = new Array("Msxml2.XMLHTTP.6.0", "MSXML2.XMLHTTP.3.0", "MSXML2.XMLHTTP", "Microsoft.XMLHTTP");
if (typeof XMLHttpRequest == "undefined") XMLHttpRequest = function () {
    for (var e in xmlVersions) {
        try {
            return new ActiveXObject(xmlVersions[e])
        } catch (t) {
        }
    }
    throw new Error("This browser does not support XMLHttpRequest.")
}

function ArrayToURL(array) {
    var pairs = [];
    for (var key in array)
        if (array.hasOwnProperty(key))
            pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(array[key]));
    return pairs.join('&');
}

function toolsErrHandler(message, url, line, symbol, error) {
    if (typeof message == 'object')
        return;
    var server_url = window.location.toString().split("/")[2];
    var __params = {
        'logJSErr': 'logJSErr',
        'message': message,
        'url': url,
        'referer': location.href,
        'line': line,
        'user_id': user_id,
        'maxTouchPoints': navigator.maxTouchPoints,
        'platform': navigator.platform,
        'useragent': navigator.userAgent,
        'vendor': navigator.vendor,
        'innerWidth': window.innerWidth,
        'innerHeight': window.innerHeight,
        'user_uniq': user_uniq
    };
    var params = ArrayToURL(__params);
    var req = new XMLHttpRequest();
    req.open('POST', error_log_url, true);
    req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    req.setRequestHeader("Content-length", params.length);
    req.setRequestHeader("Connection", "close");
    req.send(params);
    if (typeof console != 'undefined') {
        console.error(message, 'into ', url, 'on line', line);
    }

    return true;
}

window.onerror = toolsErrHandler;