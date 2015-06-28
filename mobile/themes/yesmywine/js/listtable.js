/* $Id: listtable.js 14980 2008-10-22 05:01:19Z testyang $ */
/*
 
if (typeof Ajax != 'object')
{
  alert('Ajax object doesn\'t exists.');
}
*/
if (typeof Utils != 'object')
{
  alert('Utils object doesn\'t exists.');
}

function getLastDOM(id)
{
	var obj =document.querySelectorAll('#'+id);
	if(Utils.isEmpty(obj))
	{
		return false;
	}
	else
	{
		var length = obj.length;
		return obj[length-1];
	}
	
}

/*
 * 设置按钮的显示状态，
 * isView为true时，显示修改按钮
 * isView为false时，显示保存、取消、删除按钮
 */
function setButtonStatus(id,isView)
{
	var save =getLastDOM("goods_save_"+id);    
	var cacel = getLastDOM("goods_cacel_"+id);	
	var del = getLastDOM("goods_del_"+id);	
	var edit = getLastDOM("goods_edit_"+id);	
	
	if(isView)
	{
		save.style.display="none";
		cacel.style.display="none";
		del.style.display="none";
		edit.style.display="block";
	}
	else
	{
		save.style.display="block";
		cacel.style.display="block";
		del.style.display="block";
		edit.style.display="none";
	}
}

var listTable = new Object;

listTable.query = "query";
listTable.filter = new Object;
listTable.url = location.href.lastIndexOf("?") == -1 ? location.href.substring((location.href.lastIndexOf("/")) + 1) : location.href.substring((location.href.lastIndexOf("/")) + 1, location.href.lastIndexOf("?"));
listTable.url += "?is_ajax=1";


/**
 * 添加一个产品
 */
listTable.addProduct = function(obj, act, id)
{
	//检查产品名称是否填写
	var txt_name = getLastDOM("goods_name");
	if(txt_name.value == '')
	{
		txt_name.focus();
		showMsg("tipsmsg",'请输入产品名称');
		return;
	}
	//检查价格是否正确
	var txt = getLastDOM("goods_price");
	
	if (Utils.trim(txt.value).length > 0)
    {
    	var isNumber = Utils.isNumber(Utils.trim(txt.value))
    	
    	if(!isNumber)
		{
    		txt.focus();
    		showMsg("tipsmsg",'请输入正确的产品价格');
    		return;
		}
    }
	else
	{
		txt.focus();
		showMsg("tipsmsg",'请输入价格信息');
		return;
	}
	
	$.ajax({
        type: "post",
        url: "index.php?m=default&c=Category&a=add_goods",     
        data: $("#goods_form").serialize(),   
        datatype: "json",//"xml", "html", "script", "json", "jsonp", "text".
        success: function(data) {
        	addProductResponse(data);
        	 
        },
        error: function(data) {
            alert(data);
        }
    });
	
}

//产品增加成功后，响应函数
function addProductResponse(data)
{
	var obj = JSON.parse(data);
	showMsg("tipsmsg",obj.message);
	 if(obj.error == 0)
	 {		
         firm();
	 }        	      	
	
}

//弹出一个询问框，询问继续添加 产品还是去查看产品 
function firm() {  
    //利用对话框返回的值 （true 或者 false）  
    if (confirm("产品增加成功，继续添加产品？\r\n点击确定继续添加，点击取消则转到产品列表.")) {  
    	listTable.resetProduct();  
    }  
    else {  
    	location.href = 'index.php?m=default&c=Category&a=index'; 
    }  

} 

listTable.resetProduct = function()
{
	var obj_goods_name = getLastDOM("goods_name");
	var obj_goods_price = getLastDOM("goods_price");
	var obj_goods_brief =getLastDOM("goods_brief");
	
	obj_goods_name.value ='';
	obj_goods_price.value ='';
	obj_goods_brief.value ='';
}
/**
 * 保存产品信息
 */
