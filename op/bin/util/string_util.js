exports.trimTr = function(source) {
    source = source.toString();

    source = source.replace(/\r\n/g, '');
    source = source.replace(/\n/g, '');

    return source;
};

