//[Dashboard Javascript]

//Project:	Coup Admin - Responsive Admin Template
//Primary use:   Used only for the main dashboard (index.html)

$(function () {

  'use strict';

  const options = {
    chart: {
      type: 'heatmap',
      height: 313,
      toolbar: { show: false }
    },
    dataLabels: {
      enabled: false
    },
    colors: ['#e6f0ff', '#99c2ff', '#4da6ff', '#0f539e'],
    plotOptions: {
      heatmap: {
        shadeIntensity: 0.5,
        colorScale: {
          ranges: [
            {
              from: 0,
              to: 500,
              color: '#e6f0ff',
              opacity: 0.3,
              name: '<500'
            },
            {
              from: 501,
              to: 1000,
              color: '#99c2ff',
              name: '1,000'
            },
            {
              from: 1001,
              to: 2000,
              color: '#4da6ff',
              name: '2,000'
            },
            {
              from: 2001,
              to: 3000,
              color: '#0f539e',
              name: '3,000'
            }
          ]
        }
      }
    },
    xaxis: {
      type: 'category',
      categories: ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri']
    },
    yaxis: {
      categories: ['9 am', '10 am', '11 am', '12 pm', '1 pm', '2 pm', '3 pm'],
      reversed: true
    },
    series: [
      {
        name: "9 am",
        data: [0, 0, 0, 1000, 0, 0, 0]
      },
      {
        name: "10 am",
        data: [0, 0, 0, 2000, 0, 0, 0]
      },
      {
        name: "11 am",
        data: [0, 0, 1000, 3000, 1000, 0, 0]
      },
      {
        name: "12 pm",
        data: [0, 0, 2000, 3000, 2000, 1000, 0]
      },
      {
        name: "1 pm",
        data: [1000, 2000, 3000, 3000, 3000, 2000, 1000]
      },
      {
        name: "2 pm",
        data: [0, 0, 2000, 3000, 2000, 0, 0]
      },
      {
        name: "3 pm",
        data: [0, 0, 0, 1000, 0, 0, 0]
      },
    ]
  };

  const chart = new ApexCharts(document.querySelector("#heatmap"), options);
  chart.render();
  


  
}); // End of use strict