listTable.saveProduct = function(obj, act, id)
{
	/*
	//检查价格是否正确
	var obj_goods_price = getLastDOM("goods_price_"+id);
	var txt = obj_goods_price.childNodes[0];
	if (Utils.trim(txt.value).length > 0)
    {
    	var isNumber = Utils.isNumber(Utils.trim(txt.value))
    	
    	if(!isNumber)
		{
    		txt.focus();
		 showMsg(id,'请输入正确的价格');
		}
    }
	*/
	var obj_goods_form = getLastDOM("goods_form_"+id);

    $.ajax({
        type: "post",
        url: "index.php?m=default&c=Category&a=edit_goods",     
        data: $(obj_goods_form).serialize(),   
        datatype: "json",//"xml", "html", "script", "json", "jsonp", "text".
        success: function(data) {
        	 var obj = JSON.parse(data);
        	 if(obj.error == 0)
    		 {
        		 var goods = JSON.parse(obj.content);
             	listTable.span(goods,true);
                 
                 var id = obj.goods_id;            
                 setButtonStatus(id,true);  
    		 }        	      	
        	showMsg("tipsmsg_"+id,obj.message);
        },
        error: function(data) {
            alert(data);
        }
    });
	
}

function showMsg(id,message)
{
	var obj = getLastDOM(id);
	$(obj).html(message).show(300).delay(3000).hide(300);
	//$("#tipsmsg_").html(message);// 这个是渐渐消失 
	//$("#tipsmsg_"+id).html(message).fadeTo(3000).hide(); //这个是让他显示3秒（实际就是没有改变透明度），然后隐藏 
}

/*
 * 取消修改
 */
listTable.cacelProduct = function(obj, act, id)
{	
	var obj_bak_goods_name = getLastDOM("bak_goods_name_"+id);
	var obj_bak_goods_price = getLastDOM("bak_goods_price_"+id);
	var obj_bak_goods_brief =getLastDOM("bak_goods_brief_"+id);
	var obj_bak_goods_status = getLastDOM("bak_goods_status_"+id);
	
	 var goods = new Object;
	 goods.goods_id = id;
	 goods.goods_name = obj_bak_goods_name.innerHTML;
	 goods.goods_price = obj_bak_goods_price.innerHTML;
	 goods.goods_brief = obj_bak_goods_brief.innerHTML;
	 goods.goods_status = obj_bak_goods_status.innerHTML;
	 
	 this.span(goods, false);
	 setButtonStatus(id,true);
}
/**
 * 设置产品信息为只读
 */
listTable.span = function(goods,isUpdate)
{
	if(typeof(goods) == "undefined")
	{
		return ;
	}
	var id=goods.goods_id;
	var obj_goods_name = getLastDOM("goods_name_"+id);
	var obj_goods_price = getLastDOM("goods_price_"+id);
	var obj_goods_brief = getLastDOM("goods_brief_"+id);
	var obj_goods_status = getLastDOM("goods_status_"+id);
	
  /* 设置内容 */
  obj_goods_name.innerHTML = setCalss(goods.goods_name);
  obj_goods_price.innerHTML = setCalss(goods.goods_price);
  obj_goods_brief.innerHTML = setCalss(goods.goods_brief);
  obj_goods_status.innerHTML = setCalss((goods.goods_status == 1)?"上架销售":"已下架");
  obj_goods_status.setAttribute("value",goods.goods_status);
  
  //如果是更新成功，则同时设置备份信息
  if(isUpdate)
  {
	  var obj_bak_goods_name = getLastDOM("bak_goods_name_"+id);
	 var obj_bak_goods_price = getLastDOM("bak_goods_price_"+id);
	 var obj_bak_goods_brief = getLastDOM("bak_goods_brief_"+id);
	 var obj_bak_goods_status = getLastDOM("bak_goods_status_"+id);
		
	  /* 设置内容 */
	  obj_bak_goods_name.innerHTML = setCalss(goods.goods_name);
	  obj_bak_goods_price.innerHTML = setCalss(goods.goods_price);
	  obj_bak_goods_brief.innerHTML = setCalss(goods.goods_brief);	  
	  obj_bak_goods_status.innerHTML = setCalss((goods.goods_status == 1)?"上架销售":"已下架");
	  obj_bak_goods_status.setAttribute("value",goods.goods_status);  
  }
  
}

