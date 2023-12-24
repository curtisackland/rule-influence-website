
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
          <div class="text-h5">
            <v-icon icon="mdi-chart-pie"></v-icon> Avg. Predicted Prob. of Influential Comment
          </div>
          <v-icon 
            icon="mdi-circle"
            size="300"
          ></v-icon>
          <div class="text-h3">
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

<script setup>
import OrgResponsesTable from "../components/OrgResponsesTable.vue";
</script>
