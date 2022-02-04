<template>
  <div class="main-content">
    <breadcumb :page="$t('payments_Purchases_Return')" :folder="$t('Reports')"/>

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <b-card class="wrapper" v-if="!isLoading">
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="payments"
        @on-page-change="onPageChange"
        @on-per-page-change="onPerPageChange"
        @on-sort-change="onSortChange"
        @on-search="onSearch"
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
        styleClass="table-hover tableOne vgt-table"
      >
        <div slot="table-actions" class="mt-2 mb-3">
          <b-button variant="outline-info ripple m-1" size="sm" v-b-toggle.sidebar-right>
            <i class="i-Filter-2"></i>
            {{ $t("Filter") }}
          </b-button>
          <b-button @click="Payment_PDF()" size="sm" variant="outline-success ripple m-1">
            <i class="i-File-Copy"></i> PDF
          </b-button>
          <b-button @click="Payment_Excel()" size="sm" variant="outline-danger ripple m-1">
            <i class="i-File-Excel"></i> EXCEL
          </b-button>
        </div>
      </vue-good-table>
    </b-card>

    <!-- Sidebar Filter -->
    <b-sidebar id="sidebar-right" :title="$t('Filter')" bg-variant="white" right shadow>
      <div class="px-3 py-2">
        <b-row>
          <!-- date  -->
          <b-col md="12">
            <b-form-group :label="$t('date')">
              <b-form-input type="date" v-model="Filter_date"></b-form-input>
            </b-form-group>
          </b-col>

          <!-- Reference -->
          <b-col md="12">
            <b-form-group :label="$t('Reference')">
              <b-form-input label="Reference" :placeholder="$t('Reference')" v-model="Filter_Ref"></b-form-input>
            </b-form-group>
          </b-col>

          <!-- Supplier  -->
          <b-col md="12">
            <b-form-group :label="$t('Supplier')">
              <v-select
                :reduce="label => label.value"
                :placeholder="$t('Choose_Supplier')"
                v-model="Filter_supplier"
                :options="suppliers.map(suppliers => ({label: suppliers.name, value: suppliers.id}))"
              />
            </b-form-group>
          </b-col>

          <!-- Returns  -->
          <b-col md="12">
            <b-form-group :label="$t('Return')">
              <v-select
                :reduce="label => label.value"
                :placeholder="$t('PleaseSelect')"
                v-model="Filter_return"
                :options="purchase_returns.map(purchase_returns => ({label: purchase_returns.Ref, value: purchase_returns.id}))"
              />
            </b-form-group>
          </b-col>

          <!-- Payment choice -->
          <b-col md="12">
            <b-form-group :label="$t('Paymentchoice')">
              <v-select
                v-model="Filter_Reg"
                :reduce="label => label.value"
                :placeholder="$t('PleaseSelect')"
                :options="
                          [
                          {label: 'Cash', value: 'Cash'},
                          {label: 'cheque', value: 'cheque'},
                          {label: 'Western Union', value: 'Western Union'},
                          {label: 'bank transfer', value: 'bank transfer'},
                          {label: 'credit card', value: 'credit card'},
                          {label: 'other', value: 'other'},
                          ]"
              ></v-select>
            </b-form-group>
          </b-col>

          <b-col md="6" sm="12">
            <b-button
              @click="Payments_Purchase_returns(serverParams.page)"
              variant="primary ripple m-1"
              size="sm"
              block
            >
              <i class="i-Filter-2"></i>
              {{ $t("Filter") }}
            </b-button>
          </b-col>
          <b-col md="6" sm="12">
            <b-button @click="Reset_Filter()" variant="danger ripple m-1" size="sm" block>
              <i class="i-Power-2"></i>
              {{ $t("Reset") }}
            </b-button>
          </b-col>
        </b-row>
      </div>
    </b-sidebar>
  </div>
</template>



<script>
import NProgress from "nprogress";
import jsPDF from "jspdf";
import "jspdf-autotable";

