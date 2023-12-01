<template>
  <div>
    <h1>This is the filter box</h1>
    <p>parentToChild:{{ testParentToChild }}</p>
    <p>childToParent:<input @change="updateFilter(filterTextBoxValue)" v-model="filterTextBoxValue"/></p>

  </div>
</template>

<script>
  export default {
    name: "FilterBox",
    methods: {
      updateFilter() {
        this.$emit("filterUpdated", this.filterTextBoxValue)
      }
    },
    props: ["testParentToChild"],
    watch: {
      filterTextBoxValue(oldVal, newVal) {
        console.log("watcher");
        this.updateFilter();
      }
    },
    emits: ['filterUpdated'],
    data() {
      return {
        filterTextBoxValue:""
      };
    },
    mounted() {

    }
  }
</script>

<style scoped>

</style>
