
<template>
  <v-card
  color="grey-lighten-3"
  class="my-11 elevation-10 d-inline-block"
  >
    <div class="text-h2 ma-11">
      {{$route.params.orgName}}
    </div>
  </v-card>

  <div class="d-flex flex-row ma-10">
    <div class="d-flex flex-column">
      <div class="mb-10 grey-lighten-3">
        <div class="d-flex flex-row grey-lighten-3">
          <div class="text-h1">
            #
          </div>
          <div class="text-h3 ml-5">
            <div>Rules</div>
            <div>Influenced</div>
          </div>
        </div>
      </div>
      <div class="d-flex flex-row">
        <div class="d-flex flex-column mb-10 grey-lighten-3">
          <h2>Agencies Most Impacted by {{$route.params.orgName}}</h2>
          <OrgResponsesTable :orgName="$route.params.orgName"/>
        </div>
      </div>
      <div class="d-flex flex-row">
        <div class="d-flex flex-column mb-10 grey-lighten-3">
          <h2>Rules Most Impacted by {{$route.params.orgName}}</h2>
          <div class="d-flex flex-column my-5 grey-lighten-3">
            <v-card class="elevation-10">
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
            </v-card>
          </div>
        </div>
      </div>
    </div>
    <div class="d-flex flex-column align-center justify-space-between">
      <div class="d-flex flex-column ml-16 align-center elevation-15">
        <div class="text-h5">
          Avg. Predicted Prob. of Influential Comment
        </div>
        <v-icon 
          icon="mdi-circle"
          size="300"
        ></v-icon>
        <div class="text-h3">
          #%
        </div>
      </div>
      <div>
        Area For Extra Info
      </div>
    </div>
  </div>
</template>

<script setup>
import OrgResponsesTable from "../components/OrgResponsesTable.vue";
</script>