export default {
  metaInfo: {
    title: "Payments Purchase returns"
  },
  data() {
    return {
      isLoading: true,
      serverParams: {
        sort: {
          field: "id",
          type: "desc"
        },
        page: 1,
        perPage: 10
      },
      limit: "10",
      search: "",
      totalRows: "",
      Filter_supplier: "",
      Filter_Ref: "",
      Filter_return: "",
      Filter_Reg: "",
      Filter_date: "",
      payments: [],
      suppliers: [],
      purchase_returns: []
    };
  },

  computed: {
    columns() {
      return [
        {
          label: this.$t("date"),
          field: "date",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Reference"),
          field: "Ref",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Return"),
          field: "Ref_return",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Supplier"),
          field: "provider_name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("ModePaiement"),
          field: "Reglement",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Amount"),
          field: "montant",
          tdClass: "text-left",
          thClass: "text-left"
        }
      ];
    }
  },

  methods: {
    //---- update Params Table
    updateParams(newProps) {
      this.serverParams = Object.assign({}, this.serverParams, newProps);
    },

    //---- Event Page Change
    onPageChange({ currentPage }) {
      if (this.serverParams.page !== currentPage) {
        this.updateParams({ page: currentPage });
        this.Payments_Purchase_returns(currentPage);
      }
    },

    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Payments_Purchase_returns(1);
      }
    },

    //---- Event on Sort Change
    onSortChange(params) {
      let field = "";
      if (params[0].field == "Ref_return") {
        field = "purchase_return_id";
      } else {
        field = params[0].field;
      }
      this.updateParams({
        sort: {
          type: params[0].type,
          field: field
        }
      });
      this.Payments_Purchase_returns(this.serverParams.page);
    },

    //------ Event Search

    onSearch(value) {
      this.search = value.searchTerm;
      this.Payments_Purchase_returns(this.serverParams.page);
    },

    //------ Reset Filter
    Reset_Filter() {
      this.search = "";
      this.Filter_supplier = "";
      this.Filter_Ref = "";
      this.Filter_return = "";
      this.Filter_Reg = "";
      this.Filter_date = "";
      this.Payments_Purchase_returns(this.serverParams.page);
    },

    //---------------------------------------- Set To Strings-------------------------\\
    setToStrings() {
      // Simply replaces null values with strings=''
      if (this.Filter_supplier === null) {
        this.Filter_supplier = "";
      } else if (this.Filter_return === null) {
        this.Filter_return = "";
      }
    },

    //------------------------ Payments Returns Supplier PDF -----------------------\\
    Payment_PDF() {
      var self = this;

      let pdf = new jsPDF("p", "pt");
      let columns = [
        { title: "Date", dataKey: "date" },
        { title: "Ref", dataKey: "Ref" },
        { title: "Return", dataKey: "Ref_return" },
        { title: "Supplier", dataKey: "provider_name" },
        { title: "Paid by", dataKey: "Reglement" },
        { title: "Amount", dataKey: "montant" }
      ];
      pdf.autoTable(columns, self.payments);
      pdf.text("Payments Purchase Returns", 40, 25);
      pdf.save("Payments_Purchase_returns.pdf");
    },

    //----------------------- Payments Purchase Returns Excel -----------------------\\
    Payment_Excel() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get("payment/returns_purchase/export/Excel", {
          responseType: "blob", // important
          headers: {
            "Content-Type": "application/json"
          }
        })
        .then(response => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", "Payment_Returns_Suppliers.xlsx");
          document.body.appendChild(link);
          link.click();
          // Complete the animation of the  progress bar.
          NProgress.done();
        })
        .catch(() => {
          // Complete the animation of the  progress bar.
          NProgress.done();
        });
    },

    //-------------------------------- Get All Payments Purchase returns ---------------------\\
    Payments_Purchase_returns(page) {
      this.setToStrings();
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      this.setToStrings();
      axios
        .get(
          "payment/returns_purchase?page=" +
            page +
            "&Ref=" +
            this.Filter_Ref +
            "&date=" +
             this.Filter_date +
            "&provider_id=" +
            this.Filter_supplier +
            "&purchase_return_id=" +
            this.Filter_return +
            "&Reglement=" +
            this.Filter_Reg +
            "&SortField=" +
            this.serverParams.sort.field +
            "&SortType=" +
            this.serverParams.sort.type +
            "&search=" +
            this.search +
            "&limit=" +
            this.limit
        )

        .then(response => {
          this.payments = response.data.payments;
          this.suppliers = response.data.suppliers;
          this.purchase_returns = response.data.purchase_returns;
          this.totalRows = response.data.totalRows;
          // Complete the animation of theprogress bar.
          NProgress.done();
          this.isLoading = false;
        })
        .catch(response => {
          // Complete the animation of theprogress bar.
          NProgress.done();
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
        });
    }
  },

  //----------------------------- Created function-------------------\\
  created: function() {
    this.Payments_Purchase_returns(1);
  }
};
</script>