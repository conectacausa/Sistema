//[Dashboard Javascript]

//Project:	Coup Admin - Responsive Admin Template
//Primary use:   Used only for the main dashboard (index.html)


$(document).ready(function () {
  $('#example').DataTable(
    {
      "paging": true,
      "autoWidth": true,
      "pageLength": 5,
      "lengthMenu": [[5, 10, 20, -1], [5, 10, 20, 'All']],
    }
  ); 
});

$(document).ready(function () {
  $('#example-2').DataTable(
    {
      "paging": true,
      "autoWidth": true,
      "pageLength": 10,
      "lengthMenu": [[5, 10, 20, -1], [5, 10, 20, 'All']],
    }
  ); 
}); 




var options = {
  series: [{
  name: 'series1',
  data: [ 20,15,25,25,30,27,33,30,35,32,25,31,20,25,30,27,33,30,20]
  }],
  chart: {
  height: 176,
  type: 'area',
  toolbar: {
    show: false,
    },
    offsetY: 0,
},
colors: ["#ffa800"],
fill: {
  colors: ["#ffa800" ],
  type: "gradient",
  gradient: {
    shade: "light",
    type: "vertical",
    shadeIntensity: 0.4,
    inverseColors: false,
    opacityFrom: 0.7,
    opacityTo: 0.1,
    stops: [0,85,90],
  },
},
dataLabels: {
  enabled: false
},
stroke: {
width: [2],
  curve: 'smooth'
},
grid: {
  show: false,
  padding: {
    left: -10,
    top: -25,
    right: -0,
  },
},
markers: {
    size: 0,
},
xaxis: {
  type: 'datetime',
  categories: ["2018-09-19T00:00:00.000Z", "2018-09-19T01:30:00.000Z", "2018-09-19T02:30:00.000Z", "2018-09-19T03:30:00.000Z", "2018-09-19T04:30:00.000Z", "2018-09-19T05:30:00.000Z", "2018-09-19T06:30:00.000Z"]
},
legend: {
    show: false,
},
tooltip: {
  x: {
    format: 'dd/MM/yy HH:mm'
  },
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
  },
},
xaxis: {
  axisBorder: {
    show: false
  },
  axisTicks: {
    show: false,
  },
  labels: {
    show: false,
  },
},
};

var chart = new ApexCharts(document.querySelector("#chart-widget1"), options);
chart.render();



// ----------Shifts Overview-----//
var option = {
  labels: ["Theralief", "Nullacin", "Antibiotics"],
  series: [45, 35, 20],
  chart: {
      type: "donut",
      height: 333,
      dropShadow: {
        enabled: true,
        enabledOnSeries: undefined,
        top: 0,
        left: 0,
        blur: 0.1,
        color: '#000',
        opacity: 0.2
    },
  },
  dataLabels: {
      enabled: false,
  },
  legend: {
      show: true,
      position: 'bottom',
		  horizontalAlign: 'center',
  },
  stroke: {
      width: 5,
  },
  plotOptions: {
      pie: {
          expandOnClick: false,
          donut: {
              size: "60%",
              labels: {
                  show: true,
                  name: {
                      offsetY: -0,
                      color: '#00000',
                  },
                  total: {
                      show: true,
                      fontSize: "20px",
                      fontWeight: 600,
                      color:"#000000",
                      label: "$850",
                      formatter: () => "Revenue",
                  },
              },
          },
      },
  },
  states: {
      normal: {
          filter: {
              type: "none",
          },
      },
      hover: {
          filter: {
              type: "none",
          },
      },
      active: {
          allowMultipleDataPointsSelection: false,
          filter: {
              type: "none",
          },
      },
  },
  colors: ["#97D8D0" ,"#F1B5B9", "#D7F1B5"],
};

var chart = new ApexCharts(
  document.querySelector("#balance-overview"),
  option
);
chart.render();

	
	
	  var options = {
      series: [{
      name: 'Sales',
      data: [76, 85, 101, 98, 87, 105, 91, 114, 94, 50, 120, 35]
    }],
      chart: {
      type: 'bar',
      height: 303,
      stacked: false,  
    },
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: '60%',
        endingShape: 'rounded'
      },
    },
    legend: {
        show: false,
    },
    dataLabels: {
      enabled: false
    },
    stroke: {
      show: true,
      width: 1,
      colors: ['transparent']
    },
    xaxis: {
      categories: ['Jan','Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct','Nov', 'Dec'],
    },
    yaxis: {
      title: {
        text: ''
      }
    },
    colors: ['#fedb96', '#fc696a'],
    fill: {
      opacity: 1
    },
    tooltip: {
      y: {
        formatter: function (val) {
          return "" + val + ""
        }
      }
    }
    };

    var chart = new ApexCharts(document.querySelector("#chart-Overall"), options);
    chart.render();



	


