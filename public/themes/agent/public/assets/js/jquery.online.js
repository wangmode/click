(function($){$.fn.Sonline=function(options){var opts=$.extend({},$.fn.Sonline.defualts,options);$.fn.setList(opts);$.fn.Sonline.styleType(opts);if(opts.DefaultsOpen==false){$.fn.Sonline.closes(opts.Position,0);}
$("#SonlineBox > .openTrigger").live("click",function(){$.fn.Sonline.opens(opts);});$("#SonlineBox > .contentBox > .closeTrigger").live("click",function(){$.fn.Sonline.closes(opts.Position,"fast");});if($.browser.msie&&($.browser.version=="6.0")&&!$.support.style||opts.Effect==true){$.fn.Sonline.scrollType();}
else if(opts.Effect==false){$("#SonlineBox").css({position:"fixed"});}}
$.fn.Sonline.defualts={Position:"left",Top:200,Effect:true,Width:170,DefaultsOpen:true,Style:1,Tel:"",Title:"浜掕仈绾垫í-鏈嶅姟鍦ㄧ嚎",FooterText:'',Website:'',Qqlist:""}
$.fn.Sonline.opens=function(opts){var positionType=opts.Position;$("#SonlineBox").css({width:opts.Width+4});if(positionType=="left"){$("#SonlineBox > .contentBox").animate({left:0},"fast");}
else if(positionType=="right"){$("#SonlineBox > .contentBox").animate({right:0},"fast");}
$("#SonlineBox > .openTrigger").hide();}
$.fn.Sonline.closes=function(positionType,speed){$("#SonlineBox > .openTrigger").show();var widthValue=$("#SonlineBox > .openTrigger").width();var allWidth=(-($("#SonlineBox > .contentBox").width())-6);if(positionType=="left"){$("#SonlineBox > .contentBox").animate({left:allWidth},speed);}
else if(positionType=="right"){$("#SonlineBox > .contentBox").animate({right:allWidth},speed);}
$("#SonlineBox").animate({width:widthValue},speed);}
$.fn.Sonline.styleType=function(opts){var typeNum=1;switch(opts.Style)
銆€銆€{case 1:typeNum=41;銆€銆€ break
case 2:typeNum=42;銆€銆€ break
case 3:typeNum=44;銆€銆€ break
case 4:typeNum=45;銆€銆€ break
case 5:typeNum=46;銆€銆€ break
case 6:typeNum=47;銆€銆€ break
銆€銆€ default:typeNum=41;銆€銆€}
return typeNum;}
$.fn.setList=function(opts){$("body").append("<div class='SonlineBox' id='SonlineBox' style='top:-600px; position:absolute;'><div class='openTrigger' style='display:none' title=''></div><div class='contentBox'><div class='closeTrigger' title=''></div><div class='titleBox'><span>"+opts.Title+"</span></div><div class='listBox'></div><div class='tels'>"+opts.FooterText+"</div></div></div>");$("#SonlineBox > .contentBox").width(opts.Width)
if(opts.Qqlist==""){$("#SonlineBox > .contentBox > .listBox").append("<p style='padding:15px'></p>")}
else{var qqListHtml=$.fn.Sonline.splitStr(opts);$("#SonlineBox > .contentBox > .listBox").append(qqListHtml);}
if(opts.Position=="left"){$("#SonlineBox").css({left:0});}
else if(opts.Position=="right"){$("#SonlineBox").css({right:0})}
$("#SonlineBox").css({top:opts.Top,width:opts.Width+4});var allHeights=0;if($("#SonlineBox > .contentBox").height()<$("#SonlineBox > .openTrigger").height()){allHeights=$("#SonlineBox > .openTrigger").height()+4;}else{allHeights=$("#SonlineBox > .contentBox").height()+40;}
$("#SonlineBox").height(allHeights);if(opts.Position=="left"){$("#SonlineBox > .openTrigger").css({left:0});}
else if(opts.Position=="right"){$("#SonlineBox > .openTrigger").css({right:0});}}
$.fn.Sonline.scrollType=function(){$("#SonlineBox").css({position:"absolute"});var topNum=parseInt($("#SonlineBox").css("top")+"");$(window).scroll(function(){var scrollTopNum=$(window).scrollTop();$("#SonlineBox").stop(true,false).delay(200).animate({top:scrollTopNum+topNum},"slow");});}
$.fn.Sonline.splitStr=function(opts){var strs=new Array();var QqlistText=opts.Qqlist;strs=QqlistText.split(",");var alt="";var msn=opts.Website+"/Public/Images/online/msn.gif";var QqHtml=""
for(var i=0;i<strs.length;i++){var subStrs=new Array();var subQqlist=strs[i];subStrs=subQqlist.split("|");var type=parseInt(subStrs[2]);QqHtml+="<div class='QQList'><span>"+subStrs[1]+"锛�</span><div class='ico'>";switch(type){case 2:QqHtml+="<a target='_blank' href='http://amos1.taobao.com/msg.ww?v=2&uid="+subStrs[0]+"&s=1' >";QqHtml+="<img border='0' src='http://amos1.taobao.com/online.ww?v=2&uid="+subStrs[0]+"&s=1' alt='"+alt+"' title='"+alt+"' />";QqHtml+="</a>";break;case 3:QqHtml+="<a target='_blank' href='http://amos.im.alisoft.com/msg.aw?v=2&amp;uid="+subStrs[0]+"&amp;site=cnalichn&amp;s=4'>";QqHtml+="<img alt='"+alt+"' title='"+alt+"' border='0' src='http://amos.im.alisoft.com/online.aw?v=2&amp;uid="+subStrs[0]+"&amp;site=cnalichn&amp;s=4' />";QqHtml+="</a>";break;case 4:QqHtml+="<a target=blank href='msnim:chat?contact="+subStrs[0]+"&Site="+subStrs[0]+"'>";QqHtml+="<img src='"+msn+"' alt='"+alt+"' title='"+alt+"'/>";QqHtml+="</a>";break;case 5:QqHtml+="<a target=blank href='callto://"+subStrs[0]+"'>";QqHtml+="<img border='0' src='http://mystatus.skype.com/smallclassic/"+subStrs[0]+"' alt='"+alt+"' title='"+alt+"'/>";QqHtml+="</a>";break;case 1:default:QqHtml+="<a target='_blank' href='http://wpa.qq.com/msgrd?v=3&uin="+subStrs[0]+"&site=qq&menu=yes'>";QqHtml+="<img border='0' src='http://wpa.qq.com/pa?p=2:"+subStrs[0]+":"+$.fn.Sonline.styleType(opts)+" &amp;r=0.22914223582483828' alt='"+alt+"'  title='"+alt+"'>";QqHtml+="</a>";}
QqHtml+="</div><div style='clear:both;'></div></div>";}
return QqHtml;}})(jQuery);