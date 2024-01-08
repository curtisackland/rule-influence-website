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
            :disabled="isLoading"
            class="m-2"
            @click="emitPageChange(page)"/>
      </v-row>
      <v-row justify="center" align="center">
        <v-text-field
            v-model="pageInput"
            type="number"
            label="Enter Page Number"
            variant="outlined"
            hide-details
            :disabled="isLoading"
            @input="debouncedPageNumberUpdate"
            class="mx-1 my-2 pagination-input"
        ></v-text-field>
        <v-select
            v-model="numberOfItemsPerPage"
            type="number"
            :items="perPageOptions"
            label="Items per page"
            variant="outlined"
            :disabled="isLoading"
            hide-details
            @update:modelValue="emitItemsPerPageChange"
            class="mx-1 my-2 pagination-input"
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
    pagesToShow: Number,
    itemsPerPage: Number,
    isLoading: Boolean,
  },
  methods: {
    formatPageNumber(page) {
      return page.toLocaleString();
    },
    updatePageNumberInput(event) {
      const page = event.target.value;
      if (page > 0 && page <= this.totalPages) {
        this.emitPageChange(page);
      } else if (page < 1) {
        this.pageInput = 1;
        this.emitPageChange(1);
      } else if (page > this.totalPages) {
        this.pageInput = this.totalPages;
        this.emitPageChange(this.totalPages);
      }
    },
    debouncedPageNumberUpdate(event) {
      clearTimeout(this.debounceTimeout);
      this.debounceTimeout = setTimeout(() => {
        this.performUpdate(event);
      }, 1000);
    },
    performUpdate(event) {
      this.updatePageNumberInput(event);
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
      pageNumber: 1,
      test: null,
      numberOfItemsPerPage: this.itemsPerPage,
      pageInput: this.currentPage,
      perPageOptions: [5, 10, 25, 100],
      debounceTimeout: null
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

      if (this.totalPages > 1) {
        pages.add(this.totalPages);
      }

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

.pagination-input {
  min-width: 150px;
  max-width: 150px;
}
</style>