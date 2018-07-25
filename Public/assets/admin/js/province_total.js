var province_total = echarts.init(document.getElementById('province'), 'macarons');

province_total.showLoading();
$.get('http://holy.canon4ever.com/admin/Echarts/province_total').done(function (data) {

    province_total.hideLoading();

    function randomData() {
        return Math.round(Math.random() * 1000);
    }

    var option = {
        title: {
            text: '商城会员省份及人数统计',
            subtext: '真实数据',
            left: 'center'
        },
        tooltip: {
            trigger: 'item'
        },
        legend: {
            orient: 'vertical',
            left: 'left',
            data: ['省份', '人数']
        },
        toolbox: {
            show: true,
            orient: 'vertical',
            left: 'right',
            top: 'center',
            feature: {
                dataView: {readOnly: false},
                restore: {},
                saveAsImage: {}
            }
        },
        series: [
            {
                name: '人数',
                type: 'map',
                mapType: 'china',
                roam: false,
                label: {
                    normal: {
                        show: true
                    },
                    emphasis: {
                        show: true
                    }
                },
                data: data
            }
        ]
    };

    // 使用刚指定的配置项和数据显示图表。
    province_total.setOption(option);
});