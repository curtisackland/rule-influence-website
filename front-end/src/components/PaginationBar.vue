<template>
  <v-row justify="center">
    <v-col>
      <v-row justify="center" align="center">
        <v-btn
            v-for="page in displayedPages"
            :text="formatPageNumber(page)"
            :color="page === currentPage ? 'rie-primary-color' : ''"
            :prepend-icon="page === 1 && displayedPages.size > 1 ? 'mdi-page-first' : ''"
            :append-icon="page === totalPages && displayedPages.size > 1 ? 'mdi-page-last' : ''"
            class="m-2"
            @click="emitPageChange(page)"/>
      </v-row>
      <v-row justify="center" align="center">
        <v-text-field
            type="number"
            :rules="rules"
            hide-details
            label="Enter Page Number"
            variant="outlined"
            @input="updatePageNumberInput"
        ></v-text-field>
        <v-select
            v-model="numberOfItemsPerPage"
            type="number"
            :items="perPageOptions"
            label="Items per page"
            variant="outlined"
            hide-details
            @update:modelValue="emitItemsPerPageChange"
        ></v-select>
      </v-row>
    </v-col>
  </v-row>
</template>

<script>
export default {
  name: "PaginationBar",
  props: {
    currentPage: Number,
    totalPages: Number,
    fetchData: Function,
    pagesToShow: Number,
    itemsPerPage: Number,
  },
  methods: {
    convertPageToNumber(page) {
      return parseInt(page.replace(/,/g, ""))
    },
    formatPageNumber(page) {
      return page.toString();
    },
    checkPage(page) {
      return this.displayedPages.includes(this.convertPageToNumber(page));
    },
    updatePageNumberInput(event) {
      const page = event.target.value;
      if (page > 0 && page <= this.totalPages) {
        this.emitPageChange(page);
      } else if (page < 1) {
        this.emitPageChange(1);
      } else if (page > this.totalPages) {
        this.emitPageChange(this.totalPages);
      }
    },
    emitPageChange(page) {
      this.$emit('update:currentPage', parseInt(page));
    },
    emitItemsPerPageChange(newItemsPerPage) {
      this.$emit('update:itemsPerPage', parseInt(newItemsPerPage));
    }
  },
  data() {
    return {
      rules: [
          value => !!value || 'Enter a number',
          value => value <= this.totalPages && value > 0 || 'Number must be 1-100'
      ],
      pageNumber: 1,
      test: null,
      numberOfItemsPerPage: this.itemsPerPage,
      perPageOptions: [2, 5, 10, 25, 100]
    }
  },
  computed: {
    displayedPages() {
      const pages = new Set([1]);

      const pagesToDisplay = this.pagesToShow - 2; // - 2 because we always want to display the first and last page

      let startPage = Math.max(this.currentPage - Math.floor(pagesToDisplay / 2), 1);
      let endPage = Math.min(startPage + pagesToDisplay - 1, this.totalPages);

      if (this.totalPages - Math.ceil(pagesToDisplay / 2) <= this.currentPage) {
        startPage = Math.max(this.totalPages - pagesToDisplay, 1);
      }

      if (this.currentPage <= 1 + Math.ceil(pagesToDisplay / 2)) {
        startPage = 1;
        endPage = Math.min(startPage + pagesToDisplay, this.totalPages);
      }

      for (let i = startPage; i <= endPage; i++) {
        pages.add(i);
      }

      pages.add(this.totalPages);

      return pages;
    },
  },
}
</script>

<style scoped>
.pagination-items :deep(.v-pagination__item) {
  margin: 0;
  padding: 0;
}
</style>