function setCalss(str)
{
	return str;
	if(str.indexOf('class="co"') > 0)
	{
		return str;
	}
	str = '<b class="co">'+str+'</b>';
	return str;
}
/**
 * 将产品信息设置为编辑状态
 */
listTable.editProduct = function(obj, act, id)
{
 
	var obj_goods_name = getLastDOM("goods_name_"+id);
	var obj_goods_price = getLastDOM("goods_price_"+id);
	var obj_goods_brief = getLastDOM("goods_brief_"+id);
	var obj_goods_status = getLastDOM("goods_status_"+id);
	
	this.edit(obj_goods_name, id, "goods_name")
	this.edit(obj_goods_price, id, "goods_price")
	this.edit(obj_goods_brief, id, "goods_brief",true)
	this.radio(obj_goods_status, id, "goods_status")
 
	setButtonStatus(id,false);
}


/**
 * 创建一个可编辑区
 */
listTable.edit = function(obj, act, id)
{
	//判断是否是简介
 var isBrief=arguments[3]?arguments[3]:false;
  var tag = obj.firstChild.tagName;

  if (typeof(tag) != "undefined" && tag.toLowerCase() == "input")
  {
    return;
  }

  /* 保存原始的内容 */
  var org = obj.innerHTML;
  var val = Browser.isIE ? obj.innerText : obj.textContent;

  if(isBrief)
  {
	  /* 创建一个文本域 */
	  var txt = document.createElement("textarea");
	  txt.value = (val == 'N/A') ? '' : val; 
	 
  }
  else
  {
	  /* 创建一个输入框 */
	  var txt = document.createElement("INPUT");
	  txt.value = (val == 'N/A') ? '' : val;  
	  txt.style.width = (obj.offsetWidth + 12) + "px" ;
  }
  
  txt.name =id;

  /* 隐藏对象中的内容，并将输入框加入到对象中 */
  obj.innerHTML = "";
  obj.appendChild(txt);
  txt.focus();

  /* 编辑区输入事件处理函数 */
  txt.onkeypress = function(e)
  {
    var evt = Utils.fixEvent(e);
    var obj = Utils.srcElement(e);

    if (evt.keyCode == 13)
    {
      //obj.blur();

      return false;
    }

    if (evt.keyCode == 27)
    {
      obj.parentNode.innerHTML = org;
    }
  }

  /* 编辑区失去焦点的处理函数 */
 if(id == 'goods_price')
 {
	 txt.onblur = function(e)
	  {
	    if (Utils.trim(txt.value).length > 0)
	    {
	    	var isNumber = Utils.isNumber(Utils.trim(txt.value))
	    	
	    	if(!isNumber)
			{
	    		txt.focus();
			 showMsg("tipsmsg_"+act,'请输入正确的价格');
			}
	    }
	    else
	    {
	      obj.innerHTML = org;
	    }
	  }
  }
 
  
}

/**
 * 创建一个单选区
 */
