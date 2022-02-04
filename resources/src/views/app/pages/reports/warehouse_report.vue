<template>
  <div class="main-content">
    <breadcumb :page="$t('Warehouse_report')" :folder="$t('Reports')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>

    <b-row class="justify-content-center mb-5" v-if="!isLoading">
      <!-- warehouse -->
      <b-col lg="3" md="6" sm="12">
        <b-form-group :label="$t('warehouse')">
          <v-select
            @input="Selected_Warehouse"
            v-model="Filter_warehouse"
            :reduce="label => label.value"
            :placeholder="$t('All_Warehouses')"
            :options="warehouses.map(warehouses => ({label: warehouses.name, value: warehouses.id}))"
          />
        </b-form-group>
      </b-col>
    </b-row>

    <b-row v-if="!isLoading">
      <!-- ICON BG -->
      <b-col lg="3" md="6" sm="12">
        <b-card class="card-icon-bg card-icon-bg-primary o-hidden mb-30 text-center">
          <i class="i-Full-Cart"></i>
          <div class="content">
            <p class="text-muted mt-2 mb-0">{{$t('Sales')}}</p>
            <p class="text-primary text-24 line-height-1 mb-2">{{total.sales}}</p>
          </div>
        </b-card>
      </b-col>
      <b-col lg="3" md="6" sm="12">
        <b-card class="card-icon-bg card-icon-bg-primary o-hidden mb-30 text-center">
          <i class="i-Checkout-Basket"></i>
          <div class="content">
            <p class="text-muted mt-2 mb-0">{{$t('Purchases')}}</p>
            <p class="text-primary text-24 line-height-1 mb-2">{{total.purchases}}</p>
          </div>
        </b-card>
      </b-col>
      <b-col lg="3" md="6" sm="12">
        <b-card class="card-icon-bg card-icon-bg-primary o-hidden mb-30 text-center">
          <i class="i-Turn-Left"></i>
          <div class="content">
            <p class="text-muted mt-2 mb-0">{{$t('PurchasesReturn')}}</p>
            <p class="text-primary text-24 line-height-1 mb-2">{{total.ReturnPurchase}}</p>
          </div>
        </b-card>
      </b-col>
      <b-col lg="3" md="6" sm="12">
        <b-card class="card-icon-bg card-icon-bg-primary o-hidden mb-30 text-center">
          <i class="i-Turn-Right"></i>
          <div class="content">
            <p class="text-muted mt-2 mb-0">{{$t('SalesReturn')}}</p>
            <p class="text-primary text-24 line-height-1 mb-2">{{total.ReturnSale}}</p>
          </div>
        </b-card>
      </b-col>
    </b-row>

    <b-row v-if="!isLoading">
      <b-col md="12">
        <b-card no-body class="card mb-30" header-bg-variant="transparent ">
          <b-tabs active-nav-item-class="nav nav-tabs" content-class="mt-3">
            <!-- Quotations Table -->
            <b-tab :title="$t('Quotations')">
              <vue-good-table
                mode="remote"
                :columns="columns_quotations"
                :totalRows="totalRows_quotations"
                :rows="quotations"
                @on-page-change="PageChangeQuotation"
                @on-per-page-change="onPerPageChangeQuotation"
                @on-search="onSearch_Quotations"
                :search-options="{
                    placeholder: $t('Search_this_table'),
                    enabled: true,
                }"
                :pagination-options="{
                  enabled: true,
                  mode: 'records',
                  nextLabel: 'next',
                  prevLabel: 'prev',
                }"
                styleClass="order-table vgt-table mt-2"
              >
                <template slot="table-row" slot-scope="props">
                  <div v-if="props.column.field == 'statut'">
                    <span
                      v-if="props.row.statut == 'sent'"
                      class="badge badge-outline-success"
                    >{{$t('Sent')}}</span>
                    <span v-else class="badge badge-outline-info">{{$t('Pending')}}</span>
                  </div>
                </template>
              </vue-good-table>
            </b-tab>

            <!-- Sales Table -->
            <b-tab :title="$t('Sales')">
              <vue-good-table
                mode="remote"
                :columns="columns_sales"
                :totalRows="totalRows_sales"
                :rows="sales"
                @on-page-change="PageChangeSales"
                @on-per-page-change="onPerPageChangeSales"
                @on-search="onSearch_Sales"
                :search-options="{
                    placeholder: $t('Search_this_table'),
                    enabled: true,
                }"
                :pagination-options="{
                  enabled: true,
                  mode: 'records',
                  nextLabel: 'next',
                  prevLabel: 'prev',
                }"
                styleClass="order-table vgt-table mt-2"
              >
                <template slot="table-row" slot-scope="props">
                  <div v-if="props.column.field == 'statut'">
                    <span
                      v-if="props.row.statut == 'completed'"
                      class="badge badge-outline-success"
                    >{{$t('complete')}}</span>
                    <span
                      v-else-if="props.row.statut == 'pending'"
                      class="badge badge-outline-info"
                    >{{$t('Pending')}}</span>
                    <span v-else class="badge badge-outline-warning">{{$t('Ordered')}}</span>
                  </div>
                  <div v-else-if="props.column.field == 'payment_status'">
                    <span
                      v-if="props.row.payment_status == 'paid'"
                      class="badge badge-outline-success"
                    >{{$t('Paid')}}</span>
                    <span
                      v-else-if="props.row.payment_status == 'partial'"
                      class="badge badge-outline-primary"
                    >{{$t('partial')}}</span>
                    <span v-else class="badge badge-outline-warning">{{$t('Unpaid')}}</span>
                  </div>
                </template>
              </vue-good-table>
            </b-tab>

            <!-- Returns Sale Table -->
            <b-tab :title="$t('SalesReturn')">
              <vue-good-table
                mode="remote"
                :columns="columns_returns_sale"
                :totalRows="totalRows_Return_sale"
                :rows="returns_sale"
                @on-page-change="PageChangeReturn_Customer"
                @on-per-page-change="onPerPageChangeReturn_Sale"
                :pagination-options="{
                    enabled: true,
                    mode: 'records',
                    nextLabel: 'next',
                    prevLabel: 'prev',
                  }"
                @on-search="onSearch_Return_Sale"
                :search-options="{
                    placeholder: $t('Search_this_table'),
                    enabled: true,
                }"
                styleClass="order-table vgt-table mt-2"
              >
                <template slot="table-row" slot-scope="props">
                  <div v-if="props.column.field == 'statut'">
                    <span
                      v-if="props.row.statut == 'received'"
                      class="badge badge-outline-success"
                    >{{$t('Received')}}</span>
                    <span v-else class="badge badge-outline-info">{{$t('Pending')}}</span>
                  </div>

                  <div v-else-if="props.column.field == 'payment_status'">
                    <span
                      v-if="props.row.payment_status == 'paid'"
                      class="badge badge-outline-success"
                    >{{$t('Paid')}}</span>
                    <span
                      v-else-if="props.row.payment_status == 'partial'"
                      class="badge badge-outline-primary"
                    >{{$t('partial')}}</span>
                    <span v-else class="badge badge-outline-warning">{{$t('Unpaid')}}</span>
                  </div>
                </template>
              </vue-good-table>
            </b-tab>

            <!-- Returns Purchase Table -->
            <b-tab :title="$t('PurchasesReturn')">
              <vue-good-table
                mode="remote"
                :columns="columns_returns_purchase"
                :totalRows="totalRows_Return_purchase"
                :rows="returns_purchase"
                @on-page-change="PageChangeReturn_Purchase"
                @on-per-page-change="onPerPageChangeReturn_Purchase"
                :pagination-options="{
                  enabled: true,
                  mode: 'records',
                  nextLabel: 'next',
                  prevLabel: 'prev',
                }"
                @on-search="onSearch_Return_Purchase"
                :search-options="{
                    placeholder: $t('Search_this_table'),
                    enabled: true,
                }"
                styleClass="order-table vgt-table mt-2"
              >
                <template slot="table-row" slot-scope="props">
                  <div v-if="props.column.field == 'statut'">
                    <span
                      v-if="props.row.statut == 'completed'"
                      class="badge badge-outline-success"
                    >{{$t('complete')}}</span>
                    <span v-else class="badge badge-outline-info">{{$t('Pending')}}</span>
                  </div>

                  <div v-else-if="props.column.field == 'payment_status'">
                    <span
                      v-if="props.row.payment_status == 'paid'"
                      class="badge badge-outline-success"
                    >{{$t('Paid')}}</span>
                    <span
                      v-else-if="props.row.payment_status == 'partial'"
                      class="badge badge-outline-primary"
                    >{{$t('partial')}}</span>
                    <span v-else class="badge badge-outline-warning">{{$t('Unpaid')}}</span>
                  </div>
                </template>
              </vue-good-table>
            </b-tab>

            <!-- Expense Table -->
            <b-tab :title="$t('Expenses')">
              <vue-good-table
                mode="remote"
                :columns="columns_Expense"
                :totalRows="totalRows_Expense"
                :rows="expenses"
                @on-page-change="PageChange_Expense"
                @on-per-page-change="onPerPageChange_Expense"
                :pagination-options="{
                  enabled: true,
                  mode: 'records',
                  nextLabel: 'next',
                  prevLabel: 'prev',
                }"
                @on-search="onSearch_Expense"
                :search-options="{
                    placeholder: $t('Search_this_table'),
                    enabled: true,
                }"
                styleClass="order-table vgt-table mt-2"
              ></vue-good-table>
            </b-tab>
          </b-tabs>
        </b-card>
      </b-col>
    </b-row>
    <b-row class="mt-3" v-if="!isLoading">
      <b-col lg="6" md="12" sm="12">
        <b-card class="mb-30">
          <h4 class="card-title m-0">{{$t('Total_Items_Quantity')}}</h4>
          <div class="chart-wrapper mt-3">
            <v-chart :options="Stock_Count" :autoresize="true"></v-chart>
          </div>
        </b-card>
      </b-col>
      <b-col col lg="6" md="12" sm="12">
        <b-card class="mb-30">
          <h4 class="card-title m-0">{{$t('Value_by_Cost_and_Price')}}</h4>
          <div class="chart-wrapper mt-3">
            <v-chart :options="Stock_value" :autoresize="true"></v-chart>
          </div>
        </b-card>
      </b-col>
    </b-row>
  </div>
