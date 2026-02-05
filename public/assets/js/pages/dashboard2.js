//[Dashboard Javascript]

//Project:	Coup Admin - Responsive Admin Template
//Primary use:   Used only for the main dashboard (index.html)


//Initialize nice select
$(function () {
      var owlslider = jQuery("div.owl-carousel");
        owlslider.each(function () {
        var $this = $(this),
              $items = ($this.data('items')) ? $this.data('items') : 1,
              $loop = ($this.attr('data-loop')) ? $this.data('loop') : true,
              $navdots = ($this.data('nav-dots')) ? $this.data('nav-dots') : false,
              $navarrow = ($this.data('nav-arrow')) ? $this.data('nav-arrow') : false,
              $autoplay = ($this.attr('data-autoplay')) ? $this.data('autoplay') : true,
              $autohgt = ($this.data('autoheight')) ? $this.data('autoheight') : false,
              $autowdt = ($this.data('autoWidth')) ? $this.data('autoWidth') : true,
              $space = ($this.attr('data-space')) ? $this.data('space') : 20;
         
              $(this).owlCarousel({
                  loop: $loop,
                  items: $items,
                  responsive: {
                    0:{items: $this.data('xx-items') ? $this.data('xx-items') : 1},
                    480:{items: $this.data('xs-items') ? $this.data('xs-items') : 1},
                    768:{items: $this.data('sm-items') ? $this.data('sm-items') : 2},
                    980:{items: $this.data('md-items') ? $this.data('md-items') : 3},
                    1440:{items: $this.data('xl-items') ? $this.data('xl-items') : 4},
                    1441:{items: $items}
                  },
                  dots: $navdots,
                  autoHeight:$autohgt,
                  margin:$space,
                  nav: $navarrow,
                  navText:["<i class='fa fa-angle-left fa-2x'></i>","<i class='fa fa-angle-right fa-2x'></i>"],
                  autoplay: $autoplay,
                  autoplayHoverPause: true,
              });

       }); 
 });


$(document).ready(function () {
  $('#example-trn').DataTable(
  	{
			"paging": true,
			"autoWidth": true,
			"pageLength": 5,
			"lengthMenu": [[5, 10, 20, -1], [5, 10, 20, 'All']],
		}
	); 
});


$('.review-box').slimScroll({
  height: 'auto'
});


$(function () {

  'use strict';

      var options = {
          series: [{
          name: 'series2',
          data: [11, 32, 45, 32, 34, 52, 41]
        }],
          chart: {
          height: 250,
          type: 'area',
          toolbar: {
          	show: false,
          },
        },
        dataLabels: {
          enabled: false
        },
        colors: ['#FF9800'],
        stroke: {
          curve: 'smooth',
          width: 2,
        },
        xaxis: {
          type: 'datetime',
          categories: ["2025-08-19T00:00:00.000Z", "2025-08-19T01:30:00.000Z", "2025-08-19T02:30:00.000Z", "2025-08-19T03:30:00.000Z", "2025-08-19T04:30:00.000Z", "2025-08-19T05:30:00.000Z", "2025-08-19T06:30:00.000Z"]
        },
        tooltip: {
          x: {
            format: 'dd/MM/yy HH:mm'
          },
        },
        };

        var chart = new ApexCharts(document.querySelector("#chart-revenue"), options);
        chart.render();





      var options = {
        series: [84],
        chart: {
        height: 248,
        type: 'radialBar',
      },
      colors: ['#1b84ff'],
      plotOptions: {
        radialBar: {
          hollow: {
            size: '60%',
          }
        },
      },
      labels: ['Delivery Rate'],
      };

      var chart = new ApexCharts(document.querySelector("#delivery-chart"), options);
      chart.render();


      var options = {
        series: [45],
        chart: {
        height: 250,
        type: 'radialBar',
      },
      colors: ['#FF9800'],
      plotOptions: {
        radialBar: {
          hollow: {
            size: '60%',
          }
        },
      },
      labels: ['Return Rate'],
      };

      var chart = new ApexCharts(document.querySelector("#return-chart"), options);
      chart.render();


      var options = {
        series: [25],
        chart: {
        height: 250,
        type: 'radialBar',
      },
      colors: ['#f8285a'],
      plotOptions: {
        radialBar: {
          hollow: {
            size: '60%',
          }
        },
      },
      labels: ['Cancel Rate'],
      };

      var chart = new ApexCharts(document.querySelector("#cancel-chart"), options);
      chart.render();










	var options = {
          series: [{
          name: 'Total Spend',
          data: [23, 31, 40, 101, 40, 36, 32, 23, 14, 18, 15, 12, 13, 11, 40, 10, 40, 26, 12, 23, 16, 8]
        }],
          chart: {
          height: 300,
          type: 'bar',
		  zoom: {
            enabled: false
          }
        },
        plotOptions: {
          bar: {
            borderRadius: 100,
			  columnWidth: '30%',
			  endingShape: 'rounded',
          }
        },
        dataLabels: {
          enabled: false,
          formatter: function (val) {
            return  "$" + val + "k";
          },
          offsetY: -20,
          style: {
            fontSize: '12px',
            colors: ["#3699ff"]
          }
        },
        
        xaxis: {
			type: 'datetime',
          categories: ['01/01/2021 GMT', '01/02/2021 GMT', '01/03/2021 GMT', '01/04/2021 GMT', '01/05/2021 GMT', '01/06/2021 GMT', '01/07/2021 GMT', '01/08/2021 GMT', '01/09/2021 GMT', '01/10/2021 GMT', '01/11/2021 GMT', '01/12/2021 GMT', '01/13/2021 GMT', '01/14/2021 GMT', '01/15/2021 GMT', '01/16/2021 GMT', '01/17/2021 GMT', '01/18/2021 GMT', '01/19/2021 GMT', '01/20/2021 GMT', '01/21/2021 GMT', '01/22/2021 GMT'],          
          position: 'top',
          axisBorder: {
            show: false
          },
          axisTicks: {
            show: false
          },
          crosshairs: {
            fill: {
              type: 'gradient',
              gradient: {
                colorFrom: '#D8E3F0',
                colorTo: '#BED1E6',
                stops: [0, 100],
                opacityFrom: 0.4,
                opacityTo: 0.5,
              }
            }
          },
          tooltip: {
            enabled: true,
          }
        },
        yaxis: {
          axisBorder: {
            show: false
          },
          axisTicks: {
            show: false,
          },
          labels: {
            show: false,
            formatter: function (val) {
              return val + "%";
            }
          }
        
        },
        };

        var chart = new ApexCharts(document.querySelector("#spend_trend"), options);
        chart.render();

	
}); // End of use strict




