
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

          <div v-if="Org_Info_data" class="d-flex flex-row justify-space-between">
            <div class="d-flex flex-column justify-space-evenly">
              <h4>Comments Made: {{Org_Info_data["number_of_comments"]}}</h4>
              <h4>Responses Received: {{Org_Info_data["total_response_count"]}}</h4>
            </div>
            <v-sheet 
            class="d-flex flex-row bg-rie-secondary-color pa-4"
            rounded="xl"
            >
              <div class="text-h1 font-weight-black">
                {{Org_Info_data["total_rules_changed"]}}
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
            <v-data-table
              v-if='Org_Agency_data'
                :items='Org_Agency_data'
                :headers='headers'
            >
            </v-data-table>
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
            class="pa-4 my-2 bg-rie-secondary-color"
            rounded="xl"
            v-for="row in Org_Rule_data"
            >
              <div class="d-flex flex-row justify-space-between">
                <div class="d-flex flex-column justify-space-between">
                  <div class="font-weight-bold">
                    {{row["title"]}}
                  </div>
                  <div class="font-italic">
                    By {{agencyString(row["agencies"])}}
                  </div>
                  <a :href="'https://www.federalregister.gov/d/' + row['frdoc_number']" target="_blank" class="mt-4">
                    <v-btn text="FR Document on Federal Register" rounded="xl" density="compact"></v-btn>
                  </a>
                </div>
                <div class="d-flex flex-column justify-center">
                  <div class="ml-15 text-h3 font-weight-black">
                    {{row["sumScore"]}}
                  </div>
                  <div class="d-flex flex-row-reverse">
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
        v-if="Org_Info_data"
        >
          <div class="text-h5 mb-3">
            <v-icon icon="mdi-chart-pie"></v-icon> Avg. Predicted Prob. of Influential Comment
          </div>
            <svg :width="300" :height="300" ref="chart"></svg>
          <div class="text-h3 mt-4 font-weight-black">
            {{(rounded_y_prob * 100).toFixed(4)}}%
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
import axios from "axios";

export default {
  data() {
    return {
      width: 300,
      height: 300,
      avg_y_prob: [0, 0],
      rounded_y_prob: 0,
      Org_Info_data: null,
      Org_Agency_data: null,
      Org_Rule_data: null,
      headers: [
        {title: 'Agency', key : 'agency'},
        {title: 'Total Docs by Agency', key : 'number_of_docs'},
        {title: 'Docs Changed by Organization', key : 'docs_changed'},
        {title: 'Influence Percentage', key : 'influence_percentage'}
      ]
    };
  },
  async mounted() {
    await this.fetchData(); 
    this.animateChart();
  },
  methods: {
    async fetchData(){

      //Fetch Statistics and Y Probability
      await axios.get(import.meta.env.VITE_BACKEND_URL + "/api/organization/" + encodeURIComponent(this.$route.params.orgName))
          .then(response => {
            this.Org_Info_data = response.data;
          }).catch(error => {
            if (!axios.isCancel(error)) {
              if (error.response.data.error) {
                this.errorMessage = error.response.data.error;
              } else {
                this.errorMessage = "Unable to load data."
              }
            }
          });

      //Fetch Agencies Most Impacted Data
      await axios.get(import.meta.env.VITE_BACKEND_URL + "/api/organization_agency/" + encodeURIComponent(this.$route.params.orgName))
          .then(response => {
            this.Org_Agency_data = response.data.data;
          }).catch(error => {
            if (!axios.isCancel(error)) {
              if (error.response.data.error) {
                this.errorMessage = error.response.data.error;
              } else {
                this.errorMessage = "Unable to load data."
              }
            }
          });

      //Fetch Rules Most Impaced Data
      await axios.get(import.meta.env.VITE_BACKEND_URL + "/api/organization_doc_changes/" + encodeURIComponent(this.$route.params.orgName))
          .then(response => {
            this.Org_Rule_data = response.data;
          }).catch(error => {
            if (!axios.isCancel(error)) {
              if (error.response.data.error) {
                this.errorMessage = error.response.data.error;
              } else {
                this.errorMessage = "Unable to load data."
              }
            }
          });
          
    },
    animateChart() {

      if (!this.Org_Info_data["y_prob_avg"]){
        this.avg_y_prob = [100, 0];
        this.rounded_y_prob = 0.00;
      }
      else{
        this.rounded_y_prob = this.Org_Info_data["y_prob_avg"];
        this.avg_y_prob = [100 - (100 * this.Org_Info_data["y_prob_avg"]), (100 * this.Org_Info_data["y_prob_avg"])];
      }
      const svg = d3.select(this.$refs.chart);
      const radius = Math.min(this.width, this.height) / 2;

      const colorScale = d3.scaleOrdinal().range(['white', '#3d6565']);
      const pie = d3.pie();
      const arc = d3.arc().innerRadius(0).outerRadius(radius);

      const pieData = pie(this.avg_y_prob);

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
    agencyString(agencies){
      return agencies[0] ? agencies.join(', ') : 'No authors';
    },
  },
  components:{
    OrgResponsesTable
  }
};
</script>