</template>


<script>
import { mapActions, mapGetters } from "vuex";
import ECharts from "vue-echarts/components/ECharts.vue";

// import ECharts modules manually to reduce bundle size
import "echarts/lib/chart/pie";
import "echarts/lib/chart/bar";
import "echarts/lib/chart/line";
import "echarts/lib/component/tooltip";
import "echarts/lib/component/legend";

export default {
  components: {
    "v-chart": ECharts
  },
  metaInfo: {
    // if no subcomponents specify a metaInfo.title, this title will be used
    title: "Warehouse Report"
  },
  data() {
    return {
      Stock_Count: {},
      Stock_value: {},
      totalRows_quotations: "",
      totalRows_sales: "",
      totalRows_Return_sale: "",
      totalRows_Return_purchase: "",
      totalRows_Expense: "",
      limit_quotations: "10",
      limit_returns_Sale: "10",
      limit_returns_Purchase: "10",
      limit_sales: "10",
      limit_expenses: "10",
      search_quotation: "",
      search_sale: "",
      search_expense: "",
      search_return_Sale: "",
      search_return_Purchase: "",
      sales_page: 1,
      quotations_page: 1,
      Return_sale_page: 1,
      Return_purchase_page: 1,
      Expense_page: 1,
      isLoading: true,
      Filter_warehouse: "",
      sales: [],
      quotations: [],
      warehouses: [],
      expenses: [],
      returns_sale: [],
      returns_purchase: [],
      total: {
        sales: "",
        purchases: "",
        ReturnPurchase: "",
        ReturnSale: ""
      }
    };
  },

  computed: {
    ...mapGetters(["currentUser"]),
    columns_quotations() {
      return [
        {
          label: this.$t("date"),
          field: "date",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Customer"),
          field: "client_name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Reference"),
          field: "Ref",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Total"),
          field: "GrandTotal",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Status"),
          field: "statut",
          tdClass: "text-left",
          thClass: "text-left",
          html: true,
          sortable: false
        }
      ];
    },
    columns_sales() {
      return [
        {
          label: this.$t("Reference"),
          field: "Ref",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Customer"),
          field: "client_name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Status"),
          field: "statut",
          html: true,
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Total"),
          field: "GrandTotal",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Paid"),
          field: "paid_amount",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Due"),
          field: "due",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("PaymentStatus"),
          field: "payment_status",
          html: true,
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        }
      ];
    },
    columns_returns_sale() {
      return [
        {
          label: this.$t("Reference"),
          field: "Ref",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Customer"),
          field: "client_name",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Status"),
          field: "statut",
          html: true,
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Total"),
          field: "GrandTotal",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Paid"),
          field: "paid_amount",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Due"),
          field: "due",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("PaymentStatus"),
          field: "payment_status",
          html: true,
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        }
      ];
    },
    columns_returns_purchase() {
      return [
        {
          label: this.$t("Reference"),
          field: "Ref",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Supplier"),
          field: "provider_name",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Status"),
          field: "statut",
          html: true,
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Total"),
          field: "GrandTotal",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Paid"),
          field: "paid_amount",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Due"),
          field: "due",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("PaymentStatus"),
          field: "payment_status",
          html: true,
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        }
      ];
    },
    columns_Expense() {
      return [
        {
          label: this.$t("date"),
          field: "date",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Reference"),
          field: "Ref",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Details"),
          field: "details",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Amount"),
          field: "amount",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Categorie"),
          field: "category_name",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        }
      ];
    }
  },

  methods: {
    //------------------------------Formetted Numbers -------------------------\\
    formatNumber(number, dec) {
      const value = (typeof number === "string"
        ? number
        : number.toString()
      ).split(".");
      if (dec <= 0) return value[0];
      let formated = value[1] || "";
      if (formated.length > dec)
        return `${value[0]}.${formated.substr(0, dec)}`;
      while (formated.length < dec) formated += "0";
      return `${value[0]}.${formated}`;
    },

    //---------------------- Event Select Warehouse ------------------------------\\
    Selected_Warehouse(value) {
      if (value === null) {
        this.Filter_warehouse = "";
      }
      this.Get_Reports();
      this.Get_Sales(1);
      this.Get_Quotations(1);
      this.Get_Returns_Sale(1);
      this.Get_Returns_Purchase(1);
      this.Get_Expenses(1);
    },

    //------------------------------ Show Reports -------------------------\\
    Get_Reports() {
      axios
        .get("report/Warehouse_Report?warehouse_id=" + this.Filter_warehouse)
        .then(response => {
          this.total = response.data.data;
          this.warehouses = response.data.warehouses;
        })
        .catch(response => {});
    },

    //--------------------------- Sales Event Page Change  -------------\\
    PageChangeSales({ currentPage }) {
      if (this.sales_page !== currentPage) {
        this.Get_Sales(currentPage);
      }
    },

    //--------------------------- Limit Page Sales -------------\\
    onPerPageChangeSales({ currentPerPage }) {
      if (this.limit_sales !== currentPerPage) {
        this.limit_sales = currentPerPage;
        this.Get_Sales(1);
      }
    },

    onSearch_Sales(value) {
      this.search_sale = value.searchTerm;
      this.Get_Sales(1);
    },

    //--------------------------- Get sales By warehouse -------------\\
    Get_Sales(page) {
      axios
        .get(
          "report/Sales_Warehouse?page=" +
            page +
            "&limit=" +
            this.limit_sales +
            "&warehouse_id=" +
            this.Filter_warehouse +
            "&search=" +
            this.search_sale
        )
        .then(response => {
          this.sales = response.data.sales;
          this.totalRows_sales = response.data.totalRows;
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
        })
        .catch(response => {
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
        });
    },

    //--------------------------- Event Page Change -------------\\
    PageChangeQuotation({ currentPage }) {
      if (this.quotations_page !== currentPage) {
        this.Get_Quotations(currentPage);
      }
    },

    //--------------------------- Limit Page Quotations -------------\\
    onPerPageChangeQuotation({ currentPerPage }) {
      if (this.limit_quotations !== currentPerPage) {
        this.limit_quotations = currentPerPage;
        this.Get_Quotations(1);
      }
    },

    onSearch_Quotations(value) {
      this.search_quotation = value.searchTerm;
      this.Get_Quotations(1);
    },

    //--------------------------- Get Quotations By Warehouse -------------\\
    Get_Quotations(page) {
      axios
        .get(
          "report/Quotations_Warehouse?page=" +
            page +
            "&limit=" +
            this.limit_quotations +
            "&warehouse_id=" +
            this.Filter_warehouse +
            "&search=" +
            this.search_quotation
        )
        .then(response => {
          this.quotations = response.data.quotations;
          this.totalRows_quotations = response.data.totalRows;
        })
        .catch(response => {});
    },

    //--------------------------- Event Page Change -------------\\
    PageChangeReturn_Customer({ currentPage }) {
      if (this.Return_sale_page !== currentPage) {
        this.Get_Returns_Sale(currentPage);
      }
    },

    //--------------------------- Limit Page Returns Sale -------------\\
    onPerPageChangeReturn_Sale({ currentPerPage }) {
      if (this.limit_returns_Sale !== currentPerPage) {
        this.limit_returns_Sale = currentPerPage;
        this.Get_Returns_Sale(1);
      }
    },

    onSearch_Return_Sale(value) {
      this.search_return_Sale = value.searchTerm;
      this.Get_Returns_Sale(1);
    },

    //--------------------------- Get Returns Sale By warehouse -------------\\
    Get_Returns_Sale(page) {
      axios
        .get(
          "report/Returns_Sale_Warehouse?page=" +
            page +
            "&limit=" +
            this.limit_returns_Sale +
            "&warehouse_id=" +
            this.Filter_warehouse +
            "&search=" +
            this.search_return_Sale
        )
        .then(response => {
          this.returns_sale = response.data.returns_sale;
          this.totalRows_Return_sale = response.data.totalRows;
        })
        .catch(response => {});
    },

    //--------------------------- Event Page Change -------------\\
    PageChangeReturn_Purchase({ currentPage }) {
      if (this.Return_purchase_page !== currentPage) {
        this.Get_Returns_Purchase(currentPage);
      }
    },

    //--------------------------- Limit Page Returns Purchase -------------\\
    onPerPageChangeReturn_Purchase({ currentPerPage }) {
      if (this.limit_returns_Purchase !== currentPerPage) {
        this.limit_returns_Purchase = currentPerPage;
        this.Get_Returns_Purchase(1);
      }
    },

    onSearch_Return_Purchase(value) {
      this.search_return_Purchase = value.searchTerm;
      this.Get_Returns_Purchase(1);
    },

    //--------------------------- Get Returns Purchase By warehouse -------------\\
    Get_Returns_Purchase(page) {
      axios
        .get(
          "report/Returns_Purchase_Warehouse?page=" +
            page +
            "&limit=" +
            this.limit_returns_Purchase +
            "&warehouse_id=" +
            this.Filter_warehouse +
            "&search=" +
            this.search_return_Purchase
        )
        .then(response => {
          this.returns_purchase = response.data.returns_purchase;
          this.totalRows_Return_purchase = response.data.totalRows;
        })
        .catch(response => {});
    },

    //--------------------------- Expense Event Page Change -------------\\
    PageChange_Expense({ currentPage }) {
      if (this.Expense_page !== currentPage) {
        this.Get_Expenses(currentPage);
      }
    },

    //--------------------------- Limit Page Expense -------------\\
    onPerPageChange_Expense({ currentPerPage }) {
      if (this.limit_expenses !== currentPerPage) {
        this.limit_expenses = currentPerPage;
        this.Get_Expenses(1);
      }
    },

    onSearch_Expense(value) {
      this.search_expense = value.searchTerm;
      this.Get_Expenses(1);
    },

    //--------------------------- Get Expenses By warehouse -------------\\
    Get_Expenses(page) {
      axios
        .get(
          "report/Expenses_Warehouse?page=" +
            page +
            "&limit=" +
            this.limit_expenses +
            "&warehouse_id=" +
            this.Filter_warehouse +
            "&search=" +
            this.search_expense
        )
        .then(response => {
          this.expenses = response.data.expenses;
          this.totalRows_Expense = response.data.totalRows;
        })
        .catch(response => {});
    },

    //---------------------------------- Report Warhouse Count Stock
    report_with_echart() {
      axios
        .get(`report/Warhouse_Count_Stock`)
        .then(response => {
          const responseData = response.data;
          var dark_heading = "#c2c6dc";

          this.Stock_Count = {
            color: ["#6D28D9", "#A78BFA", "#7C3AED", "#8B5CF6", "#C4B5FD"],
            tooltip: {
              show: true,
              backgroundColor: "rgba(0, 0, 0, .8)",
              formatter: function(params) {
                return `(${params.value} Items)<br />
                        (${params.data.value1} Quantity)`;
              }
            },
            legend: {
              orient: "vertical",
              left: "left",
              data: responseData.warehouses
            },

            series: [
              {
                name: "Warehouse Stock",
                type: "pie",
                radius: "50%",
                center: "50%",

                data: responseData.stock_count,
                itemStyle: {
                  emphasis: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: "rgba(0, 0, 0, 0.5)"
                  }
                }
              }
            ]
          };
          this.Stock_value = {
            color: ["#6D28D9", "#A78BFA", "#7C3AED", "#8B5CF6", "#C4B5FD"],
            tooltip: {
              show: true,
              backgroundColor: "rgba(0, 0, 0, .8)",
              formatter: function(params) {
                return `(Stock Value by Price : ${params.value})<br />
                        (Stock Value by Cost : ${params.data.value1})`;
                        // <br />(Profit Estimate : ${params.data.value2})`;
              }
            },
            legend: {
              orient: "vertical",
              left: "left",
              data: responseData.warehouses
            },

            series: [
              {
                name: "Warehouse Stock",
                type: "pie",
                radius: "50%",
                center: "50%",

                data: responseData.stock_value,
                itemStyle: {
                  emphasis: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: "rgba(0, 0, 0, 0.5)"
                  }
                }
              }
            ]
          };
        })
        .catch(response => {});
    }
  }, //end Methods

  //----------------------------- Created function------------------- \\

  created: function() {
    this.report_with_echart();
    this.Get_Reports();
    this.Get_Sales(1);
    this.Get_Quotations(1);
    this.Get_Returns_Sale(1);
    this.Get_Returns_Purchase(1);
    this.Get_Expenses(1);
  }
};
</script>