am5.ready(function() {


// Create root element
// https://www.amcharts.com/docs/v5/getting-started/#Root_element
var root = am5.Root.new("sale-container");


// Set themes
// https://www.amcharts.com/docs/v5/concepts/themes/
root.setThemes([
  am5themes_Animated.new(root)
]);


// Create chart
// https://www.amcharts.com/docs/v5/charts/xy-chart/
var chart = root.container.children.push(am5xy.XYChart.new(root, {
  panX: false,
  panY: false,
  wheelX: "panX",
  wheelY: "zoomX",
  paddingLeft: 0,
  layout: root.verticalLayout
}));


// Data
var colors = chart.get("colors");

var data = [ {
  country: "2018",
  visits: 750,
  columnSettings: { fill: colors.next() }
}, {
  country: "2019",
  visits: 204,
  columnSettings: { fill: colors.next() }
}, {
  country: "2020",
  visits: 350,
  columnSettings: { fill: colors.next() }
}, {
  country: "2021",
  visits: 165,
  columnSettings: { fill: colors.next() }
}, {
  country: "2022",
  visits: 300,
  columnSettings: { fill: colors.next() }
}, {
  country: "2023",
  visits: 500,
  columnSettings: { fill: colors.next() }
}, {
  country: "2024",
  visits: 800,
  columnSettings: { fill: colors.next() }
}];


// Create axes
// https://www.amcharts.com/docs/v5/charts/xy-chart/axes/
var xRenderer = am5xy.AxisRendererX.new(root, {
  minGridDistance: 30,
  minorGridEnabled: true
})

var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
  categoryField: "country",
  renderer: xRenderer,
  bullet: function (root, axis, dataItem) {
    return am5xy.AxisBullet.new(root, {
      location: 0.5,
      sprite: am5.Picture.new(root, {
        width: 24,
        height: 24,
        centerY: am5.p50,
        centerX: am5.p50,
        src: dataItem.dataContext.icon
      })
    });
  }
}));

xRenderer.grid.template.setAll({
  location: 1
})

xRenderer.labels.template.setAll({
  paddingTop: 20
});

xAxis.data.setAll(data);

var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
  renderer: am5xy.AxisRendererY.new(root, {
    strokeOpacity: 0.1
  })
}));


// Add series
// https://www.amcharts.com/docs/v5/charts/xy-chart/series/
var series = chart.series.push(am5xy.ColumnSeries.new(root, {
  xAxis: xAxis,
  yAxis: yAxis,
  valueYField: "visits",
  categoryXField: "country"
}));

series.columns.template.setAll({
  tooltipText: "{categoryX}: {valueY}",
  tooltipY: 0,
  strokeOpacity: 0,
  templateField: "columnSettings"
});

series.data.setAll(data);


// Make stuff animate on load
// https://www.amcharts.com/docs/v5/concepts/animations/
series.appear();
chart.appear(1000, 100);

}); // end am5.ready()