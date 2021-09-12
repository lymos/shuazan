var config = {
	// host: "https://www.doucaicai66.com/"
	host: "http://doucc.com/",
};
var isLogin = function(path, retect) {
	// 验证过期时间 过期则删除所有
	// localStorage.getItem("expire");
	if(! isLocalStorageSupported()){
		var token = getCookie("token"),
			token_expire = getCookie("token_expire");
	}else{
		var token = localStorage.getItem("token"),
			token_expire = localStorage.getItem("token_expire");
	}
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
	if(! isLocalStorageSupported()){
		var token = getCookie("token"),
			userid = getCookie("userid"),
			token_expire = getCookie("token_expire");
	}else{
		var token = localStorage.getItem("token"),
			userid = localStorage.getItem("userid"),
			token_expire = localStorage.getItem("token_expire");
	}
	
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
	if(! isLocalStorageSupported()){
		return setCookies(token);
	}
	localStorage.setItem("token", token.token);
	localStorage.setItem("token_expire", token.token_expire);
	localStorage.setItem("userid", token.userid);
	// 设置过期时间
	// localStorage.setItem("expire", token.expire);
};

var setCookies = function(token){
	setCookie("token", token.token, 5);
	setCookie("token_expire", token.token_expire, 5);
	setCookie("userid", token.userid, 5);
}

var delToken = function(){
	if(! isLocalStorageSupported()){
		removeCookie("token");
		removeCookie("token_expire");
		removeCookie("userid");
	}else{
		localStorage.clear();
	}
	
	gotoLogin();
};

/*设置cookie*/
function setCookie(name, value, iDay)
{
  var oDate=new Date();
  oDate.setDate(oDate.getDate()+iDay);
  document.cookie=name+'='+value+';expires='+oDate;
};
/*使用方法：setCookie('user', 'simon', 11);*/
/*获取cookie*/
function getCookie(name)
{
  var arr=document.cookie.split('; '); //多个cookie值是以; 分隔的，用split把cookie分割开并赋值给数组
  for(var i=0;i<arr[i].length;i++) //历遍数组
  {
    var arr2=arr[i].split('='); //原来割好的数组是：user=simon，再用split('=')分割成：user simon 这样可以通过arr2[0] arr2[1]来分别获取user和simon 
    
	if(arr2[0]==name) //如果数组的属性名等于传进来的name
    {
      return arr2[1]; //就返回属性名对应的值
    }
    
  }
  return ''; //没找到就返回空
};
/*使用方法：getCookie('user')*/
/*删除cookie*/
function removeCookie(name)
{
	setCookie(name, "", -1); //-1就是告诉系统已经过期，系统就会立刻去删除cookie
};
/*使用方法：removeCookie('user')*/


function isLocalStorageSupported() {
    var testKey = 'test',
        storage = window.sessionStorage;
    try {
        storage.setItem(testKey, 'testValue');
        storage.removeItem(testKey);
        return true;
    } catch (error) {
        return false;
    }
}
