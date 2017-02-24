
//创建飞船
function isWeiXin(){
	var ua = window.navigator.userAgent.toLowerCase();
	if(ua.match(/MicroMessenger/i) == 'micromessenger'){
		return true;
	}else{
		return false;
	}
}
function bgAudioPlay(flag) {
	var audio = $('#js-audio-bg')[0];
	if(flag){
		if(isWeiXin()){
			wx.ready(function() {
				audio.play();
			});
		}else {
			audio.play();
		}
	}else {
		if(isWeiXin()){
			wx.ready(function() {
				audio.pause();
			});
		}else {
			audio.pause();
		}
	}
}

function Ship(ctx){
	var lottery_vo = JSON.parse($('#hide_lottery_vo').val());
	gameMonitor.im.loadImage(['http://img.wanfantian.com/static/img/ad/battle/player.png']);
	this.width = 190; /*210*/
	this.height = 175; /*195*/
	this.left = gameMonitor.w/2 - this.width/2;
	this.top = gameMonitor.h/2.2 - 2*this.height;
	this.player = gameMonitor.im.createImage('http://img.wanfantian.com/static/img/ad/battle/player.png');

	/* 画飞船
	 * this.left，this.top 所绘制的图像在左上角的位置
	 * this.width, this.height 图像所应该绘制的尺寸
	 * */
	this.paint = function(){
		ctx.drawImage(this.player, this.left, this.top, this.width, this.height);
	}
	/*
	 * 设置飞船的位置，根据用户的手指而滑动
	 * event：触摸事件
	 * */
	this.setPosition = function(event){
		if(gameMonitor.isMobile()){
			var tarL = event.changedTouches[0].clientX;
			var tarT = event.changedTouches[0].clientY;
		}else{
			var tarL = event.offsetX;
			var tarT = event.offsetY;
		}
		this.left = tarL - this.width/2 - 16;
		this.top = tarT - this.height/2;
		if(this.left<0){
			this.left = 0;
		}
		if(this.left>750-this.width){
			this.left = 750-this.width;
		}
		if(this.top<0){
			this.top = 0;
		}
		if(this.top>gameMonitor.h - this.height){
			this.top = gameMonitor.h - this.height;
		}
		this.paint();
	}

	//通过手势控制飞船
	this.controll = function(){
		var _this = this;
		var stage = $('#gamepanel');
		var currentX = this.left,
			currentY = this.top,
			move = false;

		stage.on(gameMonitor.eventType.move, function(event){
			event.preventDefault();
			_this.setPosition(event);
		});
	}
	/*
	 * 飞船吃月饼
	 * */
	this.eat = function(foodlist){
		for(var i=foodlist.length-1; i>=0; i--){
			var f = foodlist[i];
			var result_end = {};

			if(f){
				var l1 = this.top+this.height/2 - (f.top+f.height/2);
				var l2 = this.left+this.width/2 - (f.left+f.width/2);
				var l3 = Math.sqrt(l1*l1 + l2*l2);
				if(l3<=this.height/2 + f.height/2){
					foodlist[f.id] = null;
					if(f.type==0){
						//吃到了结束游戏的东西
						//todo...在这里清空背景 可提高用户体验

						gameMonitor.im.createImage();

						var dao = DAO.lottery;
						var params = {};

						var audio_instance = gameMonitor.muisc[0];
						//失败结束游戏
						gameMonitor.stop();
						bgAudioPlay(0);

						result_end = gameMonitor.getScore();
						params = {
							'lottery_id': gameMonitor.lottery_vo.lottery_id,
							'score' : result_end.score
						};
						//传递抽奖的分数
						dao.gameScore(params, function (res) {

						});

						//游戏得分结果状态处理
						if(gameMonitor.lottery_vo.op_total>1){  //再玩一次
							//	再玩一次， 获得n积分
							$('#resultPanel').show();
							$('.sorry').show();
							$('.sorry-icon').show();
						}else if(gameMonitor.lottery_vo.op_total==1){
							//没有再玩一次， 分享获得更多游戏机会， 去抽奖
							var total_now = parseInt(gameMonitor.lottery_vo.score_total) + parseInt(result_end.score);
							$('.js-try-again').hide()
							if(total_now >= parseInt(gameMonitor.lottery_vo.spend_score)){
								//可以抽奖
								$('.sorry').hide();
								$('#resultPanel').show();
								$('.good').show();
								$('.js-chance-num').text(total_now);
							}else {
								//	分享获得更多机会， 点击分享
								$('#resultPanel').show();
								$('.sorry').show();
								$('.js-share-btn').show();
								$('.icon-share-btn').show();
								$('.chance').hide();

								//还差多少积分就可以去抽奖
								var need_score = parseInt(gameMonitor.lottery_vo.spend_score) - total_now;//差多少可以抽奖， 去分享
								$('#scorecontent').html('您一共打到<span id="sscore" class="lighttext">0</span>只宇宙飞鸡<br/>再打到<span id="need_num" class="lighttext">0</span>只飞鸡就可以参与抽奖<br>');
								$('#need_num').text(need_score);
								$('#sscore').text(result_end.score);
							}
						}
					} else{
						//显示积分
						$('#score').text(++gameMonitor.score);
						$('.heart').removeClass('hearthot').addClass('hearthot');
						var audio_instance = gameMonitor.muisc[0];
						bgAudioPlay(1);
						setTimeout(function() {
							$('.heart').removeClass('hearthot');
						}, 500);
					}
				}
			}

		}
	}
}

