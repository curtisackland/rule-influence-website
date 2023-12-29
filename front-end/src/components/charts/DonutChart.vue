<template>
    <div ref="chartContainer"></div>
  </template>
  
  <script>
  import * as d3 from 'd3';
  
  export default {
    name: 'MyChart',
    props: {
      data: {
        type: Array,
        required: true,
      },
    },
    mounted() {
      this.createChart();
    },
    methods: {
      createChart() {
        const width = 500; // set your desired width
        const height = Math.min(width, 500);
        const radius = Math.min(width, height) / 2;
  
        const arc = d3.arc()
          .innerRadius(radius * 0.67)
          .outerRadius(radius - 1);
  
        const pie = d3.pie()
          .padAngle(1 / radius)
          .sort(null)
          .value(d => +d.n_frdocs); // Convert n_frdocs to a number
  
        const color = d3.scaleOrdinal()
          .domain(this.data.map(d => d.org_name))
          .range(d3.quantize(t => d3.interpolateSpectral(t * 0.9 + 0.1), this.data.length).reverse());
  
        const svg = d3.select(this.$refs.chartContainer)
          .append("svg") 
          .attr("width", width)
          .attr("height", height)
          .attr("viewBox", [-width / 2, -height / 2, width, height])
          .attr("style", "max-width: 100%; height: auto;");
  
        svg.append("g")
          .selectAll()
          .data(pie(this.data))
          .join("path")
          .attr("fill", d => color(d.data.org_name))
          .attr("d", arc)
          .append("title")
          .text(d => `${d.data.org_name}: ${d.data.n_frdocs}`);
  
        svg.append("g")
          .attr("font-family", "sans-serif")
          .attr("font-size", 12)
          .attr("text-anchor", "middle")
          .selectAll()
          .data(pie(this.data))
          .join("text")
          .attr("transform", d => {
            const [x, y] = arc.centroid(d);
            const angle = Math.atan2(y, x);
            const labelRadius = radius * 0.8; // You can adjust the label radius
            const labelX = labelRadius * Math.cos(angle);
            const labelY = labelRadius * Math.sin(angle);
            return `translate(${labelX}, ${labelY})`;
          }) 
          .call(text => text.append("tspan")
            .attr("y", "-0.4em")
            .attr("font-weight", "bold")
            .text(d => d.data.org_name))
          .call(text => text.filter(d => (d.endAngle - d.startAngle) > 0.25).append("tspan")
            .attr("x", 0)
            .attr("y", "0.7em")
            .attr("fill-opacity", 0.7)
            .text(d => d.data.n_frdocs));
      },
    },
  };
  </script>

  