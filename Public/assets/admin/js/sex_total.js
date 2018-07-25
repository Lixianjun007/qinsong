var sex_total = echarts.init(document.getElementById('sex'), 'macarons');
$.get('http://holy.canon4ever.com/admin/Echarts/sex_total').done(function (data) {
    console.log(data);
    var option = {
        title: {
            text: '来自长乐美食城网站统计',
            subtext: '真实数据',
            x: 'center'
        },
        tooltip: {
            trigger: 'item',
            formatter: "{a} <br/>{b} : {c} ({d}%)"
        },
        legend: {
            orient: 'vertical',
            left: 'left',
            data: ['男', '女']
        },
        series: [
            {
                name: '长乐美食城',
                type: 'pie',
                radius: '55%',
                center: ['50%', '60%'],
                data: data,
                itemStyle: {
                    emphasis: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            }
        ]
    };
    sex_total.setOption(option);
});