//app分享
function share_func() {
	var app = 0;
	var title = wechat_share.share_app_message.title;
	var content = wechat_share.share_app_message.message;
	var url = wechat_share.share_app_message.link;
	var img = wechat_share.share_app_message.img_url;

	if (M.browser.ios) {
		var share_url = 'webshare$$app=' + app + '&title=' + title + '&url=' + url + '&img=' + img + '&content=' + content;
		window.location.href = share_url;
	} else {
		window.getdata.webShare(app, url, title, content, img);
	}
}

//绘制食物
function Food(type, left, id){
	this.speedUpTime = 300;
	this.id = id;
	this.type = type;
	this.width = 120;
	this.height = 91;
	this.left = left;
	this.top = -50;
	this.speed = 0.05 * Math.pow(1.2, Math.floor(gameMonitor.time/this.speedUpTime));
	this.loop = 0;

	var p = this.type == 0 ? '/static/img/ad/battle/food1.png' : '/static/img/ad/battle/food2.png';
	this.pic = gameMonitor.im.createImage(p);
}
Food.prototype.paint = function(ctx){
	ctx.drawImage(this.pic, this.left, this.top, this.width, this.height);
}
//食物的掉落移动的速度
Food.prototype.move = function(ctx){
	if(gameMonitor.time % this.speedUpTime == 0){
		this.speed *= 1.25; /*1.2*/
	}
	this.top += ++this.loop * this.speed;
	if(this.top>gameMonitor.h){
		gameMonitor.foodList[this.id] = null;
	}else{
		this.paint(ctx);
	}
}

function ImageMonitor(){
	var imgArray = [];
	return {
		createImage : function(src){
			return typeof imgArray[src] != 'undefined' ? imgArray[src] : (imgArray[src] = new Image(), imgArray[src].src = src, imgArray[src])
		},
		loadImage : function(arr, callback){
			for(var i=0,l=arr.length; i<l; i++){
				var img = arr[i];
				imgArray[img] = new Image();
				imgArray[img].onload = function(){
					if(i==l-1 && typeof callback=='function'){
						callback();
					}
				}
				imgArray[img].src = img
			}
		}
	}
}

