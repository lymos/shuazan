var config = {
		host: "http://localhost/",
};
var isLogin = function(path){
	// return true;
	var token = plus.storage.getItem("token"),
		token_expire = plus.storage.getItem("token_expire");
	if(token == "" || ! token){
		mui.openWindow({
			url: path + 'login.html',
			// id: 'reg',
			preload: true,
			show: {
				aniShow: 'pop-in'
			},
			styles: {
				popGesture: 'hide'
			},
			waiting: {
				autoShow: false
			}
		});
		return false;
	}
	return true;
};