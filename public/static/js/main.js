var config = {
	// host: "http://www.doucaicai66.com/"
	host: "http://doucc.com/",
};
var isLogin = function(path, retect) {
	// 验证过期时间 过期则删除所有
	// localStorage.getItem("expire");
	
	var token = localStorage.getItem("token"),
		token_expire = localStorage.getItem("token_expire");
	if (token == "" || !token) {
		if(retect){
			goto('index.php?s=home/login');
		}	
		return false;
	}
	return true;
};

var copyFun = function(node) {
	if (!node) {
		return;
	}
	var result;
	// 将复制内容添加到临时textarea元素中
	var tempTextarea = document.createElement('textarea');
	document.body.appendChild(tempTextarea);
	if (typeof(node) == 'object') {
		// 复制节点中内容
		// 是否表单
		if (node.value) {
			tempTextarea.value = node.value;
		} else {
			tempTextarea.value = node.innerHTML;
		}
	} else {
		// 直接复制文本
		tempTextarea.value = node;
	}
	// 判断设备
	/*
	var u = navigator.userAgent;
	if (u.match(/(iPhone|iPod|iPad);?/i)) {
		// iOS
		// 移除已选择的元素
		window.getSelection().removeAllRanges();
		// 创建一个Range对象
		var range = document.createRange();
		// 选中
		range.selectNode(tempTextarea);
		// 执行选中元素
		window.getSelection().addRange(range);
		// 复制
		result = document.execCommand('copy');
		// 移除选中元素
		window.getSelection().removeAllRanges();

	} else {
		// 选中    
		tempTextarea.select();
		// 复制
		result = document.execCommand('Copy');
	}
	*/
	// 选中
	tempTextarea.select();
	// 复制
	result = document.execCommand('Copy');
	// 移除临时文本域
	document.body.removeChild(tempTextarea);
	if (result) {
		alert('复制成功', {
			removeTime: 1000
		})
	} else {
		alert('复制失败', {
			removeTime: 1000
		})
	}

	return result;
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

var $link_box = document.getElementById('link-task2');
if (typeof $link_box != null && $link_box) {
	document.getElementById('link-task2').addEventListener('tap', function(event) {
		if (!isLogin("")) {
			//return ;
		}
		mui.openWindow({
			url: 'index.php?s=home/tabtask',
			id: 'link-task',
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
	}, false);

	document.getElementById('link-gain').addEventListener('tap', function(event) {
		if (!isLogin("")) {
			//return ;
		}
		mui.openWindow({
			url: 'index.php?s=home/tabgain',
			id: 'link-task',
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
	}, false);

	document.getElementById('link-me').addEventListener('tap', function(event) {
		if (!isLogin("")) {
			//return ;
		}
		mui.openWindow({
			url: 'index.php?s=home/tabme',
			id: 'link-task',
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
	}, false);

	document.getElementById('link-home').addEventListener('tap', function(event) {
		if (!isLogin("")) {
			//return ;
		}
		mui.openWindow({
			url: 'index.php?s=home',
			id: 'link-task',
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
	}, false);
}

var getToken = function() {
	var token = localStorage.getItem("token"),
		userid = localStorage.getItem("userid"),
		token_expire = localStorage.getItem("token_expire");
	return {
		userid: userid,
		token: token,
		token_expire: token_expire
	};
};

var gotoLogin = function() {
	goto("index.php?s=home/login");
};

var setLogin  = function(token){
	localStorage.setItem("token", token.token);
	localStorage.setItem("token_expire", token.token_expire);
	localStorage.setItem("userid", token.userid);
	// 设置过期时间
	// localStorage.setItem("expire", token.expire);
};

var delToken = function(){
	localStorage.clear();
	gotoLogin();
};
