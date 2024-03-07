
<template>
  <div class="d-flex flex-column align-center justify-center">
    <div class="d-flex justify-center text-h2 mt-16">
      {{$route.params.orgName}}
    </div>

    <div class="d-flex flex-row my-10 justify-space-evenly">
      <div class="d-flex flex-column">

        <v-sheet
        v-if="Org_Info_data"
        class="d-flex flex-column my-3 pa-10 justify-space-between bg-rie-primary-color align-center"
        rounded="xl"
        max-width="1200"
        >
          
          <h2 class="mb-4"><v-icon icon="mdi-file-chart"></v-icon> Statistics</h2>

          <div
          class="d-flex flex-row justify-space-between"
          >
            <h4 style="line-height: 2;">
              {{$route.params.orgName}} has submitted 
              <RouterLink :to="{ name: 'comments', query: { orgName: $route.params.orgName } }">
              <span class="bigger-text text-white">{{Org_Info_data["number_of_comments"]}} comments</span>
              </RouterLink>
              on <span class="bigger-text">{{Org_Info_data["total_rules"] }} rules</span>.
              From their comments, {{$route.params.orgName}} has received 
              <RouterLink :to="{ name: 'responses', query: { orgName: $route.params.orgName } }">
              <span class="bigger-text text-white">{{Org_Info_data["total_response_count"]}} responses</span>
              </RouterLink>, 
              resulting in <span class="bigger-text">{{Org_Info_data["total_rules_changed"]}} policy changes</span>.
            </h4>
            <v-sheet
            class="d-flex flex-column pa-10 ml-10 bg-rie-secondary-color align-center elevation-15"
            rounded="xl"
            min-width="500"
            v-if="Org_Info_data"
            >
              <div class="text-h5 mb-3">
                <v-icon icon="mdi-chart-pie"></v-icon> Comment Influency Percentage
                <v-tooltip
                  location="top"
                >
                  <template v-slot:activator="{ props }">
                    <v-btn
                      density="compact"
                      icon
                      v-bind="props"
                    >
                      <v-icon>
                        mdi-information-slab-symbol
                      </v-icon>
                    </v-btn>
                  </template>
                  <span>
                      Each comment from {{$route.params.orgName}} has an<br> assigned probability of the likelihood it has induced a rule change.<br> 
                      The average predicted probability takes the mean of all those probabilities.<br> This displays how infuential the comments from {{$route.params.orgName}} are.
                  </span>
                </v-tooltip>
              </div>
                <svg :width="150" :height="150" ref="chart"></svg>
              <div class="text-h3 mt-4 font-weight-black">
                ≈{{(rounded_y_prob * 100).toFixed(0)}}%
              </div>
            </v-sheet>
          </div>
        </v-sheet>

        <v-divider class="d-flex container justify-center" :style="{width: '50%', opacity: '0.5'}"></v-divider>

        <v-sheet 
        class="d-flex flex-column my-3 pa-10 bg-rie-primary-color align-center"
        rounded="xl"
        max-width="1200"
        >  
          <h2 class="mb-4"><v-icon icon="mdi-domain"></v-icon> Agencies Most Impacted by {{$route.params.orgName}}</h2>
          <v-progress-linear color="rie-primary-color" height="6" rounded :indeterminate="searchIsLoading"></v-progress-linear>
          <v-data-table
            class="mb-5"
            v-if='Org_Agency_data'
              :items='Org_Agency_data'
              :headers='headers'
              :page.sync="Org_Agency_currentPage"
          >
            <template v-slot:bottom>
            </template>
          </v-data-table>
          <PaginationBar
            :current-page.sync="Org_Agency_currentPage"
            :total-pages="Org_Agency_totalPages"
            :pages-to-show="Org_Agency_pagesToShow"
            :items-per-page="Org_Agency_itemsPerPage"
            :is-loading="searchIsLoading"
            @update:current-page="updateCurrentPage"
            @update:items-per-page="updateItemsPerPage"
          />
        </v-sheet>

        <v-divider class="d-flex container justify-center" :style="{width: '50%', opacity: '0.5'}"></v-divider>

        <v-sheet 
        class="d-flex flex-column my-3 pa-10 bg-rie-primary-color align-center"
        rounded="xl"
        max-width="1200"
        >

          <h2><v-icon icon="mdi-book-open-variant"></v-icon> Rules Most Impacted by {{$route.params.orgName}}</h2>

          <div class="d-flex flex-column mt-5">
            <v-sheet 
            class="pa-4 my-2 bg-rie-secondary-color"
            rounded="lg"
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
                  <div class="d-flex flex-row justify-start mt-4">
                    <a :href="'https://www.federalregister.gov/d/' + row['frdoc_number']" target="_blank" class="">
                      <v-btn text="Federal Register Document" rounded="lg" density="compact"></v-btn>
                    </a>
                    <RouterLink :to="{ name: 'rules', query: { frdocNumber: row['frdoc_number'] } }">
                      <v-btn text="Rule Page" rounded="lg" density="compact" class="ml-10"></v-btn>
                    </RouterLink>
                  </div>
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

      <!--<div class="d-flex flex-column align-center justify-space-evenly">

        <v-sheet
        class="pa-10 bg-rie-primary-color my-3 pa-10 align-center elevation-15"
        rounded="xl"
        width="600px"
        >
          <h2 class="text-center mb-3"><v-icon icon="mdi-message-reply-text"></v-icon> Most Influential Comments</h2>
          <v-sheet 
          class="pa-4 my-4 bg-rie-secondary-color"
          rounded="lg"
          v-for="row in Org_Comment_data"
          >
            <div class="d-flex flex-row justify-space-between">
              <div>
                <div class="font-italic">
                  Responses: {{row['linked_responses']}}
                </div>
                <div class="font-italic font-weight-bold">
                  Changes: {{row['number_of_changes']}}
                </div>
              </div>
              <div class="d-flex flex-column align-center">
                <a :href="'https://www.regulations.gov/comment/' + row['comment_id']" target="_blank" class="">
                  <v-btn density="compact" rounded="lg">View on Regulations.gov</v-btn>
                </a>
                <a :href="'../comments/' + row['comment_id']" target="_blank" class="">
                  <v-btn density="compact" rounded="lg" class="mt-2">Comment Page</v-btn>
                </a>
              </div>
            </div>
          </v-sheet>
        </v-sheet>

      </div>-->
    </div>
  </div>