listTable.radio = function(obj, act, id)
{
  var tag = obj.firstChild.tagName;

  if (typeof(tag) != "undefined" && tag.toLowerCase() == "input")
  {
    return;
  }

  /* 保存原始的内容 */
  var org = obj.innerHTML;
  var val = obj.getAttribute("value");

  /* 创建一个radio框 */
  var txt = document.createElement("INPUT");
  txt.type='radio';
  txt.name=id;
  txt.value = '1';
  txt.checked = (val == 1)?true:false;
  //txt.innerHTML="上架";
  
  var label = document.createElement("label");
  label.innerHTML="上架";
  
  /* 创建一个radio框 */
  var txt1 = document.createElement("INPUT");
  txt1.type='radio';
  txt1.name=id;
  txt1.value = '0';
  txt1.checked = (val == 1)?false:true;
  //txt1.innerHTML="<label>下架</label>";
  var label1 = document.createElement("label");
  label1.innerHTML="下架";
  
  /* 隐藏对象中的内容，并将输入框加入到对象中 */
  obj.innerHTML = "";
  obj.appendChild(txt);
  obj.appendChild(label);
  
  obj.appendChild(txt1);
  obj.appendChild(label1);
  //txt.focus();

  /* 编辑区输入事件处理函数 */
 /* txt.onkeypress = function(e)
  {
    var evt = Utils.fixEvent(e);
    var obj = Utils.srcElement(e);

    if (evt.keyCode == 13)
    {
      //obj.blur();

      return false;
    }

    if (evt.keyCode == 27)
    {
      obj.parentNode.innerHTML = org;
    }
  }
*/
  /* 编辑区失去焦点的处理函数 */
 /*
  txt.onblur = function(e)
  {
    if (Utils.trim(txt.value).length > 0)
    {
      res = Ajax.call(listTable.url, "act="+act+"&val=" + encodeURIComponent(Utils.trim(txt.value)) + "&id=" +id, null, "POST", "JSON", false);

      if (res.message)
      {
        alert(res.message);
      }

      if(res.id && (res.act == 'goods_auto' || res.act == 'article_auto'))
      {
          document.getElementById('del'+res.id).innerHTML = "<a href=\""+ thisfile +"?goods_id="+ res.id +"&act=del\" onclick=\"return confirm('"+deleteck+"');\">"+deleteid+"</a>";
      }

      obj.innerHTML = (res.error == 0) ? res.content : org;
    }
    else
    {
      obj.innerHTML = org;
    }
  }
  */
}

listTable.generateLink = function(formId,type)
{
	var form = document.getElementById(formId);
	var goodsIds = this.getSelectedId(form);
	//var data = JSON.stringify(goodsIds);
	$.ajax({
        type: "post",
        url: "index.php?m=default&c=Category&a=generate_link",     
        data: 'goods_ids='+goodsIds,   
        datatype: "json",//"xml", "html", "script", "json", "jsonp", "text".
        success: function(data) {
        	var strMsg = "链接已经生成，请点击确定按钮进行预览，在预览界面点击右上角进行分享。";
        	alert(strMsg); 
        	window.location.href =data;
        },
        error: function(data) {
            alert(data);
        }
    });
}

listTable.checkOutOrder = function(formId,type)
{		
	var form = document.getElementById(formId);
	var goodsIds = this.getSelectedId(form,true);
	var user_id =document.getElementById("user_id").value;
	
	if(goodsIds == '')
	{
		showMsg("tipsmsg",'请选择您要购买的产品');
		return;
	}
	
	var name = $("#consignee").val();
	var mobile = $("#mobile").val();
	var address = $("#address").val();
	if(name =='' )
	{
		showMsg("tipsmsg",'请输入收货人姓名！');
		$("#consignee").focus();
		return;
	}
	if(mobile =='' || !Utils.isTel(mobile))
	{
		showMsg("tipsmsg",'请输入正确的联系方式！');
		$("#mobile").focus();
		return;
	}
	if(address == '')
	{
		showMsg("tipsmsg",'请输入完整的收货地址！');
		$("#address").focus();
		return;
	}
	var host = "index.php?m=default&c=Flow&a=checkoutorder";
	var queryString = '&goods_ids='+goodsIds+"&user_id="+user_id+"&"+$("#orderForm").serialize();
	
	var url = host+queryString;
	window.location.href =url;
	/*$.ajax({
        type: "post",
        url: "index.php?m=default&c=Flow&a=checkoutorder",     
        data: 'goods_ids='+goodsIds+"&user_id="+user_id+"&"+$("#orderForm").serialize(),   
        datatype: "json",//"xml", "html", "script", "json", "jsonp", "text".
        success: function(data) {
        	alert(data); 
        },
        error: function(data) {
            alert(data);
        }
    });
    */
}

/**
 * 切换订单的发货状态
 */