am5.ready(function() {


// Create root element
// https://www.amcharts.com/docs/v5/getting-started/#Root_element
var root = am5.Root.new("chart-container");


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

var data = [{
  country: "Jan",
  visits: 725,
  columnSettings: { fill: colors.next() }
}, {
  country: "Fab",
  visits: 850,
  columnSettings: { fill: colors.next() }
}, {
  country: "Mar",
  visits: 602,
  columnSettings: { fill: colors.next() }
}, {
  country: "Apr",
  visits: 509,
  columnSettings: { fill: colors.next() }
}, {
  country: "May",
  visits: 322,
  columnSettings: { fill: colors.next() }
}, {
  country: "Jun",
  visits: 750,
  columnSettings: { fill: colors.next() }
}, {
  country: "July",
  visits: 204,
  columnSettings: { fill: colors.next() }
}, {
  country: "Aug",
  visits: 350,
  columnSettings: { fill: colors.next() }
}, {
  country: "Set",
  visits: 165,
  columnSettings: { fill: colors.next() }
}, {
  country: "Oct",
  visits: 300,
  columnSettings: { fill: colors.next() }
}, {
  country: "Nov",
  visits: 500,
  columnSettings: { fill: colors.next() }
}, {
  country: "Dec",
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




// -----------------------------------------------------------------------------------------------------------



am5.ready(function() {

// Create root element
// https://www.amcharts.com/docs/v5/getting-started/#Root_element
var root = am5.Root.new("chart-selling");

// Set themes
// https://www.amcharts.com/docs/v5/concepts/themes/
root.setThemes([
  am5themes_Animated.new(root)
]);

var data = [{
  name: "Ator..",
  steps: 4568,
   icon: "https://www.amcharts.com/wp-content/uploads/flags/united-states.svg",
  pictureSettings: {
    src: "//www.amcharts.com/lib/3/images/0.gif"
  }
}, {
  name: "Omepr..",
  steps: 3578,
  pictureSettings: {
    src: "data:../../../images/gallery/Med2.jpg"
  }
}, {
  name: "Metfor..",
  steps: 2546,
  pictureSettings: {
    src: "data:../../../images/gallery/Med3.jpg"
  }
}, {
  name: "Phoebe",
  steps: 1878,
  pictureSettings: {
    src: "data:../../../images/gallery/Med4.jpg"
  }
}];

// Create chart
// https://www.amcharts.com/docs/v5/charts/xy-chart/
var chart = root.container.children.push(
  am5xy.XYChart.new(root, {
    panX: false,
    panY: false,
    wheelX: "none",
    wheelY: "none",
    paddingBottom: 50,
    paddingTop: 40,
    paddingLeft:0,
    paddingRight:0
  })
);

// Create axes
// https://www.amcharts.com/docs/v5/charts/xy-chart/axes/

var xRenderer = am5xy.AxisRendererX.new(root, {
  minorGridEnabled:true,
  minGridDistance:50
});
xRenderer.grid.template.set("visible", false);

var xAxis = chart.xAxes.push(
  am5xy.CategoryAxis.new(root, {
    paddingTop:40,
    categoryField: "name",
    renderer: xRenderer
  })
);


var yRenderer = am5xy.AxisRendererY.new(root, {});
yRenderer.grid.template.set("strokeDasharray", [3]);

var yAxis = chart.yAxes.push(
  am5xy.ValueAxis.new(root, {
    min: 0,
    renderer: yRenderer
  })
);

// Add series
// https://www.amcharts.com/docs/v5/charts/xy-chart/series/
var series = chart.series.push(
  am5xy.ColumnSeries.new(root, {
    name: "Income",
    xAxis: xAxis,
    yAxis: yAxis,
    valueYField: "steps",
    categoryXField: "name",
    sequencedInterpolation: true,
    calculateAggregates: true,
    maskBullets: false,
    tooltip: am5.Tooltip.new(root, {
      dy: -30,
      pointerOrientation: "vertical",
      labelText: "{valueY}"
    })
  })
);

series.columns.template.setAll({
  strokeOpacity: 0,
  cornerRadiusBR: 10,
  cornerRadiusTR: 10,
  cornerRadiusBL: 10,
  cornerRadiusTL: 10,
  maxWidth: 50,
  fillOpacity: 0.8
});

var currentlyHovered;

series.columns.template.events.on("pointerover", function (e) {
  handleHover(e.target.dataItem);
});

series.columns.template.events.on("pointerout", function (e) {
  handleOut();
});

function handleHover(dataItem) {
  if (dataItem && currentlyHovered != dataItem) {
    handleOut();
    currentlyHovered = dataItem;
    var bullet = dataItem.bullets[0];
    bullet.animate({
      key: "locationY",
      to: 1,
      duration: 600,
      easing: am5.ease.out(am5.ease.cubic)
    });
  }
}

function handleOut() {
  if (currentlyHovered) {
    var bullet = currentlyHovered.bullets[0];
    bullet.animate({
      key: "locationY",
      to: 0,
      duration: 600,
      easing: am5.ease.out(am5.ease.cubic)
    });
  }
}

var circleTemplate = am5.Template.new({});

series.bullets.push(function (root, series, dataItem) {
  var bulletContainer = am5.Container.new(root, {});
  var circle = bulletContainer.children.push(
    am5.Circle.new(
      root,
      {
        radius: 25
      },
      circleTemplate
    )
  );

  var maskCircle = bulletContainer.children.push(
    am5.Circle.new(root, { radius: 25 })
  );

  // only containers can be masked, so we add image to another container
  var imageContainer = bulletContainer.children.push(
    am5.Container.new(root, {
      mask: maskCircle
    })
  );

  var image = imageContainer.children.push(
    am5.Picture.new(root, {
      templateField: "pictureSettings",
      centerX: am5.p10,
      centerY: am5.p10,
      width: 10,
      height: 10,
    })
  );

  return am5.Bullet.new(root, {
    locationY: 0,
    sprite: bulletContainer
  });
});

// heatrule
series.set("heatRules", [
  {
    dataField: "valueY",
    min: am5.color(0xe5dc36),
    max: am5.color(0x5faa46),
    target: series.columns.template,
    key: "fill"
  },
  {
    dataField: "valueY",
    min: am5.color(0xe5dc36),
    max: am5.color(0x5faa46),
    target: circleTemplate,
    key: "fill"
  }
]);

series.data.setAll(data);
xAxis.data.setAll(data);

var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {}));
cursor.lineX.set("visible", false);
cursor.lineY.set("visible", false);

cursor.events.on("cursormoved", function () {
  var dataItem = series.get("tooltip").dataItem;
  if (dataItem) {
    handleHover(dataItem);
  } else {
    handleOut();
  }
});

// Make stuff animate on load
// https://www.amcharts.com/docs/v5/concepts/animations/
series.appear();
chart.appear(1000, 50);

}); // end am5.ready()






















