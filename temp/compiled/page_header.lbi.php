<link href="themes/ecmoban_jumei/qq/images/qq.css" rel="stylesheet" type="text/css" />
 
<script type="text/javascript">
var process_request = "<?php echo $this->_var['lang']['process_request']; ?>";
</script>
<script type="text/javascript">
//设为首页 www.ecmoban.com
function SetHome(obj,url){
    try{
        obj.style.behavior='url(#default#homepage)';
       obj.setHomePage(url);
   }catch(e){
       if(window.netscape){
          try{
              netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
         }catch(e){
              alert("抱歉，此操作被浏览器拒绝！\n\n请在浏览器地址栏输入“about:config”并回车然后将[signed.applets.codebase_principal_support]设置为'true'");
          }
       }else{
        alert("抱歉，您所使用的浏览器无法完成此操作。\n\n您需要手动将【"+url+"】设置为首页。");
       }
  }
}
 
//收藏本站 bbs.ecmoban.com
function AddFavorite(title, url) {
  try {
      window.external.addFavorite(url, title);
  }
catch (e) {
     try {
       window.sidebar.addPanel(title, url, "");
    }
     catch (e) {
         alert("抱歉，您所使用的浏览器无法完成此操作。\n\n加入收藏失败，请使用Ctrl+D进行添加");
     }
  }
}
</script>

<div id="Top">
	<div class="tops"></div>
    <div class=" block header_bg" style="margin-bottom:0px;">
        
        <div class="top_nav_header">
            <div class="top_nav">
                <script type="text/javascript">
                    //初始化主菜单
                    function sw_nav(obj,tag){
                        var DisSub = document.getElementById("DisSub_"+obj);
                        var HandleLI= document.getElementById("HandleLI_"+obj);
                        if(tag==1){
                            DisSub.style.display = "block";
                        }else{
                            DisSub.style.display = "none";
                        }
                    }
                </script>
                <div class="block">
                	<div class="f_l left_login">
                    	<?php echo $this->smarty_insert_scripts(array('files'=>'transport.js,utils.js')); ?>
                    	<font id="ECS_MEMBERZONE" ><?php 
$k = array (
  'name' => 'member_info',
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?> </font>
                    </div>
                    
                    <ul class="top_bav_l">
                        <li class="top_sc">&nbsp;&nbsp;<a href="javascript:void(0);" onclick="AddFavorite('我的网站',location.href)">收藏本站</a></li>
                        <li>&nbsp;&nbsp;&nbsp;关注我们：</li>
                        <li style="border:none" class="menuPopup"  onMouseOver="sw_nav(1,1);" onMouseOut="sw_nav(1,0);">
                            <a id="HandleLI_1" href="javascript:;" title="微博" class="attention"></a>
                            <div id=DisSub_1 class="top_nav_box  top_weibo">
                                <a href="http://e.weibo.com/ECMBT" target="_blank" title="新浪微博" class="top_weibo"></a>
                                <a href="http://e.t.qq.com/ecmoban_com" target="_blank" title="QQ微博" class="top_qq"></a> 
                            </div> 
                        </li> 
                        <li class="menuPopup" onMouseOver="sw_nav(2,1);" onMouseOut="sw_nav(2,0);">
                            <a id="HandleLI_2" href="javascript:;" title="微信" class="top_weixin"></a> 
                            <div id="DisSub_2" class="weixinBox" style="display: none;"><img src="themes/ecmoban_jumei/images/weixin.png" style="width:150px; height:190px;  background:#0000CC" width="150" height="190"> </div> 
                        </li>
                    </ul>
                    
                    <div class="header_r">
                    
                   
                 
                     <?php if ($this->_var['navigator_list']['top']): ?>
                        <?php $_from = $this->_var['navigator_list']['top']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'nav');$this->_foreach['nav_top_list'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['nav_top_list']['total'] > 0):
    foreach ($_from AS $this->_var['nav']):
        $this->_foreach['nav_top_list']['iteration']++;
?>
                               <a href="<?php echo $this->_var['nav']['url']; ?>" <?php if ($this->_var['nav']['opennew'] == 1): ?> target="_blank" <?php endif; ?>><?php echo $this->_var['nav']['name']; ?></a>
                                <?php if (! ($this->_foreach['nav_top_list']['iteration'] == $this->_foreach['nav_top_list']['total'])): ?>
                                <?php endif; ?>
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                       <?php endif; ?>
                     
                    </div>
                </div>
            </div>
        </div>
        <div class="clear_f"></div>
        <div class="header_top logo_wrap clearfix">
            <a class="logo_new" href="index.php"><img src="themes/ecmoban_jumei/images/logo.gif" /></a>
            <div class="ser_n">
                <form id="searchForm" class="searchBox clearfix" name="searchForm" method="get" action="search.php" onSubmit="return checkSearchForm()">
                    <span class="ipt1"><input name="keywords" type="text" id="keyword" value="<?php echo htmlspecialchars($this->_var['search_keywords']); ?>" class="searchKey" /></span>
                    <span class="ipt2"><input type="submit"  name="imageField" class="fm_hd_btm_shbx_bttn" value="搜美妆"></span>
                </form>
                <div class="clear_f"></div>
                <ul class="searchType none_f"></ul>
                <?php if ($this->_var['searchkeywords']): ?>
                <div class="search_tig">
                    <?php $_from = $this->_var['searchkeywords']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'val');if (count($_from)):
    foreach ($_from AS $this->_var['val']):
?>
                    <a href="search.php?keywords=<?php echo urlencode($this->_var['val']); ?>" target="_blank" class="track"><?php echo $this->_var['val']; ?></a>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </div>    
            <?php endif; ?>
            </div>
            
            <ul class="cart_info">
                <li id="ECS_CARTINFO">
                	<div class="top_cart">
                    	<img src="themes/ecmoban_jumei/images/cart.gif" />
                    	<span class="carts_num none_f"><?php 
$k = array (
  'name' => 'cart_info',
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?></span>
                        <a href="flow.php" class="shopborder">去购物车结算</a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>

<div style="clear:both"></div>
 
<div class="menu_box clearfix"> 
<div class="block"> 
<div class="menu">
  <a href="index.php"<?php if ($this->_var['navigator_list']['config']['index'] == 1): ?> class="cur"<?php endif; ?>><?php echo $this->_var['lang']['home']; ?></a>
  <?php $_from = $this->_var['navigator_list']['middle']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'nav');$this->_foreach['nav_middle_list'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['nav_middle_list']['total'] > 0):
    foreach ($_from AS $this->_var['nav']):
        $this->_foreach['nav_middle_list']['iteration']++;
?>
  <a href="<?php echo $this->_var['nav']['url']; ?>" <?php if ($this->_var['nav']['opennew'] == 1): ?>target="_blank" <?php endif; ?> <?php if ($this->_var['nav']['active'] == 1): ?> class="cur"<?php endif; ?>><?php echo $this->_var['nav']['name']; ?></a>
 
 <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</div> 
</div>
</div>
 
 

 


