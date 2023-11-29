<script setup>
import { defineProps, defineEmits } from "vue";
const sortBy = [ "orgName",  "yCount", "frdocs", "None" ];
const sortOrder = [ "DESC", "ASC", "None" ];
const emit = defineEmits(['sortByChanged', 'orderByChanged'])

const props = defineProps({
    sortBySelection: "orgName",
    sortOrderSelection: "DESC",
});
function sortTable(event) {
    console.log(event.target.value)
    emit('sortByChanged', event.target.value)
}
function orderTable(event) {
    console.log(event.target.value)
    emit('orderByChanged', event.target.value)
}
</script>

<template>
    <div class="search-module">
      <input v-model.trim="search" type="text" placeholder="Search an Organization...">
      <select v-model="sortBy[0]" @change="sortTable($event)">
        <option v-for="opt in sortBy">{{ opt }}</option>
      </select>
      <select v-model="sortOrder[0]" @change="orderTable($event)">
        <option v-for="opt in sortOrder">{{ opt }}</option>
      </select>
    </div>
</template>

<style scoped>
  select {
    margin-left: 30px;
    margin-bottom: 10px;
    margin-top: 5px;
    border: none;
    background-color: rgb(26, 88, 68);
    border-radius: 5px;
  }
  input {
    border: none;
    background-color: #DB9B92;
    border-radius: 5px;
    padding: 10px;
    display: flex;
    width: 60%;
    margin-left: 30px;
    
    margin-bottom: 10px;
    margin-top: 25px;
  }

</style>
