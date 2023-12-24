
<template>
  <div class="d-flex flex-column justify-center">
    <div class="d-flex container justify-center text-h2 mt-16">
      {{$route.params.orgName}}
    </div>

    <div class="d-flex flex-row my-10 justify-space-evenly">
      <div class="d-flex flex-column">

        <v-sheet 
        class="d-flex flex-column my-3 pa-10 justify-space-between bg-rie-primary-color"
        rounded="xl"
        >

          <h2><v-icon icon="mdi-file-chart"></v-icon> Statistics</h2>

          <div class="d-flex flex-row justify-space-between">
            <div class="d-flex flex-column justify-space-evenly">
              <h4>Comments Made:</h4>
              <h4>Responses Received:</h4>
            </div>
            <v-sheet 
            class="d-flex flex-row bg-rie-secondary-color pa-4"
            rounded="xl"
            >
              <div class="text-h1">
                #
              </div>
              <div class="text-h3 ml-5">
                <div>Rules</div>
                <div>Influenced</div>
              </div>
            </v-sheet>
          </div>
        </v-sheet>

        <v-divider class="d-flex container justify-center" :style="{width: '50%', opacity: '0.5'}"></v-divider>

        <v-sheet 
        class="d-flex flex-row my-3 pa-10 bg-rie-primary-color"
        rounded="xl"
        >
          <div class="d-flex flex-column mb-10">
            <h2><v-icon icon="mdi-domain"></v-icon> Agencies Most Impacted by {{$route.params.orgName}}</h2>
            <OrgResponsesTable :orgName="$route.params.orgName"/>
          </div>
        </v-sheet>

        <v-divider class="d-flex container justify-center" :style="{width: '50%', opacity: '0.5'}"></v-divider>

        <v-sheet 
        class="d-flex flex-column my-3 pa-10 bg-rie-primary-color"
        rounded="xl"
        >

          <h2><v-icon icon="mdi-book-open-variant"></v-icon> Rules Most Impacted by {{$route.params.orgName}}</h2>

          <div class="d-flex flex-column mt-5">
            <v-sheet 
            class="pa-4 bg-rie-secondary-color"
            rounded="xl"
            >
              <div class="d-flex flex-row justify-space-between">
                <div class="d-flex flex-column">
                  <div>
                    Rule Title By "Authors"
                  </div>
                  <div>
                    Comment Made
                  </div>
                  <div>
                    Response
                  </div>
                </div>
                <div class="d-flex flex-column">
                  <div class="ml-15 text-h3">
                    #
                  </div>
                  <div>
                  Changes made
                  </div>
                </div>
              </div>
            </v-sheet>
          </div>
        </v-sheet> 
      </div>

      <v-divider vertical :style="{width: '50%', opacity: '0.5'}"></v-divider>

      <div class="d-flex flex-column align-center justify-space-evenly">
        <v-sheet 
        class="d-flex flex-column pa-10 bg-rie-primary-color align-center elevation-15"
        rounded="xl"
        >
          <div class="text-h5 mb-3">
            <v-icon icon="mdi-chart-pie"></v-icon> Avg. Predicted Prob. of Influential Comment
          </div>
            <svg :width="300" :height="300" ref="chart"></svg>
          <div class="text-h3 mt-4">
            #%
          </div>
        </v-sheet>

        <v-divider class="d-flex container justify-center" :style="{width: '50%', opacity: '0.5'}"></v-divider>

        <v-sheet
        class="pa-10 bg-rie-primary-color align-center elevation-15"
        rounded="xl"
        >
          Area For Extra Info
        </v-sheet>
      </div>
    </div>
  </div>
</template>

<script>
import OrgResponsesTable from "../components/OrgResponsesTable.vue";
import * as d3 from 'd3';

export default {
  data() {
    return {
      width: 300,
      height: 300,
      data: [25, 75],
    };
  },
  mounted() {
    this.animateChart();
  },
  methods: {
    animateChart() {
      const svg = d3.select(this.$refs.chart);
      const radius = Math.min(this.width, this.height) / 2;

      const colorScale = d3.scaleOrdinal().range(['white', '#3d6565']);
      const pie = d3.pie();
      const arc = d3.arc().innerRadius(0).outerRadius(radius);

      const pieData = pie(this.data);

      // Draw initial pie chart with zero size for the red part
      svg
        .selectAll('path')
        .data(pieData)
        .enter()
        .append('path')
        .attr('d', arc)
        .attr('fill', (d, i) => colorScale(i))
        .attr('transform', `translate(${this.width / 2},${this.height / 2})`)
        .transition()
        .duration(1500) // Adjust the duration as needed
        .attrTween('d', function (d, i) {
          const interpolate = d3.interpolate({ startAngle: 0, endAngle: 0 }, d);
          return function (t) {
            return arc(interpolate(t));
          };
        });
    },
  },
  components:{
    OrgResponsesTable
  }
};
</script>
