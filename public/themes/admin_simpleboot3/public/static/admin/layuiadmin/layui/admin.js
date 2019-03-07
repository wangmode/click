$(function () {
    //加载弹出层
    layui.use(['form','element'],
    function() {
        layer = layui.layer;
        element = layui.element;
    });
    tableCheck = {
        init:function  () {
            $(".layui-form-checkbox").click(function(event) {
                if($(this).hasClass('layui-form-checked')){
                    $(this).removeClass('layui-form-checked');
                    if($(this).hasClass('header')){
                        $(".layui-form-checkbox").removeClass('layui-form-checked');
                    }
                }else{
                    $(this).addClass('layui-form-checked');
                    if($(this).hasClass('header')){
                        $(".layui-form-checkbox").addClass('layui-form-checked');
                    }
                }
                
            });
        },
        getData:function  () {
            var obj = $(".layui-form-checked").not('.header');
            var arr=[];
            obj.each(function(index, el) {
                arr.push(obj.eq(index).attr('data-id'));
            });
            return arr;
        }
    };

    //开启表格多选
    tableCheck.init();


    
});

/*弹出层*/
/*
    参数解释：
    title   标题
    url     请求的url
    id      需要操作的数据id
    w       弹出层宽度（缺省调默认值）
    h       弹出层高度（缺省调默认值）
*/
function window_open(title,url,w,h){
    if (title == null || title == '') {
        title=false;
    };
    if (url == null || url == '') {
        url="404.html";
    };
    if (w == null || w == '') {
        w=($(window).width()*0.9);
    };
    if (h == null || h == '') {
        h=($(window).height() - 50);
    };
    layer.open({
        type: 2,
        area: [w+'px', h +'px'],
        fix: false, //不固定
        maxmin: true,
        shadeClose: true,
        shade:0.4,
        title: title,
        content: url
    });
}

/*关闭弹出框口*/
function x_admin_close(){
    var index = parent.layer.getFrameIndex(window.name);
    parent.layer.close(index);
}


function to_disable(id,url){
    var is_have = $('#button'+id).hasClass('layui-btn-danger');
    var status = 2;
    if(is_have===false){
        status = 1;
        $('#button'+id).removeClass("layui-btn-disabled");
        $('#button'+id).addClass("layui-btn-danger");
    }else{
        $('#button'+id).addClass("layui-btn-disabled");
        $('#button'+id).removeClass("layui-btn-danger");
    }
    $.ajax({
        url:url,
        data:{status:status,id:id},
        type:'post',
        dataType:'json',
        success:function(res){
            if(res.status !== 1){
                return false;
            }else{
                return false;
            }
        },
    });
}


function yzt_to_disable(id,url){
    $.ajax({
        url:url,
        data:{id:id},
        type:'post',
        dataType:'json',
        success:function(res){
            if(res.status !== 1){
                layer.msg(res.msg, {icon: 5});
                return false;
            }else{
                if(res.customer_status === 1){
                    $('#button'+id).removeClass("layui-btn-disabled");
                    $('#button'+id).addClass("layui-btn-danger");
                }else{
                    $('#button'+id).addClass("layui-btn-disabled");
                    $('#button'+id).removeClass("layui-btn-danger");
                }
                layer.msg(res.msg, {icon: 6});
                return false;
            }
        },
    });
}


