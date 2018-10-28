$(function()
{
    // 颜色选择器
    var $input_tag = $('input[name="title"]');
    var $title_color = $('input[name="title_color"]');
    $(".colorpicker-submit").colorpicker(
    {
        fillcolor:true,
        success:function(o, color)
        {
            $input_tag.css('color', color);
            $title_color.val(color);
        },
        reset:function(o)
        {
            $input_tag.css('color', '');
            $title_color.val('');
        }
    });

    /* 搜索切换 */
    var $more_where = $('.more-where');
    $more_submit = $('.more-submit');
    $more_submit.find('input[name="is_more"]').change(function()
    {
        if($more_submit.find('i').hasClass('am-icon-angle-down'))
        {
            $more_submit.find('i').removeClass('am-icon-angle-down');
            $more_submit.find('i').addClass('am-icon-angle-up');
        } else {
            $more_submit.find('i').addClass('am-icon-angle-down');
            $more_submit.find('i').removeClass('am-icon-angle-up');
        }
    
        if($more_submit.find('input[name="is_more"]:checked').val() == undefined)
        {
            $more_where.addClass('none');
        } else {
            $more_where.removeClass('none');
        }
    });

    // 日期选择
    var $time_start = $('#time_start');
    var $time_end = $('#time_end');
    var nowTemp = new Date();
    var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

    var checkin = $time_start.datepicker({}).on('changeDate.datepicker.amui', function(ev) {
        var newDate = new Date(ev.date)
        newDate = (ev.date.valueOf() > checkout.date.valueOf() || ev.date.valueOf() == checkout.date.valueOf()) ? newDate.setDate(newDate.getDate() + 1) : checkout.date.valueOf();
        checkout.setValue(newDate);
        
        checkin.close();
        $time_end[0].blur();
    }).data('amui.datepicker');
    var checkout = $time_end.datepicker({
        onRender:function(date) {
            return date.valueOf() <= checkin.date.valueOf() ? 'am-disabled' : '';
        }
    }).on('changeDate.datepicker.amui', function(ev) {
      checkout.close();
    }).data('amui.datepicker');



    $(document).on('click', '.alloperate', function ()
    {
        var id = check_chooseids();
        if (id.length === 0) {
            alert('请先勾选要操作的内容！');
            return;
        }
        var text = '你确定要将 ' + id + '状态批量改为';
        var status = $(this).attr('value');
        console.log(id, status);
        if (status == 1) {
            text += '已处理';
        } else if (status == 2) {
            text += '已支付';
        } else if (status == 3) {
            text += '已完成';
        } else if (status == 4) {
            text += '已取消';
        } else if (status == 5) {
            text += '已关闭';
        } else if (status == 6) {
            text += '配送中';
        }
        text += '?';
        $('#alloperate_text').html(text);
        $('#confirm-success').modal({
            relatedTarget: this,
            onConfirm: function (options)
            {
                $.ajax({
                    url: "/admin.php?m=Admin&c=Order&a=modify",
                    type: "post",
                    dataType: 'json',
                    data: {id: id, status: status},
                    success: function (result) {
                        if (result.code == 0) {
                            alert(result.msg);
                            $("input[name='list-choose']:checked").each(function (index) {
                                $(this).attr('checked', false);
                            })
                            window.location.reload();

                        } else {
                            alert(result.msg);
                        }
                    },
                    error: function (msg) {
                        console.log(msg);
                        alert(msg);
                    }

                })

            },
            onCancel: function () {}
});
    }
    );

    function check_chooseids() {
        var ids = [];
        $("input[name='list-choose']:checked").each(function (index) {
            ids[index] = $(this).attr('value');
        })
        return ids;
    }
    $("input[name='list-choose-all']").click(function () {
        $("input[name='list-choose']").each(function (index) {
            $(this).attr('checked', true);
        })
    })

});