</template>

<script>
import OrgResponsesTable from "../components/OrgResponsesTable.vue";
import PaginationBar from "@/components/PaginationBar.vue";
import * as d3 from 'd3';
import axios from "axios";

export default {
  data() {
    return {
      chartWidth: 150,
      chartHeight: 150,
      avg_y_prob: [0, 0],
      rounded_y_prob: 0,
      Org_Info_data: null,
      Org_Agency_data: null,
      searchIsLoading: false,
      OrganizationName: encodeURIComponent(this.$route.params.orgName),
      Org_Agency_currentPage: 1,
      Org_Agency_totalPages: null,
      Org_Agency_itemsPerPage: 10,
      Org_Agency_pagesToShow: 9,
      Org_Rule_data: null,
      Org_Comment_data: null,
    };
  },
  async mounted() {
    await this.fetchData(); 
    this.animateChart();
  },
  methods: {
    async fetchData(){

      this.searchIsLoading = true;

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
      await axios.get(import.meta.env.VITE_BACKEND_URL + "/api/organization_agency/" + encodeURIComponent(this.$route.params.orgName), {
        params: {
          filters: {
            page: this.Org_Agency_currentPage, // has to be an integer || NULL
            itemsPerPage: this.Org_Agency_itemsPerPage // has to be an integer || NULL
          }
        }
      })
          .then(response => {
            this.Org_Agency_data = response.data.data;
            this.Org_Agency_totalPages = response.data.totalPages;
          }).catch(error => {
            if (!axios.isCancel(error)) {
              if (error.response.data.error) {
                this.errorMessage = error.response.data.error;
              } else {
                this.errorMessage = "Unable to load data."
              }
            }
          });

      //Fetch Rules Most Impacted Data
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
      
      // //Fetch Top Influential Comments Data
      // await axios.get(import.meta.env.VITE_BACKEND_URL + "/api/comments", {
      //   params: { filters: {
      //       orgName: this.$route.params.orgName, //string of org name
      //       sortBy: "numberOfChanges", // sort by a specific column: "numberOfChanges" || "linkedResponses" || NULL
      //       sortOrder: "DESC", // can be "DESC" || "ASC" || NULL
      //     }},
      // }).then(response => {
      //       this.Org_Comment_data = response.data.data.slice(0,5);
      // }).catch(error => {
      //   if (!axios.isCancel(error)) {
      //     if (error.response.data.error) {
      //       this.errorMessage = error.response.data.error;
      //     } else {
      //       this.errorMessage = "Unable to load data."
      //     }
      //   }
      // });
      
      this.searchIsLoading = false;

    },
    updateCurrentPage(newPage) {
      this.Org_Agency_currentPage = newPage;
      this.fetchData();
    },
    updateItemsPerPage(newItemsPerPage) {
      this.Org_Agency_itemsPerPage = newItemsPerPage;
      this.fetchData();
    },
    animateChart() {

      if (!this.Org_Info_data["y_prob_avg"]){
        this.avg_y_prob = [100, 0];
        this.rounded_y_prob = 0.00;
      }
      else{
        this.rounded_y_prob = this.Org_Info_data["y_prob_avg"];
        this.avg_y_prob = [100 - (100 * this.rounded_y_prob), (100 * this.rounded_y_prob)];
      }
      const svg = d3.select(this.$refs.chart);
      const radius = Math.min(this.chartWidth, this.chartHeight) / 2;

      const colorScale = d3.scaleOrdinal().range(['#2F4F4F', 'white']);
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
        .attr('transform', `translate(${this.chartWidth / 2},${this.chartHeight / 2})`)
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
    OrgResponsesTable,
    PaginationBar
  }
};
</script>

<style>
  .bigger-text{
    font-size: 1.8em; 
    font-weight: bold;
  }
</style>