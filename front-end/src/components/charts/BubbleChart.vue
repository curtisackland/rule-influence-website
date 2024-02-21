<template>
    <div ref="chartContainer" style="width: 500px; height: 500px;"></div>
</template>
  
<script>
  import * as d3 from 'd3';
  
  export default {
    props: {
      data: {
        type: Array,
        required: true,
      },
      selectedCriteria: String
    },
    data() {
      return {
        criteriaOptions : [
      { title: 'number of changes', value: 'org_changes' },
      { title: 'number of responses', value: 'org_responses' },
      { title: 'number of rules', value: 'org_rules' }, ]
      }
    },
    mounted() {
      this.createChart();
    },
    watch: {
      selectedCriteria() {
        this.clearChart();
        this.createChart();
      },
      data() {
        this.clearChart();
        this.createChart();
      },
    },
    methods: {
      clearChart() {
        const chartContainer = this.$refs.chartContainer;
        d3.select(chartContainer).selectAll("svg").remove();
      },
      createChart() {
        const width = this.data.length > 11 ? 560 : 460;
        const height = this.data.length > 11 ? 560 : 460;
        const chartContainer = this.$refs.chartContainer;

        // Clear existing chart
        this.clearChart();
  
        const svg = d3.select(chartContainer)
          .append("svg")
          .attr("width", width)
          .attr("height", height);
  
        const color = d3.scaleOrdinal()
          .domain(this.data.map(d => d.org_name))
          .range(d3.schemeSet1);
  
        const size = d3.scaleLinear()
          .domain([0, d3.max(this.data, d => +d[this.selectedCriteria])])
          .range([7, 55]);
  
        const Tooltip = d3.select(this.$refs.chartContainer)
          .append("div")
          .style("opacity", 0)
          .attr("class", "tooltip")
          .style("background-color", "white")
          .style("border", "solid")
          .style("border-width", "2px")
          .style("border-radius", "5px");
  
        const mouseover = function (d) {
          Tooltip.style("opacity", 1);
        };
  
        const mousemove  = (selectedCriteria) => (event, d) => {
          const data = d3.select(event.currentTarget).data()[0];
          const selectedTitle = this.criteriaOptions.find(option => option.value === this.selectedCriteria).title;
          console.log(data)
  
          if (data) {
            Tooltip
              .html(`<u>${data.org_name}</u><br>The ${selectedTitle} is ${data[selectedCriteria]}`)
              .style("left", `${chartContainer.offsetLeft + width}px`)
              .style("top", `${0}px`);
          }
        };
  
        const mouseleave = function (d) {
          Tooltip.style("opacity", 0);
        };
  
        function getColor(n_frdocs) {
          const baseColor = d3.rgb(
            Math.floor(Math.random() * 150) + 100,
            Math.floor(Math.random() * 150) + 100,
            Math.floor(Math.random() * 150) + 100
          );
          const pastelColor = baseColor.brighter(0.5);
          return pastelColor.toString();
        }
  
        const node = svg.selectAll("circle")
          .data(this.data)
          .enter()
          .append("circle")
          .attr("class", "node")
          .attr("r", d => size(+d[this.selectedCriteria]))
          .attr("cx", width / 2)
          .attr("cy", height / 2)
          .style("fill", d => getColor(+d[this.selectedCriteria]))
          .style("fill-opacity", 0.8)
          .attr("stroke", "black")
          .style("stroke-width", 1)
          .on("mouseover", mouseover)
          .on("mousemove", mousemove(this.selectedCriteria))
          .on("mouseleave", mouseleave)
          .style("transition", "transform 200ms ease-in-out");
  
        const simulation = d3.forceSimulation()
          .force("center", d3.forceCenter().x(width / 2).y(height / 2))
          .force("charge", d3.forceManyBody().strength(.1))
          .force("collide", d3.forceCollide().strength(.2).radius(d => (size(+d[this.selectedCriteria]) + 3)).iterations(1));
  
        simulation
          .nodes(this.data)
          .on("tick", function (d) {
            node
              .attr("cx", d => d.x)
              .attr("cy", d => d.y);
          });
      },
    }, 
  };
  </script>