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
}