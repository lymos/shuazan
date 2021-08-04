var config = {
		host: "http://www.doucaicai66.com/",
};
var isLogin = function(path, retect){
	// return true;
	var token = plus.storage.getItem("token"),
		token_expire = plus.storage.getItem("token_expire");
	if(token == "" || ! token){
		if(retect){
			gotoLogin();
		}
		return false;
	}
	return true;
};

var copyFun = function(copy,tips) {  
    if(!tips){  
        tips="已成功复制到剪贴板";  
    }  
   // loading();  
    mui.plusReady(function() {  
        //判断是安卓还是ios  
        if (mui.os.ios) {  
            //ios  
            var UIPasteboard = plus.ios.importClass("UIPasteboard");  
            var generalPasteboard = UIPasteboard.generalPasteboard();  
            //设置/获取文本内容:  
            generalPasteboard.plusCallMethod({  
                setValue: copy,  
                forPasteboardType: "public.utf8-plain-text"  
            });  
            generalPasteboard.plusCallMethod({  
                valueForPasteboardType: "public.utf8-plain-text"  
            });  
            mui.toast(tips);  
         //   loading_close();  
        } else {  
            //安卓  
            var context = plus.android.importClass("android.content.Context");  
            var main = plus.android.runtimeMainActivity();  
            var clip = main.getSystemService(context.CLIPBOARD_SERVICE);  
            plus.android.invoke(clip, "setText", copy);  
            mui.toast(tips);  
         //   loading_close();  
        }  
    });  
};

var goto = function(url, id) {
	mui.openWindow({
		url: url,
		id: id,
		preload: true,
		extras: {
			is_back: true
		},
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

};

var getToken = function() {
	var token = plus.storage.getItem("token"),
		userid = plus.storage.getItem("userid"),
		token_expire = plus.storage.getItem("token_expire");
	return {
		userid: userid,
		token: token,
		token_expire: token_expire
	};
};

var gotoLogin = function() {
	goto("login.html");
};

var setLogin  = function(token){
	plus.storage.setItem("token", token.token);
	plus.storage.setItem("token_expire", token.token_expire.toString());
	plus.storage.setItem("userid", token.userid);
	// 设置过期时间
	// localStorage.setItem("expire", token.expire);
};

var delToken = function(){
	plus.storage.clear();	
};

var refresh = function(){
	plus.webview.getLaunchWebview().reload();
	plus.webview.getWebviewById("html/tab-task.html").reload();
	plus.webview.getWebviewById("html/tab-gain.html").reload();
	plus.webview.getWebviewById("html/tab-me.html").reload();
};
