{__NOLAYOUT__}
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title></title>
		<style>
			*{
				padding:0;
				margin:0;
			}
			html,body{
				width:100%;
				height:100%;
				background: #ebecee;
				overflow: hidden;
			}
			#main{
				margin:0 auto;
				width:400px;
				height:100%;
			}
			#main .main{		
				margin: 195px auto;		
				width:352px;
				height:275px;
				background: url(/static/tpl/red-bg.png) no-repeat;
			}
			#main .main img{
				display: inline-block;
				width:352px;
				height:275px;				
			}
			#main .main .main-information{
				text-align: center;
				padding-top: 128px;
				font-size: 32px;
				color: #fff;
			}
			#main .main .bottom{
				background: #f0f;
				margin-top: 114px;
			}
			#main .main .bottom .bottom-left{
				float: left;
				margin-left: 45px;
				width:130px;
			}
			#main .main .bottom .bottom-left .main-auto{
				float: left;
			}
			#main .main .bottom .bottom-left .main-jump{
				float: left;
				margin-left: 5px;
			}
			#main .main .bottom .bottom-left .main-jump a{
				color: #2439ff;
			}
			#main .main .bottom .bottom-right{
				float: left;
				margin-left: 35px;
				width:110px;
			}
			#main .main .bottom .bottom-right .main-wait{
				float: left;
			}
			#main .main .bottom .bottom-right .main-time{
				float: left;
				margin-left: 10px;
			}
		</style>
	</head>
	<body>
		<div id="main">
			<div class="main">				
				<p class="main-information"><?php echo(strip_tags($msg));?></p>
				<div class="bottom">
					<div class="bottom-left">
						<p class="main-auto">页面自动</p>
						<p class="main-jump"><a id="href" href="<?php echo($url);?>">跳转</a></p>
					</div>
					<div class="bottom-right">
						<p class="main-wait">等待时间:</p>
						<p class="main-time" id="wait"><?php echo($wait);?></p>						
					</div>
				</div>					
			</div>				
		</div>
    <script type="text/javascript">
        (function(){
            var wait = document.getElementById('wait');
            var href = document.getElementById('href').href;
            var interval = setInterval(function(){
                var time = --wait.innerHTML;
                if(time <= 0) {
                    window.location.href = href;
                    clearInterval(interval);
                };
            }, 1000);
        })();
</script>
	</body>
</html>
