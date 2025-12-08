export default {
    name: "Paginator",
    template: `
    <div v-if="totalItems > 0" class="dataTables_wrapper container-fluid dt-bootstrap4">
        <div class="row">
            <!-- Infos -->
            <div class="col-sm-12 col-md-5">
                <div class="dataTables_info" id="example1_info" role="status" aria-live="polite">
                    Affichage de {{ startItem }} à {{ endItem }} sur {{ totalItems }} entrées
                </div>
            </div>

            <!-- Pagination -->
            <div class="col-sm-12 col-md-7">
                <div class="dataTables_paginate paging_simple_numbers" id="example1_paginate">
                    <ul class="pagination">
                        <!-- Previous -->
                        <li :class="['paginate_button', 'page-item', 'previous', {disabled: currentPage === 1}]" id="example1_previous">
                            <a href="#" class="page-link" @click.prevent="changePage(currentPage - 1)">Précédent</a>
                        </li>

                        <!-- Pages -->
                        <li v-for="page in visiblePages" :key="page" :class="['paginate_button', 'page-item', {active: page === currentPage}]">
                            <a href="#" class="page-link" @click.prevent="changePage(page)">{{ page }}</a>
                        </li>

                        <!-- Next -->
                        <li :class="['paginate_button', 'page-item', 'next', {disabled: currentPage === lastPage}]" id="example1_next">
                            <a href="#" class="page-link" @click.prevent="changePage(currentPage + 1)">Suivant</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    `,
    props: {
        currentPage: { type: Number, required: true },
        lastPage: { type: Number, required: true },
        totalItems: { type: Number, required: true },
        perPage: { type: Number, default: 10 },
        maxVisiblePages: { type: Number, default: 5 },
    },
    computed: {
        visiblePages() {
            const pages = [];
            const half = Math.floor(this.maxVisiblePages / 2);
            let start = Math.max(1, this.currentPage - half);
            let end = start + this.maxVisiblePages - 1;
            if (end > this.lastPage) {
                end = this.lastPage;
                start = Math.max(1, end - this.maxVisiblePages + 1);
            }
            for (let i = start; i <= end; i++) pages.push(i);
            return pages;
        },
        startItem() {
            return (this.currentPage - 1) * this.perPage + 1;
        },
        endItem() {
            return Math.min(this.currentPage * this.perPage, this.totalItems);
        },
    },
    methods: {
        changePage(page) {
            if (
                page >= 1 &&
                page <= this.lastPage &&
                page !== this.currentPage
            ) {
                this.$emit("update:currentPage", page);
                this.$emit("page-changed", page);
            }
        },
    },
};