listTable.changeOrderShipStatus = function(obj,orderId,status)
{
	var obj=$(obj);
	var order_obj = obj.parent().parent().parent();
	
	$.ajax({
			type: "post",
	        url: "index.php?m=default&c=user&a=change_order_shipstatus",     
	        data: 'orderId='+orderId+"&shipStatus="+status,   
	        datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".
	        success: function(data) {
	        	//alert(data);
	        	order_obj.html(data);
	        },
	        error: function(data) {
	            alert(data);
	        }
		});
}

/**
 * 切换状态
 */
listTable.toggle = function(obj, act, id)
{
  var val = (obj.src.match(/yes.gif/i)) ? 0 : 1;

  var res = Ajax.call(this.url, "act="+act+"&val=" + val + "&id=" +id, null, "POST", "JSON", false);

  if (res.message)
  {
    alert(res.message);
  }

  if (res.error == 0)
  {
    obj.src = (res.content > 0) ? 'images/yes.gif' : 'images/no.gif';
  }
}

/**
 * 切换排序方式
 */
listTable.sort = function(sort_by, sort_order)
{
  var args = "act="+this.query+"&sort_by="+sort_by+"&sort_order=";

  if (this.filter.sort_by == sort_by)
  {
    args += this.filter.sort_order == "DESC" ? "ASC" : "DESC";
  }
  else
  {
    args += "DESC";
  }

  for (var i in this.filter)
  {
    if (typeof(this.filter[i]) != "function" &&
      i != "sort_order" && i != "sort_by" && !Utils.isEmpty(this.filter[i]))
    {
      args += "&" + i + "=" + this.filter[i];
    }
  }

  this.filter['page_size'] = this.getPageSize();

  Ajax.call(this.url, args, this.listCallback, "POST", "JSON");
}

/**
 * 翻页
 */
listTable.gotoPage = function(page)
{
  if (page != null) this.filter['page'] = page;

  if (this.filter['page'] > this.pageCount) this.filter['page'] = 1;

  this.filter['page_size'] = this.getPageSize();

  this.loadList();
}

/**
 * 载入列表
 */
listTable.loadList = function()
{
  var args = "act="+this.query+"" + this.compileFilter();

  Ajax.call(this.url, args, this.listCallback, "POST", "JSON");
}

/**
 * 删除列表中的一个记录
 */
listTable.remove = function(id, cfm, opt)
{
  if (opt == null)
  {
    opt = "remove";
  }

  if (confirm(cfm))
  {
    var args = "act=" + opt + "&id=" + id + this.compileFilter();

    Ajax.call(this.url, args, this.listCallback, "GET", "JSON");
  }
}

listTable.gotoPageFirst = function()
{
  if (this.filter.page > 1)
  {
    listTable.gotoPage(1);
  }
}

listTable.gotoPagePrev = function()
{
  if (this.filter.page > 1)
  {
    listTable.gotoPage(this.filter.page - 1);
  }
}

listTable.gotoPageNext = function()
{
  if (this.filter.page < listTable.pageCount)
  {
    listTable.gotoPage(parseInt(this.filter.page) + 1);
  }
}

listTable.gotoPageLast = function()
{
  if (this.filter.page < listTable.pageCount)
  {
    listTable.gotoPage(listTable.pageCount);
  }
}

listTable.changePageSize = function(e)
{
    var evt = Utils.fixEvent(e);
    if (evt.keyCode == 13)
    {
        listTable.gotoPage();
        return false;
    };
}

listTable.listCallback = function(result, txt)
{
  if (result.error > 0)
  {
    alert(result.message);
  }
  else
  {
    try
    {
      document.getElementById('listDiv').innerHTML = result.content;

      if (typeof result.filter == "object")
      {
        listTable.filter = result.filter;
      }

      listTable.pageCount = result.page_count;
    }
    catch (e)
    {
      alert(e.message);
    }
  }
}

