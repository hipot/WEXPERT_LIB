function serializer(val) {
    var esc = {'"' : '\\"', '\\': '\\\\', '\/': '\/', '\b': '\\b', '\f': '\\f', '\n': '\\n', '\r': '\\r'};
    var cnv = function (val) {
        switch (val.constructor) {
        case String:
            //return '"' + val.replace(/["\\\/\b\f\n\r]/g, function(a){ return esc[a] }) + '"';
            return '"' + val.replace(/[\x22\\\/\b\f\n\r]/g, function(a){ return esc[a] }) + '"';
        case Array:
            var a = [], i = val.length;
            while(i--) a[i] = cnv(val[i]);
            return '[' +  a.join(',') + ']';
        case Object:
            var a = [], i = 0, k;
            for(k in val) a[i++] = (/^[a-zA-Z_][\w_]*$/.test(k) ? k : cnv(k)) + ':' + cnv(val[k]);
            return '{' +  a.join(',') + '}';
        case Date:
            return 'new Date(' + (val-0) + ')';
        default:
            return val;
        };
    };
    return '(' + cnv(val) + ')';
};