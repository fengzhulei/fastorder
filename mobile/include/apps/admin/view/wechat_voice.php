{include file="pageheader"}
<div class="container-fluid" style="padding:0">
	<div class="row" style="margin:0">
	  <div class="col-md-2 col-sm-2 col-lg-1" style="padding-right:0;">{include file="wechat_left_menu"}</div>
	  <div class="col-md-10 col-sm-10 col-lg-11" style="padding-right:0;">
		<div class="panel panel-default">
			<div class="panel-heading">素材管理</div>
			<div class="panel-body">
			    <ul class="nav nav-tabs" role="tablist">
                     <li role="presentation"><a href="{url('article')}">图文消息</a></li>
                     <li role="presentation"><a href="{url('picture')}">图片</a></li>
                     <li role="presentation" class="active"><a href="{url('voice')}">语音</a></li>
                     <li role="presentation"><a href="{url('video')}">视频</a></li>
			     </ul>
			</div>
			<div class="panel-body">
    			<form action="{url('voice')}" method="post" enctype="multipart/form-data" id="voiceForm">
    			     <div class="form-group">
    			         <a href="javascript:;" class="btn btn-success" onClick="$('input[name=voice]').click();">上传语音</a><span class="text-muted"> 大小: 不超过2M, 长度: 不超过60s, 格式: mp3, amr</span>
    			         <input type="file" name="voice" style="display: none;" onChange="$('#voiceForm').submit();" />
    			     </div>
    			</form>
    			<div class="row" style="margin:0;">
    			{loop $list $v}
                    <div class="col-xs-4 col-md-2 col-lg-2 thumbnail" style="margin-right:10px;">
                        <img alt="{$v['file_name']}" src="__PUBLIC__/images/voice.png" class="img-rounded" style="height:220px" />
                        <p class="text-muted" style="word-wrap:break-word;word-break:normal;">{$v['file_name']}</p>
                        <p class="text-muted">{$v['size']}</p>
                        <div class="bg-info">
                            <ul class="nav nav-pills nav-justified" role="tablist">
                            <li role="presentation"><a href="{url('download', array('id'=>$v['id']))}" title="下载" class="ectouch-fs18"><span class="glyphicon glyphicon-download-alt"></span></a></li>
                            <li role="presentation"><a href="{url('media_edit', array('id'=>$v['id']))}" title="编辑" class="ectouch-fs18 fancybox fancybox.iframe"><span class="glyphicon glyphicon-pencil"></span></a></li>
                            <li role="presentation"><a href="{url('media_del', array('id'=>$v['id']))}" title="删除" class="ectouch-fs18"><span class="glyphicon glyphicon-trash"></span></a></li>
                            </ul>
                        </div>
                    </div>
                {/loop}
                </div>
			</div>
		</div>
		{include file="pageview"}
	  </div>
	</div>
</div>
{include file="pagefooter"}