var gameMonitor = {
	w : 750,
	h : 2640,
	bgWidth : 750,
	bgHeight : 2640,
	time : 0,
	timmer : null,
	bgSpeed : 3, /*2*/
	bgloop : 0,
	score : 0,
	im : new ImageMonitor(),
	foodList : [],
	bgDistance : 0,//背景位置
	eventType : {
		start : 'touchstart',
		move : 'touchmove',
		end : 'touchend'
	},
	lottery_vo : JSON.parse($('#hide_lottery_vo').val()),
	muisc: $('audio'),

	init : function(){
		var _this = this;
		var canvas = document.getElementById('stage');
		var ctx = canvas.getContext('2d');

		var audio_instance = gameMonitor.muisc[0];
		bgAudioPlay(1);
		setTimeout(function () {
			bgAudioPlay(0);
		},200);

		//绘制背景
		var bg = new Image();

		_this.bg = bg;
		bg.onload = function(){
			ctx.drawImage(bg, 0, 0, _this.bgWidth, _this.bgHeight);
		}
		bg.src = 'http://img.wanfantian.com/static/img/ad/battle/bg.jpg';
		_this.initListener(ctx);

	},
	initListener : function(ctx){
		var _this = this;
		var body = $(document.body);
		var config_tips = {
			msg: '提示信息',
			padding_tb: '4%',
			padding_rl: '4%',
			top: '20%',
			font_size: 28,
			time: 2500,
			z_index: 22222
		};
		$(document).on(gameMonitor.eventType.move, function(event){
			event.preventDefault();
		});

		//再玩一次
		body.on(gameMonitor.eventType.start, '.js-try-again', function(){
			window.location.href = window.location;
		});

		body.on(gameMonitor.eventType.start, '#frontpage', function(){
			$('#frontpage').css('left', '-100%');
		});

		function playGame(left_total, _this) {
			M.load.loadTip('loadType', '开始游戏', 'delay');
			//判断是否有游戏机会
			if(left_total>=0 && gameMonitor.lottery_vo.op_total){
				$('#guidePanel').hide();
				$('.matte').hide();
				bgAudioPlay(1);
				_this.ship = new Ship(ctx);
				_this.ship.paint();
				_this.ship.controll();
				gameMonitor.run(ctx);
			}else{
				var wft = $('#guidePanel').attr('data-wft');
				$('#guidePanel').hide();
				//区分移动端与微信端，避免物理返回没有次数时的提醒
				if(wft){
					share_func();
					config_tips.msg = '您的游戏机会已用完，赶快分享获取游戏机会！';
					M.util.popup_tips(config_tips);
				}else {
					$('.js-share-to').show();
					$('.matte').show();
				}

			}
		}

		/*#guidePanel*/
		body.on(gameMonitor.eventType.start, '#guidePanel', function(){
			var is_end = $(this).attr('data-end');
			$('.matte').css({
				'z-index': '222222'
			});
			$('.matte').show();
			var dao = DAO.lottery;
			var params = {
				lottery_id: 6
			};

			//次数减1
			dao.gameBegin(params, function (res) {
				var left_total = -1;
				var data = res['data'];
				var left_total = data['left_total'];
				$('.js-chance-num').text(data['left_total']);
				if(res.status == 1){
					if(is_end == "yes"){
						alert('活动已经截止！')
					}else {
						M.load.loadTip('loadType', '游戏加载中...', 'loadType');
						playGame(left_total, _this);
					}
				}else {
					if(left_total == -1){
						var wft = $('#guidePanel').attr('data-wft');
						$('#guidePanel').hide();
						//区分移动端与微信端 再次进入时的提醒
						if(wft){
							share_func();
							config_tips.msg = '您的游戏机会已用完，赶快分享获取游戏机会！';
							M.util.popup_tips(config_tips);
						}else {
							$('.js-share-to').show();
							$('.matte').hide();
						}
					}else {
						$('.matte').hide();
						M.load.loadTip('errorType', '加载出错，请刷新重新加载...', 'delay');
					}
				}
			});
		});
	},
	//背景滚动动作
	rollBg : function(ctx){
		if(this.bgDistance>=this.bgHeight){
			this.bgloop = 0;
		}
		this.bgDistance = ++this.bgloop * this.bgSpeed;
		ctx.drawImage(this.bg, 0, this.bgDistance-this.bgHeight, this.bgWidth, this.bgHeight);
		ctx.drawImage(this.bg, 0, this.bgDistance, this.bgWidth, this.bgHeight);
	},

	run : function(ctx){
		var _this = gameMonitor;
		ctx.clearRect(0, 0, _this.bgWidth, _this.bgHeight);
		_this.rollBg(ctx);

		//绘制飞船
		_this.ship.paint();
		_this.ship.eat(_this.foodList);


		//产生月饼
		_this.genorateFood();

		//绘制月饼
		for(i=_this.foodList.length-1; i>=0; i--){
			var f = _this.foodList[i]; //产生的食物对象。
			if(f){
				f.paint(ctx);
				f.move(ctx);
			}

		}
		_this.timmer = setTimeout(function(){
			gameMonitor.run(ctx);
		}, Math.round(1000/60));

		_this.time++;
	},
	stop : function(){
		var _this = this;
		$('#stage').off(gameMonitor.eventType.start + ' ' +gameMonitor.eventType.move);
		_this.ship = null;
		setTimeout(function(){
			clearTimeout(_this.timmer);
		}, 0);

	},

	genorateFood : function(){
		var genRate = 50; //产生月饼的频率
		var random = Math.random();
		if(random*genRate>genRate-1){
			var left = Math.random()*(this.w - 50);
			var type = Math.floor(left)%2 == 0 ? 0 : 1;
			var id = this.foodList.length;
			var f = new Food(type, left, id);
			this.foodList.push(f);
		}
	},

	//重置相关的状态
	reset : function(){
		this.foodList = [];
		this.bgloop = 0;
		this.score = 0;
		this.timmer = null;
		this.time = 0;
		$('#score').text(this.score);
	},
	//得分
	getScore : function(){
		var time = Math.fl
		oor(this.time/60);
		var score = this.score;

		if(score){
			$('#scorecontent').html('您在<span id="stime" class="lighttext">0</span>秒内打到了<span id="sscore" class="lighttext">0</span>只宇宙飞鸡<br>');
		}else {
			$('#scorecontent').html('您竟然1宇宙飞鸡都没打到！加油哦！');
		}
		$('#stime').text(time);
		$('#sscore').text(score);

		var result_end = {
			'time': time,
			'score': score
		}
		return result_end;
	},
	// 判断设备是否是手机
	isMobile : function(){
		var sUserAgent= navigator.userAgent.toLowerCase(),
			bIsIpad= sUserAgent.match(/ipad/i) == "ipad",
			bIsIphoneOs= sUserAgent.match(/iphone os/i) == "iphone os",
			bIsMidp= sUserAgent.match(/midp/i) == "midp",
			bIsUc7= sUserAgent.match(/rv:1.2.3.4/i) == "rv:1.2.3.4",
			bIsUc= sUserAgent.match(/ucweb/i) == "ucweb",
			bIsAndroid= sUserAgent.match(/android/i) == "android",
			bIsCE= sUserAgent.match(/windows ce/i) == "windows ce",
			bIsWM= sUserAgent.match(/windows mobile/i) == "windows mobile",
			bIsWebview = sUserAgent.match(/webview/i) == "webview";
		return (bIsIpad || bIsIphoneOs || bIsMidp || bIsUc7 || bIsUc || bIsAndroid || bIsCE || bIsWM);
	}
}
if(!gameMonitor.isMobile()){
	gameMonitor.eventType.start = 'mousedown';
	gameMonitor.eventType.move = 'mousemove';
	gameMonitor.eventType.end = 'mousedown';
}

gameMonitor.init();