listTable.selectAll = function(obj, chk)
{
  if (chk == null)
  {
    chk = 'checkboxes';
  }

  var elems = obj.form.getElementsByTagName("INPUT");

  for (var i=0; i < elems.length; i++)
  {
    if (elems[i].name == chk || elems[i].name == chk + "[]")
    {
      elems[i].checked = obj.checked;
    }
  }
}

listTable.getSelectedId = function(obj)
{
	var hasNumber= arguments[1]?arguments[1]:false;
	
	var chk = 'checkboxes';
	var goodsIds = '';　//创建一个数组
	var elems = obj;

	  for (var i=0; i < elems.length; i++)
	  {
	    if (elems[i].name == chk || elems[i].name == chk + "[]")
	    {
	      if(elems[i].checked == true)
    	  {
	    	  if(hasNumber)
    		  {
	    		  var goodsId =elems[i].value;
	    		  var goodsNumber = $("#goods_number_"+goodsId).val();
	    		  goodsIds +=goodsId+'^'+goodsNumber+',';
    		  }
	    	  else
    		  {
	    		  goodsIds +=elems[i].value+',';
    		  }
	    	  
    	  }
	    }
	  }
	  
	  return goodsIds;
}

listTable.compileFilter = function()
{
  var args = '';
  for (var i in this.filter)
  {
    if (typeof(this.filter[i]) != "function" && typeof(this.filter[i]) != "undefined")
    {
      args += "&" + i + "=" + encodeURIComponent(this.filter[i]);
    }
  }

  return args;
}

listTable.getPageSize = function()
{
  var ps = 15;

  pageSize = document.getElementById("pageSize");

  if (pageSize)
  {
    ps = Utils.isInt(pageSize.value) ? pageSize.value : 15;
    document.cookie = "ECSCP[page_size]=" + ps + ";";
  }
}

listTable.addRow = function(checkFunc)
{
  cleanWhitespace(document.getElementById("listDiv"));
  var table = document.getElementById("listDiv").childNodes[0];
  var firstRow = table.rows[0];
  var newRow = table.insertRow(-1);
  newRow.align = "center";
  var items = new Object();
  for(var i=0; i < firstRow.cells.length;i++) {
    var cel = firstRow.cells[i];
    var celName = cel.getAttribute("name");
    var newCel = newRow.insertCell(-1);
    if (!cel.getAttribute("ReadOnly") && cel.getAttribute("Type")=="TextBox")
    {
      items[celName] = document.createElement("input");
      items[celName].type  = "text";
      items[celName].style.width = "50px";
      items[celName].onkeypress = function(e)
      {
        var evt = Utils.fixEvent(e);
        var obj = Utils.srcElement(e);

        if (evt.keyCode == 13)
        {
          listTable.saveFunc();
        }
      }
      newCel.appendChild(items[celName]);
    }
    if (cel.getAttribute("Type") == "Button")
    {
      var saveBtn   = document.createElement("input");
      saveBtn.type  = "image";
      saveBtn.src = "./images/icon_add.gif";
      saveBtn.value = save;
      newCel.appendChild(saveBtn);
      this.saveFunc = function()
      {
        if (checkFunc)
        {
          if (!checkFunc(items))
          {
            return false;
          }
        }
        var str = "act=add";
        for(var key in items)
        {
          if (typeof(items[key]) != "function")
          {
            str += "&" + key + "=" + items[key].value;
          }
        }
        res = Ajax.call(listTable.url, str, null, "POST", "JSON", false);
        if (res.error)
        {
          alert(res.message);
          table.deleteRow(table.rows.length-1);
          items = null;
        }
        else
        {
          document.getElementById("listDiv").innerHTML = res.content;
          if (document.getElementById("listDiv").childNodes[0].rows.length < 6)
          {
             listTable.addRow(checkFunc);
          }
          items = null;
        }
      }
      saveBtn.onclick = this.saveFunc;

      //var delBtn   = document.createElement("input");
      //delBtn.type  = "image";
      //delBtn.src = "./images/no.gif";
      //delBtn.value = cancel;
      //newCel.appendChild(delBtn);
    }
  }

}
