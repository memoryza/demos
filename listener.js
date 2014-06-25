var net = require('net');
var ev = require('events');
var channel = new ev.EventEmitter();
var clients = {};
var subs = {};
channel.on('join', function(id, client) {	
	if(!subs[id]) {
		clients[id] = client;
		subs[id] = function(sId, msg) {
			if(id != sId) {
				clients[id].write(msg)
			}
		}		
		channel.on('broadcast', subs[id]);
	}	
});
channel.on('leave', function(sId) {
	channel.removeListener('broadcast', subs[sId]);
	delete clients[sId];
	delete subs[sId];
	channel.emit('broadcast', sId, sId +'has leave');
	
});
channel.on('error', function(ev) {
	console.log('err msg' + ev);
});
channel.on('shutdown', function() {
	channel.emit('broadcast', '',  'some reasonCloseAll');	
	clients = {};
	subs = {};
	channel.removeAllListeners('broadcast');
});
var server = net.createServer(function(client) {
	var id = client.remoteAddress + ':' + client.remotePort;
	client.on('connect', function() {
		channel.emit('join', id, client);
	})
	channel.emit('join', id, client);
	
	client.on('data', function(data) {
		if(data.toString() == 's') {
			channel.emit('shutdown');
		} else if(data.toString() == 'r'){
			client.emit('connect');
		} else {
			channel.emit('broadcast', id, data.toString());
		}
		
	})
	client.on('close', function() {
		channel.emit('leave', id);
	});
	channel.emit('error', new Error('afdsf'));
}).listen(8888)
